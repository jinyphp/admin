<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IpTrackingService
{
    /**
     * IP별 로그인 시도 최대 횟수
     */
    protected $maxAttempts;
    
    /**
     * 시도 제한 시간 (분)
     */
    protected $decayMinutes;
    
    /**
     * 차단 시간 (분)
     */
    protected $blockDuration;
    
    /**
     * GeoIP 서비스 활성화 여부
     */
    protected $geoIpEnabled;
    
    /**
     * 허용된 국가 코드
     */
    protected $allowedCountries;
    
    public function __construct()
    {
        $this->maxAttempts = config('admin.security.ip_max_attempts', 5);
        $this->decayMinutes = config('admin.security.ip_decay_minutes', 15);
        $this->blockDuration = config('admin.security.ip_block_duration', 60);
        $this->geoIpEnabled = config('admin.security.geoip_enabled', false);
        $this->allowedCountries = config('admin.security.allowed_countries', []);
    }
    
    /**
     * IP 주소가 차단되었는지 확인
     */
    public function isBlocked(string $ipAddress): bool
    {
        // 화이트리스트 확인
        if ($this->isWhitelisted($ipAddress)) {
            return false;
        }
        
        // 블랙리스트 확인
        if ($this->isBlacklisted($ipAddress)) {
            return true;
        }
        
        // 임시 차단 확인
        $attempts = $this->getAttempts($ipAddress);
        if ($attempts && $attempts->is_blocked) {
            if ($attempts->blocked_until && Carbon::now()->lt($attempts->blocked_until)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * IP 주소가 화이트리스트에 있는지 확인
     */
    public function isWhitelisted(string $ipAddress): bool
    {
        return Cache::remember("ip_whitelist_{$ipAddress}", 300, function () use ($ipAddress) {
            return DB::table('admin_ip_whitelist')
                ->where('ip_address', $ipAddress)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', Carbon::now());
                })
                ->exists();
        });
    }
    
    /**
     * IP 주소가 블랙리스트에 있는지 확인
     */
    public function isBlacklisted(string $ipAddress): bool
    {
        return Cache::remember("ip_blacklist_{$ipAddress}", 300, function () use ($ipAddress) {
            return DB::table('admin_ip_blacklist')
                ->where('ip_address', $ipAddress)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', Carbon::now());
                })
                ->exists();
        });
    }
    
    /**
     * 로그인 시도 기록
     */
    public function recordAttempt(string $ipAddress, bool $success = false, ?int $userId = null): void
    {
        $attempts = $this->getAttempts($ipAddress);
        
        if (!$attempts) {
            // 새로운 IP 기록 생성
            DB::table('admin_ip_attempts')->insert([
                'ip_address' => $ipAddress,
                'attempts' => 1,
                'last_attempt_at' => Carbon::now(),
                'user_id' => $userId,
                'is_blocked' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } else {
            // 기존 기록 업데이트
            $this->updateAttempts($ipAddress, $attempts, $success, $userId);
        }
        
        // 캐시 무효화
        Cache::forget("ip_attempts_{$ipAddress}");
        
        // 로그 기록
        $this->logAttempt($ipAddress, $success, $userId);
    }
    
    /**
     * 시도 기록 업데이트
     */
    protected function updateAttempts(string $ipAddress, $attempts, bool $success, ?int $userId): void
    {
        $lastAttempt = Carbon::parse($attempts->last_attempt_at);
        $now = Carbon::now();
        
        if ($success) {
            // 성공 시 카운터 리셋
            DB::table('admin_ip_attempts')
                ->where('ip_address', $ipAddress)
                ->update([
                    'attempts' => 0,
                    'last_attempt_at' => $now,
                    'last_success_at' => $now,
                    'user_id' => $userId,
                    'is_blocked' => false,
                    'blocked_until' => null,
                    'updated_at' => $now
                ]);
        } else {
            // 실패 시 카운터 증가
            $newAttempts = 1;
            
            // decay 시간 내의 시도만 카운트
            if ($lastAttempt->diffInMinutes($now) < $this->decayMinutes) {
                $newAttempts = $attempts->attempts + 1;
            }
            
            $isBlocked = $newAttempts >= $this->maxAttempts;
            $blockedUntil = $isBlocked ? $now->addMinutes($this->blockDuration) : null;
            
            DB::table('admin_ip_attempts')
                ->where('ip_address', $ipAddress)
                ->update([
                    'attempts' => $newAttempts,
                    'last_attempt_at' => $now,
                    'user_id' => $userId,
                    'is_blocked' => $isBlocked,
                    'blocked_until' => $blockedUntil,
                    'updated_at' => $now
                ]);
            
            if ($isBlocked) {
                $this->notifyBlocked($ipAddress, $newAttempts);
            }
        }
    }
    
    /**
     * IP별 시도 기록 조회
     */
    public function getAttempts(string $ipAddress)
    {
        return Cache::remember("ip_attempts_{$ipAddress}", 60, function () use ($ipAddress) {
            return DB::table('admin_ip_attempts')
                ->where('ip_address', $ipAddress)
                ->first();
        });
    }
    
    /**
     * 남은 시도 횟수 조회
     */
    public function remainingAttempts(string $ipAddress): int
    {
        $attempts = $this->getAttempts($ipAddress);
        
        if (!$attempts) {
            return $this->maxAttempts;
        }
        
        $lastAttempt = Carbon::parse($attempts->last_attempt_at);
        
        // decay 시간이 지났으면 리셋
        if ($lastAttempt->diffInMinutes(Carbon::now()) >= $this->decayMinutes) {
            return $this->maxAttempts;
        }
        
        return max(0, $this->maxAttempts - $attempts->attempts);
    }
    
    /**
     * 차단 해제 시간 조회
     */
    public function availableIn(string $ipAddress): int
    {
        $attempts = $this->getAttempts($ipAddress);
        
        if (!$attempts || !$attempts->is_blocked) {
            return 0;
        }
        
        if (!$attempts->blocked_until) {
            return 0;
        }
        
        $blockedUntil = Carbon::parse($attempts->blocked_until);
        
        if ($blockedUntil->isPast()) {
            return 0;
        }
        
        return $blockedUntil->diffInSeconds(Carbon::now());
    }
    
    /**
     * IP 차단
     */
    public function blockIp(string $ipAddress, ?string $reason = null, ?Carbon $expiresAt = null): void
    {
        DB::table('admin_ip_blacklist')->updateOrInsert(
            ['ip_address' => $ipAddress],
            [
                'reason' => $reason,
                'is_active' => true,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
        
        Cache::forget("ip_blacklist_{$ipAddress}");
        
        Log::warning("IP blocked", [
            'ip' => $ipAddress,
            'reason' => $reason,
            'expires_at' => $expiresAt
        ]);
    }
    
    /**
     * IP 차단 해제
     */
    public function unblockIp(string $ipAddress): void
    {
        DB::table('admin_ip_blacklist')
            ->where('ip_address', $ipAddress)
            ->update([
                'is_active' => false,
                'updated_at' => Carbon::now()
            ]);
        
        DB::table('admin_ip_attempts')
            ->where('ip_address', $ipAddress)
            ->update([
                'is_blocked' => false,
                'blocked_until' => null,
                'attempts' => 0,
                'updated_at' => Carbon::now()
            ]);
        
        Cache::forget("ip_blacklist_{$ipAddress}");
        Cache::forget("ip_attempts_{$ipAddress}");
    }
    
    /**
     * IP 화이트리스트 추가
     */
    public function whitelistIp(string $ipAddress, ?string $description = null, ?Carbon $expiresAt = null): void
    {
        DB::table('admin_ip_whitelist')->updateOrInsert(
            ['ip_address' => $ipAddress],
            [
                'description' => $description,
                'is_active' => true,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
        
        Cache::forget("ip_whitelist_{$ipAddress}");
    }
    
    /**
     * IP 화이트리스트 제거
     */
    public function removeFromWhitelist(string $ipAddress): void
    {
        DB::table('admin_ip_whitelist')
            ->where('ip_address', $ipAddress)
            ->delete();
        
        Cache::forget("ip_whitelist_{$ipAddress}");
    }
    
    /**
     * 지역 기반 접근 확인
     */
    public function isAllowedCountry(string $ipAddress): bool
    {
        if (!$this->geoIpEnabled || empty($this->allowedCountries)) {
            return true;
        }
        
        $country = $this->getCountryCode($ipAddress);
        
        if (!$country) {
            // GeoIP 조회 실패 시 설정에 따라 처리
            return config('admin.security.allow_unknown_country', true);
        }
        
        return in_array($country, $this->allowedCountries);
    }
    
    /**
     * IP 주소에서 국가 코드 조회
     */
    public function getCountryCode(string $ipAddress): ?string
    {
        // GeoIP 서비스 연동 (GeoIP2 라이브러리 사용 예시)
        // 실제 구현은 사용하는 GeoIP 서비스에 따라 달라집니다
        
        try {
            // 간단한 무료 API 사용 예시
            $response = @file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=countryCode");
            
            if ($response) {
                $data = json_decode($response, true);
                return $data['countryCode'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error("GeoIP lookup failed", [
                'ip' => $ipAddress,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    /**
     * IP 정보 조회
     */
    public function getIpInfo(string $ipAddress): array
    {
        $info = [
            'ip' => $ipAddress,
            'is_blocked' => $this->isBlocked($ipAddress),
            'is_whitelisted' => $this->isWhitelisted($ipAddress),
            'is_blacklisted' => $this->isBlacklisted($ipAddress),
            'remaining_attempts' => $this->remainingAttempts($ipAddress),
            'available_in' => $this->availableIn($ipAddress)
        ];
        
        if ($this->geoIpEnabled) {
            $info['country'] = $this->getCountryCode($ipAddress);
            $info['is_allowed_country'] = $this->isAllowedCountry($ipAddress);
        }
        
        $attempts = $this->getAttempts($ipAddress);
        if ($attempts) {
            $info['total_attempts'] = $attempts->attempts;
            $info['last_attempt'] = $attempts->last_attempt_at;
            $info['last_success'] = $attempts->last_success_at;
        }
        
        return $info;
    }
    
    /**
     * 통계 조회
     */
    public function getStatistics(): array
    {
        $now = Carbon::now();
        
        return [
            'total_blocked_ips' => DB::table('admin_ip_blacklist')
                ->where('is_active', true)
                ->count(),
            
            'total_whitelisted_ips' => DB::table('admin_ip_whitelist')
                ->where('is_active', true)
                ->count(),
            
            'recent_attempts' => DB::table('admin_ip_attempts')
                ->where('last_attempt_at', '>', $now->subHours(24))
                ->count(),
            
            'blocked_today' => DB::table('admin_ip_attempts')
                ->where('is_blocked', true)
                ->where('blocked_until', '>', $now)
                ->count(),
            
            'top_attempted_ips' => DB::table('admin_ip_attempts')
                ->where('last_attempt_at', '>', $now->subDays(7))
                ->orderBy('attempts', 'desc')
                ->limit(10)
                ->get()
        ];
    }
    
    /**
     * 오래된 기록 정리
     */
    public function cleanup(int $days = 30): int
    {
        $date = Carbon::now()->subDays($days);
        
        $deleted = DB::table('admin_ip_attempts')
            ->where('last_attempt_at', '<', $date)
            ->where('is_blocked', false)
            ->delete();
        
        // 만료된 블랙리스트 항목 비활성화
        DB::table('admin_ip_blacklist')
            ->where('expires_at', '<', Carbon::now())
            ->where('is_active', true)
            ->update(['is_active' => false]);
        
        // 만료된 화이트리스트 항목 비활성화
        DB::table('admin_ip_whitelist')
            ->where('expires_at', '<', Carbon::now())
            ->where('is_active', true)
            ->update(['is_active' => false]);
        
        // 캐시 클리어
        Cache::tags(['ip_tracking'])->flush();
        
        return $deleted;
    }
    
    /**
     * 시도 로그 기록
     */
    protected function logAttempt(string $ipAddress, bool $success, ?int $userId): void
    {
        $level = $success ? 'info' : 'warning';
        $message = $success ? 'Login attempt succeeded' : 'Login attempt failed';
        
        Log::$level($message, [
            'ip' => $ipAddress,
            'user_id' => $userId,
            'timestamp' => Carbon::now()->toDateTimeString()
        ]);
    }
    
    /**
     * 차단 알림
     */
    protected function notifyBlocked(string $ipAddress, int $attempts): void
    {
        Log::warning("IP blocked due to excessive attempts", [
            'ip' => $ipAddress,
            'attempts' => $attempts,
            'blocked_until' => Carbon::now()->addMinutes($this->blockDuration)->toDateTimeString()
        ]);
        
        // 관리자에게 이메일 알림 (선택적)
        if (config('admin.security.notify_on_block', false)) {
            // 이메일 알림 로직
        }
    }
}
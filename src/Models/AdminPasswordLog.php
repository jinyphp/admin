<?php

namespace Jiny\Admin\Models;

use Jiny\Admin\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPasswordLog extends Model
{
    use HasFactory;

    protected $table = 'admin_password_logs';

    protected $fillable = [
        'email',
        'action',
        'old_password_hash',
        'user_id',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'device',
        'attempt_count',
        'first_attempt_at',
        'last_attempt_at',
        'is_blocked',
        'blocked_at',
        'unblocked_at',
        'status',
        'details',
        'metadata',
    ];

    protected $casts = [
        'details' => 'array',
        'metadata' => 'array',
        'is_blocked' => 'boolean',
        'first_attempt_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'blocked_at' => 'datetime',
        'unblocked_at' => 'datetime',
    ];

    /**
     * User relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 로그인 실패 기록
     */
    public static function recordFailedAttempt($email, $request, $userId = null)
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $browserInfo = self::parseBrowserInfo($userAgent);

        // 설정값 가져오기
        $maxAttempts = config('admin.setting.password.lockout.max_attempts', 5);
        $logAfterAttempts = config('admin.setting.password.lockout.log_after_attempts', 5);
        $cacheTtl = config('admin.setting.password.lockout.attempt_cache_ttl', 3600);

        // 동일한 이메일과 IP로 현재 활성 차단 확인
        $blockedLog = self::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('is_blocked', true)
            ->where('status', 'blocked')
            ->first();

        if ($blockedLog) {
            // 이미 차단된 상태면 차단 시도 카운트 증가
            $blockedAttempts = self::where('email', $email)
                ->where('ip_address', $ipAddress)
                ->where('status', 'blocked')
                ->count();

            // 차단된 상태에서의 추가 시도 기록
            return self::create([
                'email' => $email,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'browser' => $browserInfo['browser'],
                'platform' => $browserInfo['platform'],
                'device' => $browserInfo['device'],
                'attempt_count' => $blockedAttempts + 1,
                'first_attempt_at' => now(),
                'last_attempt_at' => now(),
                'is_blocked' => true,
                'blocked_at' => $blockedLog->blocked_at,
                'status' => 'blocked',
                'details' => [
                    'referer' => $request->header('Referer'),
                    'accept_language' => $request->header('Accept-Language'),
                    'blocked_since' => $blockedLog->blocked_at,
                ],
            ]);
        }

        // 캐시에서 실패 횟수 확인
        $attemptCount = (int) \Cache::get("password_attempts_{$email}_{$ipAddress}", 0) + 1;

        // 카운트를 캐시에 저장
        \Cache::put("password_attempts_{$email}_{$ipAddress}", $attemptCount, $cacheTtl);

        // 설정된 횟수 이상 실패 시에만 DB에 기록
        if ($attemptCount >= $logAfterAttempts) {
            $isBlocked = ($attemptCount == $maxAttempts); // 설정된 최대 횟수일 때 차단
            $blockedAt = $isBlocked ? now() : null;
            $status = $isBlocked ? 'blocked' : 'failed';

            if ($isBlocked) {
                // 차단 로그 기록
                AdminUserLog::log('password_blocked', null, [
                    'email' => $email,
                    'ip_address' => $ipAddress,
                    'attempts' => $attemptCount,
                    'blocked_at' => now(),
                ]);
            }

            // DB에 실패 기록 생성
            return self::create([
                'email' => $email,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'browser' => $browserInfo['browser'],
                'platform' => $browserInfo['platform'],
                'device' => $browserInfo['device'],
                'attempt_count' => $attemptCount,
                'first_attempt_at' => now(),
                'last_attempt_at' => now(),
                'is_blocked' => $isBlocked,
                'blocked_at' => $blockedAt,
                'status' => $status,
                'details' => [
                    'referer' => $request->header('Referer'),
                    'accept_language' => $request->header('Accept-Language'),
                    'attempt_number' => $attemptCount,
                ],
            ]);
        }

        // 로그 기록 시작 전은 메모리 객체만 반환
        return (object) [
            'attempt_count' => $attemptCount,
            'is_blocked' => false,
            'status' => 'failed',
        ];
    }

    /**
     * 로그인 성공 시 실패 카운트 초기화
     */
    public static function resetFailedAttempts($email, $ipAddress)
    {
        // 캐시에서 카운트 삭제
        \Cache::forget("password_attempts_{$email}_{$ipAddress}");

        // 로그인 성공 기록 (선택적)
        $previousAttempts = \Cache::get("password_attempts_{$email}_{$ipAddress}", 0);
        if ($previousAttempts > 0) {
            AdminUserLog::log('password_attempts_cleared', null, [
                'email' => $email,
                'ip_address' => $ipAddress,
                'cleared_attempts' => $previousAttempts,
                'cleared_at' => now(),
            ]);
        }
    }

    /**
     * 차단 여부 확인
     */
    public static function isBlocked($email, $ipAddress)
    {
        return self::where('email', $email)
            ->where('ip_address', $ipAddress)
            ->where('is_blocked', true)
            ->where('status', 'blocked')
            ->exists();
    }

    /**
     * 차단 해제
     */
    public function unblock()
    {
        $this->is_blocked = false;
        $this->unblocked_at = now();
        $this->status = 'resolved';
        $this->save();

        // 차단 해제 로그
        AdminUserLog::log('password_unblocked', null, [
            'email' => $this->email,
            'ip_address' => $this->ip_address,
            'unblocked_at' => now(),
        ]);
    }

    /**
     * 브라우저 정보 파싱
     */
    private static function parseBrowserInfo($userAgent)
    {
        $browser = 'Unknown';
        $platform = 'Unknown';
        $device = 'Desktop';

        // 브라우저 감지
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Edge';
        }

        // 플랫폼 감지
        if (preg_match('/Windows/', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Mac OS/', $userAgent)) {
            $platform = 'Mac OS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $platform = 'Android';
            $device = 'Mobile';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            $platform = 'iOS';
            $device = preg_match('/iPad/', $userAgent) ? 'Tablet' : 'Mobile';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device' => $device,
        ];
    }

    /**
     * Scope: 차단된 기록만
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope: 활성 기록만 (24시간 내)
     */
    public function scopeActive($query)
    {
        return $query->where('last_attempt_at', '>=', now()->subDay());
    }

    /**
     * Scope: 위험한 시도 (3회 이상)
     */
    public function scopeDangerous($query)
    {
        return $query->where('attempt_count', '>=', 3);
    }
}

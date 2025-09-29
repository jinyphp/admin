<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * IP 화이트리스트 기반 접근 제어 미들웨어
 * 
 * 관리자 페이지에 대한 IP 기반 접근 제한을 수행합니다.
 * 화이트리스트에 등록된 IP만 접근을 허용하며,
 * 차단된 접근 시도는 로그에 기록됩니다.
 */
class IpWhitelistMiddleware
{
    /**
     * IP 화이트리스트 캐시 키 가져오기
     */
    private function getCacheKey(): string
    {
        return config('setting.ip_whitelist.cache.key', 'admin_ip_whitelist');
    }
    
    /**
     * 캐시 유효 시간 가져오기 (초)
     */
    private function getCacheTtl(): int
    {
        return config('setting.ip_whitelist.cache.ttl', 300);
    }
    
    /**
     * 기본 허용 IP 가져오기 (로컬 개발용)
     */
    private function getDefaultAllowedIps(): array
    {
        return config('setting.ip_whitelist.default_allowed', [
            '127.0.0.1',
            '::1', // IPv6 localhost
        ]);
    }

    /**
     * HTTP 요청 처리
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string|null  $mode  'strict' 또는 'log_only'
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $mode = 'strict'): Response
    {
        // IP 화이트리스트 기능 활성화 여부 확인
        if (!config('setting.ip_whitelist.enabled', false)) {
            return $next($request);
        }

        $clientIp = $this->getClientIp($request);
        $isAllowed = $this->isIpAllowed($clientIp);

        // 접근 로그 기록
        $this->logAccess($request, $clientIp, $isAllowed);

        // log_only 모드: 로그만 기록하고 통과
        if ($mode === 'log_only') {
            return $next($request);
        }

        // strict 모드: 차단 처리
        if (!$isAllowed) {
            return $this->denyAccess($request, $clientIp);
        }

        // 허용된 IP의 접근 카운트 및 마지막 접근 시간 업데이트
        $this->updateAccessInfo($clientIp);

        return $next($request);
    }

    /**
     * 클라이언트 IP 주소 획득
     *
     * @param  Request  $request
     * @return string
     */
    private function getClientIp(Request $request): string
    {
        // 프록시/로드밸런서 뒤에 있는 경우 실제 IP 획득
        $trustedProxies = config('setting.ip_whitelist.trusted_proxies', []);
        
        if (!empty($trustedProxies)) {
            $request->setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_FOR);
        }

        return $request->ip() ?? '0.0.0.0';
    }

    /**
     * IP 주소 허용 여부 확인
     *
     * @param  string  $ip
     * @return bool
     */
    private function isIpAllowed(string $ip): bool
    {
        // 로컬 개발 환경 체크
        if (app()->environment('local') && in_array($ip, $this->getDefaultAllowedIps())) {
            return true;
        }

        // 캐시된 화이트리스트 조회
        $whitelist = $this->getWhitelist();

        foreach ($whitelist as $entry) {
            if ($this->matchesIpEntry($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * IP가 화이트리스트 엔트리와 일치하는지 확인
     *
     * @param  string  $ip
     * @param  array  $entry
     * @return bool
     */
    private function matchesIpEntry(string $ip, array $entry): bool
    {
        // 비활성화된 엔트리 스킵
        if (!$entry['is_active']) {
            return false;
        }

        // 만료된 엔트리 스킵
        if ($entry['expires_at'] && now()->gt($entry['expires_at'])) {
            return false;
        }

        switch ($entry['type']) {
            case 'single':
                return $ip === $entry['ip_address'];
                
            case 'range':
                return $this->isIpInRange($ip, $entry['ip_range_start'], $entry['ip_range_end']);
                
            case 'cidr':
                return $this->isIpInCidr($ip, $entry['ip_address'], $entry['cidr_prefix']);
                
            default:
                return false;
        }
    }

    /**
     * IP가 범위 내에 있는지 확인
     *
     * @param  string  $ip
     * @param  string  $start
     * @param  string  $end
     * @return bool
     */
    private function isIpInRange(string $ip, string $start, string $end): bool
    {
        $ipLong = ip2long($ip);
        $startLong = ip2long($start);
        $endLong = ip2long($end);

        if ($ipLong === false || $startLong === false || $endLong === false) {
            return false;
        }

        return $ipLong >= $startLong && $ipLong <= $endLong;
    }

    /**
     * IP가 CIDR 표기법 범위 내에 있는지 확인
     *
     * @param  string  $ip
     * @param  string  $cidrBase
     * @param  int  $prefix
     * @return bool
     */
    private function isIpInCidr(string $ip, string $cidrBase, int $prefix): bool
    {
        $ipLong = ip2long($ip);
        $baseLong = ip2long($cidrBase);

        if ($ipLong === false || $baseLong === false) {
            return false;
        }

        $mask = -1 << (32 - $prefix);
        return ($ipLong & $mask) === ($baseLong & $mask);
    }

    /**
     * 화이트리스트 조회 (캐시 활용)
     *
     * @return array
     */
    private function getWhitelist(): array
    {
        return Cache::remember($this->getCacheKey(), $this->getCacheTtl(), function () {
            return DB::table('admin_ip_whitelist')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })
                ->toArray();
        });
    }

    /**
     * 접근 로그 기록
     *
     * @param  Request  $request
     * @param  string  $ip
     * @param  bool  $isAllowed
     * @return void
     */
    private function logAccess(Request $request, string $ip, bool $isAllowed): void
    {
        $user = Auth::user();

        DB::table('admin_ip_access_logs')->insert([
            'ip_address' => $ip,
            'url' => $request->url(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'user_id' => $user?->id,
            'email' => $user?->email,
            'is_allowed' => $isAllowed,
            'reason' => $isAllowed ? null : 'IP not in whitelist',
            'request_data' => json_encode([
                'path' => $request->path(),
                'query' => $request->query(),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 접근 정보 업데이트
     *
     * @param  string  $ip
     * @return void
     */
    private function updateAccessInfo(string $ip): void
    {
        DB::table('admin_ip_whitelist')
            ->where('ip_address', $ip)
            ->update([
                'access_count' => DB::raw('access_count + 1'),
                'last_accessed_at' => now(),
            ]);

        // 캐시 무효화
        Cache::forget($this->getCacheKey());
    }

    /**
     * 접근 거부 처리
     *
     * @param  Request  $request
     * @param  string  $ip
     * @return Response
     */
    private function denyAccess(Request $request, string $ip): Response
    {
        // 보안상 상세 정보는 로그에만 기록하고 사용자에게는 일반적인 메시지만 표시
        $message = 'Access denied.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => $message,
            ], 403);
        }

        // 일반 403 페이지로 리다이렉트
        abort(403, $message);
    }

    /**
     * 화이트리스트 캐시 초기화
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget(config('setting.ip_whitelist.cache.key', 'admin_ip_whitelist'));
    }
}
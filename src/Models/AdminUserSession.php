<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUserSession extends Model
{
    protected $table = 'admin_user_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'last_activity_at',
        'login_at',
        'is_active',
        'browser',
        'browser_version',
        'platform',
        'device',
        'last_activity',
        'extra_data',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'login_at' => 'datetime',
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
        'extra_data' => 'array',
    ];

    protected $appends = [
        'two_factor_used'
    ];

    /**
     * 관련 사용자
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 2FA 사용 여부 접근자
     *
     * @return bool
     */
    public function getTwoFactorUsedAttribute()
    {
        // extra_data에 저장된 경우 우선 확인
        if (isset($this->extra_data['two_factor_used'])) {
            return (bool) $this->extra_data['two_factor_used'];
        }

        // 사용자의 2FA 활성화 상태로 판단
        return $this->user ? (bool) $this->user->two_factor_enabled : false;
    }

    /**
     * 활성 세션만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 비활성 세션만 조회
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * 세션 생성 또는 업데이트
     */
    public static function track($user, $request, $twoFactorUsed = false)
    {
        $userAgent = $request->userAgent();
        $browserInfo = self::parseBrowserInfo($userAgent);

        try {
            // 먼저 기존 세션이 있는지 확인
            $existingSession = self::where('session_id', session()->getId())->first();

            if ($existingSession) {
                // 기존 세션이 있으면 업데이트 (2FA 값 유지)
                $existingSession->update([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $userAgent,
                    'last_activity_at' => now(),
                    'is_active' => true,
                    'browser' => $browserInfo['browser'],
                    'browser_version' => $browserInfo['version'],
                    'platform' => $browserInfo['platform'],
                    'device' => $browserInfo['device'],
                    'extra_data' => json_encode([
                        'two_factor_used' => $twoFactorUsed,
                        'referer' => $request->header('Referer'),
                        'accept_language' => $request->header('Accept-Language'),
                    ]),
                ]);

                return $existingSession;
            } else {
                // 새 세션 생성
                return self::create([
                    'session_id' => session()->getId(),
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $userAgent,
                    'last_activity_at' => now(),
                    'login_at' => now(),
                    'is_active' => true,
                    'browser' => $browserInfo['browser'],
                    'browser_version' => $browserInfo['version'],
                    'platform' => $browserInfo['platform'],
                    'device' => $browserInfo['device'],
                    'extra_data' => json_encode([
                        'two_factor_used' => $twoFactorUsed,
                        'referer' => $request->header('Referer'),
                        'accept_language' => $request->header('Accept-Language'),
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Session tracking failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * 세션 활동 업데이트
     */
    public static function updateActivity($sessionId)
    {
        return self::where('session_id', $sessionId)
            ->update(['last_activity_at' => now()]);
    }

    /**
     * 세션 종료
     */
    public static function terminate($sessionId, $reason = 'user_logout')
    {
        $session = self::where('session_id', $sessionId)->first();
        if ($session) {
            $session->is_active = false;
            $session->last_activity_at = now();
            
            // extra_data에 종료 정보 추가
            $extraData = json_decode($session->extra_data, true) ?? [];
            $extraData['terminated_at'] = now()->toDateTimeString();
            $extraData['termination_reason'] = $reason;
            $session->extra_data = json_encode($extraData);
            
            return $session->save();
        }
        return false;
    }

    /**
     * 브라우저 정보 파싱
     */
    private static function parseBrowserInfo($userAgent)
    {
        $browser = 'Unknown';
        $version = '';
        $platform = 'Unknown';
        $device = 'Desktop';

        // 브라우저 감지
        if (preg_match('/MSIE/i', $userAgent) && ! preg_match('/Opera/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/OPR/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome/i', $userAgent) && ! preg_match('/Edge/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent) && ! preg_match('/Edge/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        // 플랫폼 감지
        if (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac OS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
            $device = 'Mobile';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
            $device = preg_match('/ipad/i', $userAgent) ? 'Tablet' : 'Mobile';
        }

        return [
            'browser' => $browser,
            'version' => $version,
            'platform' => $platform,
            'device' => $device,
        ];
    }

    /**
     * 오래된 세션 정리
     */
    public static function cleanupOldSessions($hours = 24)
    {
        $oldSessions = self::where('last_activity_at', '<', now()->subHours($hours))
            ->where('is_active', true)
            ->get();
            
        $count = 0;
        foreach ($oldSessions as $session) {
            $session->is_active = false;
            
            // extra_data에 종료 정보 추가
            $extraData = json_decode($session->extra_data, true) ?? [];
            $extraData['terminated_at'] = now()->toDateTimeString();
            $extraData['termination_reason'] = 'session_timeout';
            $session->extra_data = json_encode($extraData);
            
            if ($session->save()) {
                $count++;
            }
        }
        
        return $count;
    }
}

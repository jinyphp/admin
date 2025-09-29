<?php

namespace Jiny\Admin\Http\Controllers\Web\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUserSession;

/**
 * 관리자 로그아웃 컨트롤러
 *
 * 관리자 로그아웃 처리 및 관련 세션 정리를 담당합니다.
 * 로그아웃 이벤트 로깅 및 세션 추적 종료를 처리합니다.
 */
class AdminLogout extends Controller
{
    /**
     * 컨트롤러 생성자
     */
    public function __construct()
    {
        // 설정은 config('admin.setting')에서 직접 읽음
    }

    /**
     * 로그아웃 처리
     *
     * 사용자를 로그아웃하고 관련 세션을 종료합니다.
     * 로그아웃 로그를 기록하고 세션 추적을 종료합니다.
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse 로그인 페이지로 리다이렉트
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $sessionId = session()->getId();

        if ($user) {
            // 로그아웃 전 세션 정보 수집
            $sessionData = $this->collectSessionData($request);

            // 로그아웃 로그 기록
            $this->logLogoutEvent($user, $sessionData);

            // 세션 종료 처리
            $this->terminateUserSession($sessionId, $user);

            // 활동 추적 업데이트
            $this->updateActivityTracking($user);
        }

        // Laravel 인증 로그아웃
        Auth::logout();

        // 세션 무효화 및 토큰 재생성
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 성공 메시지와 함께 로그인 페이지로 리다이렉트
        $message = '로그아웃되었습니다.';
        $loginRoute = 'admin.login';

        return redirect()->route($loginRoute)
            ->with('success', $message);
    }

    /**
     * 빠른 로그아웃 (세션 타임아웃 등)
     *
     * 세션 타임아웃이나 강제 로그아웃 시 사용되는 메서드입니다.
     *
     * @param  string  $reason  로그아웃 사유
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceLogout(Request $request, $reason = 'session_timeout')
    {
        $user = Auth::user();
        $sessionId = session()->getId();

        if ($user) {
            // 강제 로그아웃 로그 기록
            AdminUserLog::log('force_logout', $user, [
                'reason' => $reason,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $sessionId,
                'logout_time' => now()->toDateTimeString(),
            ]);

            // 세션 종료
            AdminUserSession::terminate($sessionId);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 사유에 따른 메시지 설정
        $message = $this->getLogoutMessage($reason);

        return redirect()->route('admin.login')
            ->with('warning', $message);
    }

    /**
     * 세션 데이터 수집
     *
     * @return array
     */
    private function collectSessionData(Request $request)
    {
        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'logout_time' => now()->toDateTimeString(),
            'session_duration' => $this->calculateSessionDuration(),
            'last_activity' => session('last_activity_time'),
            'browser_info' => $this->getBrowserInfo($request->userAgent()),
            'referer' => $request->header('Referer'),
            'protocol' => $request->secure() ? 'HTTPS' : 'HTTP',
        ];
    }

    /**
     * 로그아웃 이벤트 로깅
     */
    private function logLogoutEvent($user, array $sessionData)
    {
        // 로깅 설정 확인
        $loggingEnabled = true;

        if ($loggingEnabled) {
            AdminUserLog::log('logout', $user, $sessionData);
        }
    }

    /**
     * 사용자 세션 종료
     *
     * @param  string  $sessionId
     */
    private function terminateUserSession($sessionId, $user)
    {
        // 현재 세션 종료
        AdminUserSession::terminate($sessionId);

        // 동시 세션이 허용되지 않는 경우 모든 세션 종료
        $allowConcurrent = false;

        if (! $allowConcurrent) {
            // 사용자의 모든 활성 세션 종료
            AdminUserSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'terminated_at' => now(),
                    'termination_reason' => 'user_logout',
                ]);
        }
    }

    /**
     * 활동 추적 업데이트
     */
    private function updateActivityTracking($user)
    {
        // 마지막 활동 시간 업데이트
        if (isset($user->last_activity_at)) {
            $user->last_activity_at = now();
            $user->save();
        }
    }

    /**
     * 세션 지속 시간 계산
     *
     * @return int 초 단위
     */
    private function calculateSessionDuration()
    {
        $loginTime = session('login_time');
        if ($loginTime) {
            return now()->diffInSeconds($loginTime);
        }

        return 0;
    }

    /**
     * 로그아웃 사유별 메시지 반환
     *
     * @param  string  $reason
     * @return string
     */
    private function getLogoutMessage($reason)
    {
        $messages = [
            'session_timeout' => '세션이 만료되어 로그아웃되었습니다.',
            'concurrent_login' => '다른 기기에서 로그인하여 로그아웃되었습니다.',
            'admin_force' => '관리자에 의해 로그아웃되었습니다.',
            'password_change' => '비밀번호 변경으로 인해 로그아웃되었습니다.',
            'account_disabled' => '계정이 비활성화되어 로그아웃되었습니다.',
            'maintenance' => '시스템 유지보수로 인해 로그아웃되었습니다.',
        ];

        return $messages[$reason] ?? '로그아웃되었습니다.';
    }

    /**
     * 브라우저 정보 파싱
     *
     * @param  string  $userAgent
     * @return array
     */
    private function getBrowserInfo($userAgent)
    {
        $browser = 'Unknown';
        $version = '';
        $platform = 'Unknown';

        // 플랫폼 감지
        if (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac OS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        }

        // 브라우저 감지
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
            preg_match('/MSIE (.*?);/', $userAgent, $matches);
            if (count($matches) > 1) {
                $version = $matches[1];
            }
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
            preg_match('/Firefox\/([0-9\.]+)/', $userAgent, $matches);
            if (count($matches) > 1) {
                $version = $matches[1];
            }
        } elseif (preg_match('/OPR|Opera/i', $userAgent)) {
            $browser = 'Opera';
            preg_match('/OPR\/([0-9\.]+)/', $userAgent, $matches);
            if (count($matches) > 1) {
                $version = $matches[1];
            }
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Microsoft Edge';
            preg_match('/Edge\/([0-9\.]+)/', $userAgent, $matches);
            if (count($matches) > 1) {
                $version = $matches[1];
            }
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
            preg_match('/Chrome\/([0-9\.]+)/', $userAgent, $matches);
            if (count($matches) > 1) {
                $version = $matches[1];
            }
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
            preg_match('/Version\/([0-9\.]+)/', $userAgent, $matches);
            if (count($matches) > 1) {
                $version = $matches[1];
            }
        }

        return [
            'browser' => $browser,
            'version' => $version,
            'platform' => $platform,
        ];
    }
}

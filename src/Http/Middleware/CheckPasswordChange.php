<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 비밀번호 변경 필요 여부를 확인하는 미들웨어
 *
 * 비밀번호 만료나 강제 변경이 필요한 경우를 체크하여
 * 비밀번호 변경 페이지로 리다이렉트합니다.
 */
class CheckPasswordChange
{
    /**
     * 비밀번호 변경 페이지 경로들
     * 이 경로들은 무한 리다이렉트를 방지하기 위해 체크에서 제외
     */
    protected $excludedRoutes = [
        'admin.password.change',
        'admin.password.change.post',
        'admin.logout',
        'admin.login',
        'admin.login.post',
    ];

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 인증되지 않은 사용자는 통과
        if (! Auth::check()) {
            return $next($request);
        }

        // 제외된 라우트는 통과
        if (in_array($request->route()->getName(), $this->excludedRoutes)) {
            return $next($request);
        }

        $user = Auth::user();

        // 세션에 비밀번호 변경 필요 플래그가 있는 경우
        if (session('password_change_required')) {
            return redirect()->route('admin.password.change');
        }

        // 비밀번호 강제 변경 체크
        $passwordChangeRequired = false;
        $changeReason = '';

        // 1. force_password_change 플래그 체크
        if (isset($user->force_password_change) && $user->force_password_change) {
            $passwordChangeRequired = true;
            $changeReason = '관리자가 비밀번호 변경을 요청했습니다.';
        }
        // 2. password_must_change 플래그 체크
        elseif (isset($user->password_must_change) && $user->password_must_change) {
            $passwordChangeRequired = true;
            $changeReason = '비밀번호 변경이 필요합니다.';
        }
        // 3. 비밀번호 만료 체크
        elseif ($user->password_changed_at && $user->password_expires_at) {
            if (now()->greaterThan($user->password_expires_at)) {
                $passwordChangeRequired = true;
                $changeReason = '비밀번호가 만료되었습니다.';
            }
        }

        // 비밀번호 변경이 필요한 경우
        if ($passwordChangeRequired) {
            // 비밀번호 변경 필요 세션 플래그 설정
            session()->put('password_change_required', true);
            session()->put('password_change_user_id', $user->id);

            session()->flash('notification', [
                'type' => 'warning',
                'title' => '비밀번호 변경 필요',
                'message' => $changeReason.' 새 비밀번호를 설정해주세요.',
            ]);

            return redirect()->route('admin.password.change');
        }

        return $next($request);
    }
}

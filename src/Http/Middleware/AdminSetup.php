<?php
namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSetup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $setup = config('jiny.admin.setup');

        if($setup) {
            return $next($request);
        }

        // 관리자 접속설정이 없는 경우, 설정 화면으로 이동
        return $next($request);
        return redirect('/admin/setup')->with('errors','관리자 사이트에 접속할 수 없습니다.');

    }
}

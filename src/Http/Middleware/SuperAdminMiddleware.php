<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if($user) {
            $myUser = DB::table('users')->where('email', $user->email)->first();
            //dd($myUser);
            if($myUser) {
                if($myUser->isAdmin && strtoupper($myUser->utype) == "SUPER") {
                    //dd('Super2 ');
                    return $next($request);
                } else {
                    //dd('Super3 ');
                    // 접속할 수 있는 관리자 등급이 아닙니다.;
                    $prefix = admin_prefix();
                    return redirect($prefix.'/permit')
                    ->with('error','Super 권한만 접속 가능합니다.');
                }
            }

            // 일반 사용자
            return redirect('/home');
        }

        // 권한이 없는 사용자는 로그인
        return redirect('/'.$prefix.'/login'); // ->with('error','You have not admin access');
    }
}

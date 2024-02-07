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
            if($myUser) {
                if($myUser->isAdmin && $myUser->utype == "SUPER") {
                    return $next($request);
                } else {
                    // 접속할 수 있는 관리자 등급이 아닙니다.;
                    $prefix = admin_prefix();
                    return redirect($prefix.'/permit');
                }
            }
        }

        return redirect('/');
    }
}

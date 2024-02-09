<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $prefix = admin_prefix();

        if(Auth::user()) {
            if (Auth::user()->isAdmin == 1) {
                //dd("admin");
                return $next($request);
            }

            // 권한이 없는 사용자는 로그인
            return redirect('/'.$prefix.'/reject');
        }

       // 권한이 없는 사용자는 로그인
       return redirect('/'.$prefix.'/login'); // ->with('error','You have not admin access');
    }
}

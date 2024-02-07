<?php
namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if($user) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $created_at = date("Y-m-d H:i:s");

            if($user->isAdmin == 1) {
                /*
                $uri = $_SERVER['REQUEST_URI'];
                DB::table('jiny_auth_access_log')->insert([
                    'email' => $user->email,
                    'ip_address' => $ipAddress,
                    'uri' => $uri,
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
                */

                return $next($request);
            } else {
                // 미권환 접속자 로그 기록
                DB::table('jiny_auth_reject_log')->insert([
                    'email' => $user->email,
                    'ip_address' => $ipAddress,
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

                return $this->errorPages('관리자 등급만 접속이 가능합니다.');
            }
        }

        return $this->errorPages('로그인이 필요로 합니다.');
    }

    private function errorPages($message)
    {
        $_admin_ = config('jiny.admin.setting');
        if(isset($_admin_['errors'])) {
            $admin_error_uri = $_admin_['errors'];
        } else {
            $admin_error_uri = "/admin/error";
        }

        return redirect($admin_error_uri)->with('message',$message);
    }
}

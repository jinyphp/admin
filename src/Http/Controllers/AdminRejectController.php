<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 페이지 접속 권한 없음 오류 페이지
 */
class AdminRejectController extends Controller
{
    public function __construct()
    {
    }


    public function index(Request $request)
    {
        //$user = Auth::user();
        // if($user->utype == "admin" || $user->utype == "super" ) {
        //     $prefix = admin_prefix();
        //     return redirect("/".$prefix);
        // }

        // 권환 없음 페이지 출력
        $viewFile = "jiny-admin"."::permit.reject";

        $user = Auth::user();
        if($user) {
            // 로그인 회원의 접속 로그 기록
            DB::table('jiny_auth_reject_log')->insert([
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $viewFile = "jiny-admin"."::permit.reject_user";
            return view($viewFile,['user'=>$user]);
        }

        // 로그인 접속이 없는 상태에서
        // 접근 기록
        DB::table('jiny_auth_reject_log')->insert([
            //'email' => $user->email,
            'ip_address' => $request->ip(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $viewFile = "jiny-admin"."::permit.reject";
        return view($viewFile);
    }


}

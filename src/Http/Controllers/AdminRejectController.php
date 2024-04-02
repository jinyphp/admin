<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminRejectController extends Controller
{
    public function __construct()
    {
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->utype == "admin" || $user->utype == "super" ) {
            $prefix = admin_prefix();
            return redirect("/".$prefix);
        }

        // 권환 없음 페이지 출력
        $viewFile = "jiny-admin"."::permit.reject";
        return view($viewFile);
    }


}

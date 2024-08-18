<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Jiny\Site\Http\Controllers\SiteController;
class AdminRejectSuper extends SiteController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        if($user) {

            if(!$this->isSuper($user)) {
                // 권환 없음 페이지 출력
                $viewFile = "jiny-admin::permit.grade";
                return view($viewFile,[
                    'user' => $user
                ]);
            }

            // super관리자인 경우
            // admin 페이지로 이동
            return redirect("/admin");
        }

    }

    private function isSuper($user)
    {
        if($user->isAdmin) {
            if($user->utype == "super") {
                return true;
            }
        }

        return false;
    }


}

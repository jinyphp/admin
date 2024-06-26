<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Jiny\WireTable\Http\Controllers\WireDashController;
class AdminDashboard extends WireDashController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['view']['main'] = "jiny-admin::dashboard.main";

        $this->actions['title'] = "JinyPHP Admin";
        $this->actions['subtitle'] = "JinyPHP Main Admin 입니다. 서비스와 모듈을 관리합니다.";

        //setMenu('menus/site.json');
        setTheme("admin/sidebar");
    }

    /*
    public function index(Request $request)
    {
        // 지니테마가 설치되어 있는지 확인
        if(function_exists("getThemeName")) {

            // 테마의 view를 출력
            return view("jiny-admin::dashboard.theme");
        }

        $viewFile = "jiny-admin"."::dashboard.index";
        return view($viewFile);
    }
        */

}

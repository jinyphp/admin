<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AdminDashboard extends Controller
{
    public function __construct()
    {
    }


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

}

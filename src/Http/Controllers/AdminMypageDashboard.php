<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AdminMypageDashboard extends Controller
{
    public function __construct()
    {
    }


    public function index(Request $request)
    {
        return view("jiny-admin::admin.mypage");
    }

}

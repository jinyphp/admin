<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminRejectSuper extends Controller
{
    public function __construct()
    {
    }


    public function index(Request $request)
    {
        // 권환 없음 페이지 출력
        return view('jiny-admin::permit');
    }


}

<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminLogReject extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "jiny_auth_reject_log"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['list'] = "jiny-admin::admin.reject.list";
        $this->actions['view']['form'] = "jiny-admin::admin.reject.form";

        $this->actions['title'] = "Admin Reject";
        $this->actions['subtitle'] = "";
    }

    public function index(Request $request)
    {


        return parent::index($request);
    }

}

<?php
namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserProfile extends WireTablePopupForms
{

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['view']['layout'] = "jiny-admin::admin.profile.layout";

        $this->actions['title'] = "Avata Images";
        $this->actions['subtitle'] = "사용자별 아바타 이미지 입니다.";
    }

    public function index(Request $request)
    {
        $this->params['user'] = Auth::user();
        return parent::index($request);
    }



}

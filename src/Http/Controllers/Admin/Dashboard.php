<?php
namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Jiny\Admin\Http\Controllers\AdminDashboard;
class Dashboard extends AdminDashboard
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['view']['main'] = "jiny-admin::admin.dashboard.main";

        $this->actions['title'] = "JinyPHP Admin";
        $this->actions['subtitle'] = "JinyPHP Main Admin 입니다. 서비스와 모듈을 관리합니다.";

    }



}

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
class AdminActionFiles extends WireDashController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        $this->actions['view']['main'] = "jiny-admin::actions.main";

        $this->actions['title'] = "Actions";
        $this->actions['subtitle'] = "Actions 파일목록 입니다.";

        //setMenu('menus/site.json');
        //setTheme("admin/sidebar");
    }

    public function edit(Request $request, $all = null)
    {
        // main 내용 변경
        $this->actions['view']['main'] = "jiny-admin::actions.edit";

        // view 인자값 추가
        $this->params['filename'] = $all; //.".json";
        //dd($this->params);

        return parent::index($request);
    }



}

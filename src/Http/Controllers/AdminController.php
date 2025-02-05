<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


/**
 * 어드민 관리자 컨트롤러
 */
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminController extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();

        // 레이아웃 설정
        if(!$this->viewLayout){
            if(isset($this->actions['view']['layout'])){
                $this->viewLayout = $this->actions['view']['layout'];
            } else {
                $this->viewLayout = "jiny-admin::table.layout";
            }
        }

        if(!$this->viewTable){
            if(isset($this->actions['view']['table'])){
                $this->viewTable = $this->actions['view']['table'];
            } else {
                $this->viewTable = "jiny-admin::table.table";
            }
        }

    }


    public function index(Request $request)
    {
        $this->params['prefix'] = prefix('admin');

        return parent::index($request);
    }

}

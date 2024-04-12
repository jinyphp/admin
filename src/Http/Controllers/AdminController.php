<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\LiveController;
class AdminController extends LiveController
{
    public function __construct()
    {
        parent::__construct();

        // 컨트롤러 테마 지정
        $this->setTheme("admin.sidebar");
    }

}

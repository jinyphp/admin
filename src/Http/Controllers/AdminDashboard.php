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
        // $this->setVisit($this);

    }

    public function index(Request $request)
    {
        $this->params['prefix'] = prefix('admin');

        return parent::index($request);
    }

}

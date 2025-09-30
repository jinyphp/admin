<?php

namespace Jiny\Admin\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminErpDashboard extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-admin::erp.dashboard');
    }
}
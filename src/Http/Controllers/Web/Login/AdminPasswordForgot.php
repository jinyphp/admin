<?php

namespace Jiny\Admin\Http\Controllers\Web\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPasswordForgot extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-admin::Site.Login.password-forgot');
    }
}

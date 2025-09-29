<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Base Admin Controller
 *
 * 관리자 컨트롤러의 공통 기능을 제공하는 기본 컨트롤러
 */
abstract class AdminController extends Controller
{
    /**
     * 생성자
     */
    public function __construct()
    {
        // 관리자 미들웨어 적용
        $this->middleware('admin');
    }
}
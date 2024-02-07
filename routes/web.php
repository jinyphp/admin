<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// 관리자 접속 라우트 경로
$_admin_ = config('jiny.admin.setting');
if($_admin_) {
    $adminPrefix = $_admin_['route'];
} else {
    $adminPrefix = "admin";
}
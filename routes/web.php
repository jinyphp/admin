<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// 모듈에서 설정되 접속 prefix값을 읽어 옵니다.
$prefix = admin_prefix();

use Modules\Admin\Http\Controllers\AdminDashboardController;
//
// admin과 super 2개의 미들웨어 통과 필요
Route::middleware(['web','auth:sanctum', 'verified', 'admin', 'super'])
->name('admin')
->prefix($prefix)->group(function () {

    Route::get('/', [AdminDashboardController::class, 'index']);
});





Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
->name('admin')
->prefix($prefix)->group(function () {
    Route::get('permit', [AdminDashboardController::class, 'permit']);
});

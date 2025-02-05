<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// 모듈에서 설정되 접속 prefix값을 읽어 옵니다.
$prefix = admin_prefix();
$admin = Prefix("admin");

/**
 * 일반접속 시도
 */
Route::middleware(['web'])
->name('admin')
->prefix($admin)->group(function () {
    // 접속 권한이 없는 사용자가 관리자 페이지에 접근하는 경우
    // 오류 페이지 출력
    Route::get('/reject', [
        Jiny\Admin\Http\Controllers\AdminRejectController::class,
        'index']);
});


// Super 권한이 있는 경우
// admin과 super 2개의 미들웨어 통과 필요
Route::middleware(['web','auth:sanctum', 'verified', 'admin', 'super'])
->name('admin')
->prefix($admin)->group(function () {

    Route::get('/', [
        Jiny\Admin\Http\Controllers\Admin\Dashboard::class,
        'index']);

    // Route::get('/actions', [
    //     Jiny\Admin\Http\Controllers\AdminActionFiles::class,
    //     'index']);

    // Route::get('/actions/{all?}', [
    //     Jiny\Admin\Http\Controllers\AdminActionFiles::class,
    //     'edit'])->where('all', '.*');;

    // Route::get('/actions/edit', [
    //     Jiny\Admin\Http\Controllers\AdminActionFileEdit::class,
    //     'index']);

    Route::get('profile', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserProfile::class,
        'index'
    ]);

    // 권한 없는 접속 로그
    Route::get('/log/reject', [
        Jiny\Admin\Http\Controllers\AdminLogReject::class,
        'index']);


});


// Super 권한이 없는 경우
use Jiny\Admin\Http\Controllers\AdminRejectSuper;
Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
->name('admin')
->prefix($admin)->group(function () {
    Route::get('permit', [AdminRejectSuper::class, 'index']);
});


Route::middleware(['web','auth:sanctum', 'verified'])
->name('admin.')
->prefix($admin)->group(function () {

    // 관리자 마이페이지
    Route::resource(
        '/mypage',
        \Jiny\Admin\Http\Controllers\AdminMypageDashboard::class);

});





Route::middleware(['web'])
->name('admin')
->prefix($prefix)->group(function () {
    Route::get('/setup', [\Jiny\Admin\Http\Controllers\AdminSetup::class, 'index']);
});


// API
// Route::middleware(['web', 'auth:sanctum', 'verified'])
// ->name('admin.')
// ->prefix('/api/admin')->group(function () {
//     Route::get('search', [  // '/api/search'에서 앞의 '/'를 제거했습니다.
//         \Jiny\Admin\Http\Controllers\AdminSearch::class,
//         'search'
//     ])->name('api.search');
// });


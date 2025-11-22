<?php

use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\Web\Login\AdminAuth;
use Jiny\Admin\Http\Controllers\Web\Login\AdminLogin;
use Jiny\Admin\Http\Controllers\Web\Login\AdminLogout;
use Jiny\Admin\Http\Controllers\Web\Login\AdminPasswordChange;
use Jiny\Admin\Http\Controllers\Web\Login\AdminPasswordForgot;
use Jiny\Admin\Http\Controllers\Web\Setup\AdminSetup;
use Jiny\Admin\Http\Controllers\Home\AdminHome;
use Jiny\Admin\Http\Controllers\Erp\AdminErpDashboard;

/*
|--------------------------------------------------------------------------
| Admin Web Routes
|--------------------------------------------------------------------------
|
| 관리자 영역의 웹 라우트를 정의합니다.
| 미들웨어별로 계층적으로 구성되어 있습니다.
|
*/

Route::middleware(['web'])->prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes (미들웨어 없음 - 누구나 접근 가능)
    |--------------------------------------------------------------------------
    */

    // Setup Routes - 초기 설정
    Route::prefix('setup')->name('admin.setup.')->group(function () {
        Route::get('/', [AdminSetup::class, 'index'])->name('');
        Route::get('/check-requirements', [AdminSetup::class, 'checkRequirements'])->name('check-requirements');
        Route::get('/check-database', [AdminSetup::class, 'checkDatabase'])->name('check-database');
        Route::get('/check-pending-migrations', [AdminSetup::class, 'checkPendingMigrations'])->name('check-pending-migrations');
        Route::post('/run-migrations', [AdminSetup::class, 'runMigrations'])->name('run-migrations');
        Route::post('/create-admin', [AdminSetup::class, 'createAdmin'])->name('create-admin');
        Route::post('/save-settings', [AdminSetup::class, 'saveSettings'])->name('save-settings');
        Route::post('/next-step', [AdminSetup::class, 'nextStep'])->name('next-step');
        Route::post('/go-to-step', [AdminSetup::class, 'goToStep'])->name('go-to-step');
        Route::post('/complete', [AdminSetup::class, 'completeSetup'])->name('complete');
    });

    // Login Routes - 로그인
    Route::name('admin.login')->group(function () {
        Route::get('/login', [AdminLogin::class, 'showLoginForm']);
        Route::post('/login', [AdminAuth::class, 'login'])->name('.post');
    });

    // Redirect 'login' to admin login (for middleware compatibility)
    Route::get('/login', function () {
        return redirect()->route('admin.login');
    })->name('login');

    // Password Routes - 비밀번호 (공개)
    Route::prefix('login/password')->name('admin.password.')->group(function () {
        Route::get('/forgot', AdminPasswordForgot::class)->name('forgot');
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes (auth 미들웨어 - 인증된 사용자만)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth'])->group(function () {

        // Password Change Routes - 비밀번호 변경
        Route::prefix('login/password')->name('admin.password.')->group(function () {
            Route::get('/change', [AdminPasswordChange::class, 'showChangeForm'])->name('change');
            Route::post('/change', [AdminPasswordChange::class, 'changePassword'])->name('change.post');
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (auth + admin 미들웨어 - 관리자만)
    |--------------------------------------------------------------------------
    */

    // admin 미들웨어는 자체적으로 인증을 처리하므로 auth 미들웨어 불필요
    Route::middleware(['admin'])->group(function () {

        // Logout - 로그아웃
        Route::match(['get', 'post'], '/logout', [AdminLogout::class, 'logout'])->name('admin.logout');

        // Admin Home - 관리자 홈
        Route::get('/', AdminHome::class)->name('admin.home');

        // ERP Routes - ERP 시스템
        Route::prefix('erp')->name('admin.erp.')->group(function () {
            Route::get('/dashboard', AdminErpDashboard::class)->name('dashboard');
        });

    });

});

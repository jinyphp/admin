<?php

use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\Web\Login\AdminAuth;
use Jiny\Admin\Http\Controllers\Web\Login\AdminLogin;
use Jiny\Admin\Http\Controllers\Web\Login\AdminLogout;
use Jiny\Admin\Http\Controllers\Web\Login\AdminPasswordChange;
use Jiny\Admin\Http\Controllers\Web\Setup\AdminSetup;

/*
|--------------------------------------------------------------------------
| Admin Domain Web Routes
|--------------------------------------------------------------------------
*/

// Web 미들웨어 그룹 적용
Route::middleware(['web'])->group(function () {

    // Admin Login Routes
    Route::prefix('admin')->group(function () {
        // Setup routes (초기 설정 시에만 접근 가능)
        Route::prefix('setup')->group(function () {
            Route::get('/', [AdminSetup::class, 'index'])->name('admin.setup');
            Route::get('/check-requirements', [AdminSetup::class, 'checkRequirements'])->name('admin.setup.check-requirements');
            Route::get('/check-database', [AdminSetup::class, 'checkDatabase'])->name('admin.setup.check-database');
            Route::get('/check-pending-migrations', [AdminSetup::class, 'checkPendingMigrations'])->name('admin.setup.check-pending-migrations');
            Route::post('/run-migrations', [AdminSetup::class, 'runMigrations'])->name('admin.setup.run-migrations');
            Route::post('/create-admin', [AdminSetup::class, 'createAdmin'])->name('admin.setup.create-admin');
            Route::post('/save-settings', [AdminSetup::class, 'saveSettings'])->name('admin.setup.save-settings');
            Route::post('/next-step', [AdminSetup::class, 'nextStep'])->name('admin.setup.next-step');
            Route::post('/go-to-step', [AdminSetup::class, 'goToStep'])->name('admin.setup.go-to-step');
            Route::post('/complete', [AdminSetup::class, 'completeSetup'])->name('admin.setup.complete');
        });

        // Login routes (누구나 접근 가능, 컨트롤러에서 처리)
        Route::get('/login', [AdminLogin::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuth::class, 'login'])->name('admin.login.post');

        // Password related routes
        Route::prefix('login/password')->group(function () {
            // Password forgot route (누구나 접근 가능)
            Route::get('/forgot', \Jiny\Admin\Http\Controllers\Web\Login\AdminPasswordForgot::class)->name('admin.password.forgot');

            // Password change route (인증된 사용자만)
            Route::middleware(['auth'])->group(function () {
                Route::get('/change', [AdminPasswordChange::class, 'showChangeForm'])->name('admin.password.change');
                Route::post('/change', [AdminPasswordChange::class, 'changePassword'])->name('admin.password.change.post');
            });
        });

        // Authenticated routes (관리자 권한 필요)
        Route::middleware(['auth', 'admin'])->group(function () {
            Route::get('/dashboard', \Jiny\Admin\Http\Controllers\Admin\AdminDashboard\AdminDashboard::class)->name('admin.dashboard');
            Route::match(['get', 'post'], '/logout', [AdminLogout::class, 'logout'])->name('admin.logout');

            Route::get('/', function () {
                return redirect()->route('admin.dashboard');
            });
        });
    });
});

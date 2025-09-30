<?php

use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\Admin2FAController;

// Admin Dashboard Route
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system')->group(function () {
    Route::get('/dashboard', \Jiny\Admin\Http\Controllers\Admin\AdminDashboard\AdminDashboard::class)
        ->name('admin.system.dashboard');
});

// // Admin Test Routes
// Route::middleware(['web', 'admin'])->prefix('admin/system')->group(function () {
//     Route::group(['prefix' => 'test'], function () {
//         Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminTest\AdminTest::class)
//             ->name('admin.test');

//         Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminTest\AdminTestCreate::class)
//             ->name('admin.test.create');

//         Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminTest\AdminTestEdit::class)
//             ->name('admin.test.edit');

//         Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminTest\AdminTestShow::class)
//             ->name('admin.test.show');

//         Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminTest\AdminTestDelete::class)
//             ->name('admin.test.delete');
//     });
// });

// // Admin Templates Routes
// Route::middleware(['web', 'admin'])->prefix('admin/system')->group(function () {
//     Route::group(['prefix' => 'templates'], function () {
//         Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminTemplates\AdminTemplates::class)
//             ->name('admin.templates');

//         Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminTemplates\AdminTemplatesCreate::class)
//             ->name('admin.templates.create');

//         Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminTemplates\AdminTemplatesEdit::class)
//             ->name('admin.templates.edit');

//         Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminTemplates\AdminTemplatesShow::class)
//             ->name('admin.templates.show');

//         Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminTemplates\AdminTemplatesDelete::class)
//             ->name('admin.templates.delete');
//     });
// });

// // Admin Hello Routes
// Route::middleware(['web', 'admin'])->prefix('admin/system')->group(function () {
//     Route::group(['prefix' => 'hello'], function () {
//         Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminHello\AdminHello::class)
//             ->name('admin.hello');

//         Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminHello\AdminHelloCreate::class)
//             ->name('admin.hello.create');

//         Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminHello\AdminHelloEdit::class)
//             ->name('admin.hello.edit');

//         Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminHello\AdminHelloShow::class)
//             ->name('admin.hello.show');

//         Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminHello\AdminHelloDelete::class)
//             ->name('admin.hello.delete');
//     });
// });

// Admin User Type Routes
Route::middleware(['web', 'admin'])->prefix('admin/system/user')->group(function () {
    Route::group(['prefix' => 'type'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminUsertype\AdminUsertype::class)
            ->name('admin.system.user.type');

        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminUsertype\AdminUsertypeCreate::class)
            ->name('admin.system.user.type.create');

        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminUsertype\AdminUsertypeEdit::class)
            ->name('admin.system.user.type.edit');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUsertype\AdminUsertypeShow::class)
            ->name('admin.system.user.type.show');

        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUsertype\AdminUsertypeDelete::class)
            ->name('admin.system.user.type.delete');
    });
});

// Admin Users Routes
Route::middleware(['web', 'admin'])->prefix('admin/system')->group(function () {
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminUsers\AdminUsers::class)
            ->name('admin.system.users');

        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminUsers\AdminUsersCreate::class)
            ->name('admin.system.users.create');

        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminUsers\AdminUsersEdit::class)
            ->name('admin.system.users.edit');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUsers\AdminUsersShow::class)
            ->name('admin.system.users.show');

        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUsers\AdminUsersDelete::class)
            ->name('admin.system.users.delete');
    });
});

// Admin User Logs Routes (auth required)
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/user')->group(function () {
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminUserLogs\AdminUserLogs::class)
            ->name('admin.system.user.logs');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUserLogs\AdminUserLogsShow::class)
            ->name('admin.system.user.logs.show');

        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUserLogs\AdminUserLogsDelete::class)
            ->name('admin.system.user.logs.delete');
    });
});

// Admin IP Whitelist Routes (보안 설정)
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/security')->group(function () {
    Route::group(['prefix' => 'ip-whitelist'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist\AdminIpWhitelist::class)
            ->name('admin.system.security.ip-whitelist');

        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist\AdminIpWhitelistCreate::class)
            ->name('admin.system.security.ip-whitelist.create');

        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist\AdminIpWhitelistEdit::class)
            ->name('admin.system.security.ip-whitelist.edit');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist\AdminIpWhitelistShow::class)
            ->name('admin.system.security.ip-whitelist.show');

        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist\AdminIpWhitelistDelete::class)
            ->name('admin.system.security.ip-whitelist.delete');
    });
});

// Admin User 2FA Routes (관리자 전용)
Route::middleware(['web'])->prefix('admin/system/user')->group(function () {
    Route::group(['prefix' => '2fa'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2fa::class)
            ->name('admin.system.user.2fa');

        Route::get('/create/{id?}', \Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faCreate::class)
            ->name('admin.system.user.2fa.create');

        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'edit'])
            ->name('admin.system.user.2fa.edit');

        Route::post('/{id}/generate', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'generate'])
            ->name('admin.system.user.2fa.generate');

        Route::post('/{id}/store', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'store'])
            ->name('admin.system.user.2fa.store');

        Route::post('/{id}/regenerate-backup', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'regenerateBackup'])
            ->name('admin.system.user.2fa.regenerate-backup');

        Route::post('/{id}/show-qr', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'showQr'])
            ->name('admin.system.user.2fa.show-qr');

        Route::post('/{id}/regenerate-qr', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'regenerateQr'])
            ->name('admin.system.user.2fa.regenerate-qr');

        Route::post('/{id}/confirm-regenerate', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'confirmRegenerateQr'])
            ->name('admin.system.user.2fa.confirm-regenerate');

        Route::delete('/{id}/disable', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'disable'])
            ->name('admin.system.user.2fa.disable');

        Route::delete('/{id}/force-disable', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'forceDisable'])
            ->name('admin.system.user.2fa.force-disable');

        Route::get('/{id}/status', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faEdit::class, 'status'])
            ->name('admin.system.user.2fa.status');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faShow::class)
            ->name('admin.system.user.2fa.show');

        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\AdminUser2faDelete::class)
            ->name('admin.system.user.2fa.delete');
    });
});

// 2FA 추가 라우트 (admin-2fa.php에서 통합)
Route::prefix('admin/system/users/{userId}/2fa')->middleware(['web', 'auth'])->group(function () {
    // SMS 관련
    Route::post('/send-sms', [Admin2FAController::class, 'sendSmsCode'])
        ->name('admin.system.user.2fa.send-sms');
    
    Route::post('/verify-sms', [Admin2FAController::class, 'verifySmsCode'])
        ->name('admin.system.user.2fa.verify-sms');
    
    // Email 관련
    Route::post('/send-email', [Admin2FAController::class, 'sendEmailCode'])
        ->name('admin.system.user.2fa.send-email');
    
    Route::post('/verify-email', [Admin2FAController::class, 'verifyEmailCode'])
        ->name('admin.system.user.2fa.verify-email');
    
    // 2FA 방법 변경
    Route::post('/change-method', [Admin2FAController::class, 'changeMethod'])
        ->name('admin.system.user.2fa.change-method');
    
    // 백업 코드 관련
    Route::post('/regenerate-backup', [Admin2FAController::class, 'regenerateBackupCodes'])
        ->name('admin.system.user.2fa.regenerate-backup');
    
    Route::get('/download-backup', [Admin2FAController::class, 'downloadBackupCodes'])
        ->name('admin.system.user.2fa.download-backup');
    
    // 2FA 상태 확인 (기존 라우트와 중복 - 제거)
    // Route::get('/status', [Admin2FAController::class, 'getStatus'])
    //     ->name('admin.system.user.2fa.status');
    
    // 전화번호 업데이트
    Route::post('/update-phone', [Admin2FAController::class, 'updatePhoneNumber'])
        ->name('admin.system.user.profile.update-phone');
});

// 만료된 코드 정리 (크론잡용 - API 미들웨어 사용)
Route::get('/admin/2fa/cleanup', [\Jiny\Admin\Http\Controllers\Admin\AdminUser2fa\Admin2FAController::class, 'cleanupExpiredCodes'])
    ->name('admin.2fa.cleanup')
    ->middleware(['api']);

// 2FA 인증 라우트 (로그인 과정)
Route::middleware(['web'])->prefix('admin/login')->group(function () {
    Route::get('/2fa/challenge', [\Jiny\Admin\Http\Controllers\Web\Login\Admin2FA::class, 'showChallenge'])
        ->name('admin.2fa.challenge');

    Route::post('/2fa/verify', [\Jiny\Admin\Http\Controllers\Web\Login\Admin2FA::class, 'verify'])
        ->name('admin.2fa.verify');
});

// 계정 잠금 해제 라우트
Route::middleware(['web'])->prefix('account/unlock')->group(function () {
    // 잠금 해제 페이지 표시
    Route::get('/{token}', [\Jiny\Admin\Http\Controllers\Web\Login\UnlockAccount::class, 'show'])
        ->name('account.unlock.show');
    
    // 잠금 해제 처리
    Route::post('/', [\Jiny\Admin\Http\Controllers\Web\Login\UnlockAccount::class, 'unlock'])
        ->name('account.unlock.process');
    
    // 새 잠금 해제 링크 요청 페이지
    Route::get('/request/new', [\Jiny\Admin\Http\Controllers\Web\Login\UnlockAccount::class, 'requestForm'])
        ->name('account.unlock.request');
    
    // 새 잠금 해제 링크 발송
    Route::post('/request/send', [\Jiny\Admin\Http\Controllers\Web\Login\UnlockAccount::class, 'sendUnlockLink'])
        ->name('account.unlock.send');
});

// Admin User Sessions Routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/user')->group(function () {
    Route::group(['prefix' => 'sessions'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminSessions\AdminSessions::class)
            ->name('admin.system.user.sessions');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminSessions\AdminSessionsShow::class)
            ->name('admin.system.user.sessions.show');

        Route::post('/{id}/terminate', \Jiny\Admin\Http\Controllers\Admin\AdminSessions\AdminSessionsDelete::class)
            ->name('admin.system.user.sessions.terminate');
    });
});

// Admin User Stats Routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/user')->group(function () {
    Route::get('/stats', \Jiny\Admin\Http\Controllers\Admin\AdminStats\AdminStats::class)
        ->name('admin.system.user.stats');
});

// Admin Password Logs Routes (관리자 전용)
Route::middleware(['web', 'auth'])->prefix('admin/system/user/password')->group(function () {
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogs::class)
            ->name('admin.system.user.password.logs');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogsShow::class)
            ->name('admin.system.user.password.logs.show');

        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogsDelete::class)
            ->name('admin.system.user.password.logs.delete');

        Route::post('/{id}/unblock', \Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogsUnblock::class)
            ->name('admin.system.user.password.logs.unblock');

        Route::post('/bulk/unblock', [\Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogsUnblock::class, 'bulk'])
            ->name('admin.system.user.password.logs.bulk-unblock');
    });
});

// Admin User Password Management Routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/user')->group(function () {
    Route::group(['prefix' => 'password'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminUserPassword\AdminUserPassword::class)
            ->name('admin.system.user.password');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminUserPassword\AdminUserPasswordShow::class)
            ->name('admin.system.user.password.show');
    });
});

// Admin Avatar Routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/user')->group(function () {
    Route::group(['prefix' => 'avatar'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminAvatar\AdminAvatar::class)
            ->name('admin.system.avatar');

        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminAvatar\AdminAvatarCreate::class)
            ->name('admin.system.avatar.create');

        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminAvatar\AdminAvatarEdit::class)
            ->name('admin.system.avatar.edit');

        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminAvatar\AdminAvatarShow::class)
            ->name('admin.system.avatar.show');

        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminAvatar\AdminAvatarDelete::class)
            ->name('admin.system.avatar.delete');
    });
});

// Admin Mail Routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/mail')->group(function () {
    // Mail Dashboard
    Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminMailDashboard::class)
        ->name('admin.system.mail');
    
    // Mail Settings
    Route::group(['prefix' => 'setting'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminMailSetting\AdminMailSetting::class)
            ->name('admin.system.mail.setting');
        
        Route::post('/update', [\Jiny\Admin\Http\Controllers\Admin\AdminMailSetting\AdminMailSetting::class, 'update'])
            ->name('admin.system.mail.setting.update');
        
        Route::post('/test', [\Jiny\Admin\Http\Controllers\Admin\AdminMailSetting\AdminMailSetting::class, 'test'])
            ->name('admin.system.mail.setting.test');
    });
    
    // Email Templates
    Route::group(['prefix' => 'templates'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminEmailTemplates\AdminEmailTemplates::class)
            ->name('admin.system.mail.templates');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminEmailTemplates\AdminEmailTemplatesCreate::class)
            ->name('admin.system.mail.templates.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminEmailTemplates\AdminEmailTemplatesEdit::class)
            ->name('admin.system.mail.templates.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminEmailTemplates\AdminEmailTemplatesShow::class)
            ->name('admin.system.mail.templates.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminEmailTemplates\AdminEmailTemplatesDelete::class)
            ->name('admin.system.mail.templates.delete');
    });
    
    // Admin Email Logs Routes
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs\AdminEmailLogs::class)
            ->name('admin.system.mail.logs');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs\AdminEmailLogsCreate::class)
            ->name('admin.system.mail.logs.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs\AdminEmailLogsEdit::class)
            ->name('admin.system.mail.logs.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs\AdminEmailLogsShow::class)
            ->name('admin.system.mail.logs.show');
        
        Route::post('/{id}/send', \Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs\AdminEmailLogsSend::class)
            ->name('admin.system.mail.logs.send');
        
        Route::post('/{id}/resend', \Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs\AdminEmailLogsResend::class)
            ->name('admin.system.mail.logs.resend');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs\AdminEmailLogsDelete::class)
            ->name('admin.system.mail.logs.delete');
    });
    
    // Email Tracking Management Routes (관리자용)
    Route::group(['prefix' => 'tracking'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminEmailtracking\AdminEmailtracking::class)
            ->name('admin.system.mail.tracking');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminEmailtracking\AdminEmailtrackingCreate::class)
            ->name('admin.system.mail.tracking.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminEmailtracking\AdminEmailtrackingEdit::class)
            ->name('admin.system.mail.tracking.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminEmailtracking\AdminEmailtrackingShow::class)
            ->name('admin.system.mail.tracking.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminEmailtracking\AdminEmailtrackingDelete::class)
            ->name('admin.system.mail.tracking.delete');
        
        // Email Tracking Pixel/Link Routes (공개 접근 - 인증 불필요)
        Route::get('/pixel/{token}', [\Jiny\Admin\Http\Controllers\Admin\EmailTrackingController::class, 'pixel'])
            ->name('admin.system.email.tracking.pixel')
            ->withoutMiddleware(['auth', 'admin']);
        
        Route::get('/link/{token}/{linkId}', [\Jiny\Admin\Http\Controllers\Admin\EmailTrackingController::class, 'link'])
            ->name('admin.system.email.tracking.link')
            ->withoutMiddleware(['auth', 'admin']);
        
        Route::get('/stats/{emailId}', [\Jiny\Admin\Http\Controllers\Admin\EmailTrackingController::class, 'stats'])
            ->name('admin.system.email.tracking.stats');
    });
});

// Admin Notification Settings Routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/settings/notifications')->group(function () {
    Route::get('/', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'index'])
        ->name('admin.system.notifications');
    
    // 웹훅 관리
    Route::get('/webhooks', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'webhooks'])
        ->name('admin.system.notifications.webhooks');
    Route::get('/webhooks/create', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'createWebhook'])
        ->name('admin.system.notifications.webhooks.create');
    Route::post('/webhooks', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'storeWebhook'])
        ->name('admin.system.notifications.webhooks.store');
    Route::get('/webhooks/{id}/edit', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'editWebhook'])
        ->name('admin.system.notifications.webhooks.edit');
    Route::put('/webhooks/{id}', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'updateWebhook'])
        ->name('admin.system.notifications.webhooks.update');
    Route::delete('/webhooks/{id}', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'deleteWebhook'])
        ->name('admin.system.notifications.webhooks.delete');
    Route::post('/webhooks/{id}/test', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'testWebhook'])
        ->name('admin.system.notifications.webhooks.test');
    Route::get('/webhooks/{channel}/subscriptions', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'webhookSubscriptions'])
        ->name('admin.system.notifications.webhooks.subscriptions');
    Route::post('/webhooks/{channel}/subscriptions', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'saveWebhookSubscriptions'])
        ->name('admin.system.notifications.webhooks.subscriptions.save');
    
    // 이벤트 채널 설정
    Route::get('/event-channels', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'eventChannels'])
        ->name('admin.system.notifications.event-channels');
    Route::post('/event-channels', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'saveEventChannels'])
        ->name('admin.system.notifications.event-channels.save');
    
    // 푸시 알림 설정
    Route::get('/push', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'pushSettings'])
        ->name('admin.system.notifications.push');
    Route::post('/push/vapid', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'generateVapidKeys'])
        ->name('admin.system.notifications.push.vapid');
    
    // 브로드캐스트
    Route::post('/broadcast', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'broadcast'])
        ->name('admin.system.notifications.broadcast');
    
    // 통계
    Route::get('/statistics', [\Jiny\Admin\Http\Controllers\Admin\AdminNotificationSettings::class, 'statistics'])
        ->name('admin.system.notifications.statistics');
});

// Admin CAPTCHA Logs Routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/system/user/captcha')->group(function () {
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogs::class, 'index'])
            ->name('admin.system.captcha.logs');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogsCreate::class)
            ->name('admin.system.captcha.logs.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogsEdit::class)
            ->name('admin.system.captcha.logs.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogsShow::class)
            ->name('admin.system.captcha.logs.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogsDelete::class)
            ->name('admin.system.captcha.logs.delete');
        
        Route::get('/list', [\Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogs::class, 'list'])
            ->name('admin.system.captcha.logs.list');
        
        Route::get('/export', [\Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogs::class, 'export'])
            ->name('admin.system.captcha.logs.export');
        
        Route::post('/block-ip', [\Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogs::class, 'blockIp'])
            ->name('admin.system.captcha.logs.block');
        
        Route::post('/cleanup', [\Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs\AdminCaptchaLogs::class, 'cleanupLogs'])
            ->name('admin.system.captcha.logs.cleanup');
    });
});

// Admin SMS Routes
Route::middleware(['web'])->prefix('admin/system/sms')->group(function () {
    // SMS Dashboard Route
    Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminSmsdashboard\AdminSmsdashboard::class)
        ->name('admin.system.sms');
    
    // SMS Provider Routes
    Route::group(['prefix' => 'provider'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider\AdminSmsProvider::class)
            ->name('admin.system.sms.provider');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider\AdminSmsProviderCreate::class)
            ->name('admin.system.sms.provider.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider\AdminSmsProviderEdit::class)
            ->name('admin.system.sms.provider.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider\AdminSmsProviderShow::class)
            ->name('admin.system.sms.provider.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider\AdminSmsProviderDelete::class)
            ->name('admin.system.sms.provider.delete');
    });
    
    // SMS Send Routes  
    Route::group(['prefix' => 'send'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSend::class)
            ->name('admin.system.sms.send');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSendCreate::class)
            ->name('admin.system.sms.send.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSendEdit::class)
            ->name('admin.system.sms.send.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSendShow::class)
            ->name('admin.system.sms.send.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSendDelete::class)
            ->name('admin.system.sms.send.delete');
        
        // SMS 발송 액션 라우트 
        Route::post('/{id}/send', [\Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSend::class, 'send'])
            ->name('admin.system.sms.send.action');
        
        Route::post('/bulk-send', [\Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSend::class, 'sendBulk'])
            ->name('admin.system.sms.send.bulk');
        
        Route::post('/{id}/resend', [\Jiny\Admin\Http\Controllers\Admin\AdminSmsSend\AdminSmsSend::class, 'resend'])
            ->name('admin.system.sms.send.resend');
    });
    
    // SMS Queue Routes
    Route::group(['prefix' => 'queue'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminSmsqueue\AdminSmsqueue::class)
            ->name('admin.system.sms.queue');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminSmsqueue\AdminSmsqueueCreate::class)
            ->name('admin.system.sms.queue.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminSmsqueue\AdminSmsqueueEdit::class)
            ->name('admin.system.sms.queue.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminSmsqueue\AdminSmsqueueShow::class)
            ->name('admin.system.sms.queue.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminSmsqueue\AdminSmsqueueDelete::class)
            ->name('admin.system.sms.queue.delete');
    });
});

// Admin Iptracking Routes
Route::middleware(['web'])->prefix('admin/system')->group(function () {
    Route::group(['prefix' => 'iptracking'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminIptracking\AdminIptracking::class)
            ->name('admin.system.iptracking');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminIptracking\AdminIptrackingCreate::class)
            ->name('admin.system.iptracking.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminIptracking\AdminIptrackingEdit::class)
            ->name('admin.system.iptracking.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIptracking\AdminIptrackingShow::class)
            ->name('admin.system.iptracking.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIptracking\AdminIptrackingDelete::class)
            ->name('admin.system.iptracking.delete');
    });
});

// Admin Ipblacklist Routes
Route::middleware(['web'])->prefix('admin/system')->group(function () {
    Route::group(['prefix' => 'ipblacklist'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminIpblacklist\AdminIpblacklist::class)
            ->name('admin.system.ipblacklist');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminIpblacklist\AdminIpblacklistCreate::class)
            ->name('admin.system.ipblacklist.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminIpblacklist\AdminIpblacklistEdit::class)
            ->name('admin.system.ipblacklist.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIpblacklist\AdminIpblacklistShow::class)
            ->name('admin.system.ipblacklist.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIpblacklist\AdminIpblacklistDelete::class)
            ->name('admin.system.ipblacklist.delete');
    });
});

// Admin Ipwhitelist Routes
Route::middleware(['web'])->prefix('admin/system')->group(function () {
    Route::group(['prefix' => 'ipwhitelist'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminIpwhitelist\AdminIpwhitelist::class)
            ->name('admin.system.ipwhitelist');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminIpwhitelist\AdminIpwhitelistCreate::class)
            ->name('admin.system.ipwhitelist.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminIpwhitelist\AdminIpwhitelistEdit::class)
            ->name('admin.system.ipwhitelist.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIpwhitelist\AdminIpwhitelistShow::class)
            ->name('admin.system.ipwhitelist.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminIpwhitelist\AdminIpwhitelistDelete::class)
            ->name('admin.system.ipwhitelist.delete');
    });
});



// Admin Webhook Routes
Route::middleware(['web'])->prefix('admin/system/webhook')->group(function () {
    // 웹훅 대시보드
    Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminWebhookdashboard\AdminWebhookdashboard::class)
        ->name('admin.system.webhook');
    
    // 웹훅 채널 관리
    Route::group(['prefix' => 'channels'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminWebhookchannels\AdminWebhookchannels::class)
            ->name('admin.system.webhook.channels');
        
        Route::get('/create', \Jiny\Admin\Http\Controllers\Admin\AdminWebhookchannels\AdminWebhookchannelsCreate::class)
            ->name('admin.system.webhook.channels.create');
        
        Route::get('/{id}/edit', \Jiny\Admin\Http\Controllers\Admin\AdminWebhookchannels\AdminWebhookchannelsEdit::class)
            ->name('admin.system.webhook.channels.edit');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminWebhookchannels\AdminWebhookchannelsShow::class)
            ->name('admin.system.webhook.channels.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminWebhookchannels\AdminWebhookchannelsDelete::class)
            ->name('admin.system.webhook.channels.delete');
        
        Route::post('/{id}/test', [\Jiny\Admin\Http\Controllers\Admin\AdminWebhookchannels\AdminWebhookchannels::class, 'test'])
            ->name('admin.system.webhook.channels.test');
    });
    
    // 웹훅 로그
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', \Jiny\Admin\Http\Controllers\Admin\AdminWebhooklogs\AdminWebhooklogs::class)
            ->name('admin.system.webhook.logs');
        
        Route::get('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminWebhooklogs\AdminWebhooklogsShow::class)
            ->name('admin.system.webhook.logs.show');
        
        Route::delete('/{id}', \Jiny\Admin\Http\Controllers\Admin\AdminWebhooklogs\AdminWebhooklogsDelete::class)
            ->name('admin.system.webhook.logs.delete');
    });
});

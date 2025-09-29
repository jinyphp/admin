<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUser2fa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Models\User;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Services\TwoFactorAuthService;
use Jiny\Admin\Services\NotificationService;

/**
 * AdminUser2faEdit Controller
 * 
 * 2FA 설정 편집 및 관리 기능을 제공합니다.
 * TwoFactorAuthService를 사용하여 모든 2FA 관련 작업을 처리합니다.
 */
class AdminUser2faEdit extends Controller
{
    private $jsonData;
    private $twoFactorService;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
        
        // 2FA 서비스 초기화
        $this->twoFactorService = new TwoFactorAuthService();
    }

    /**
     * Show the form for editing 2FA settings
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        // 세션에서 임시 데이터 가져오기
        $sessionData = $this->twoFactorService->getFromSession($user->id, [
            'secret',
            'qr',
            'backup',
            'regenerated_backup_codes',
            'new_secret',
            'new_qr',
            'regenerating'
        ]);
        
        // 재생성 모드 확인
        if ($sessionData['regenerating'] && $sessionData['new_secret'] && $sessionData['new_qr']) {
            // 재생성 중인 경우 새로운 데이터 사용
            $secret = $sessionData['new_secret'];
            $qrCodeImage = $sessionData['new_qr'];
            $backupCodes = $sessionData['backup'];
            $isRegenerating = true;
        } else {
            // 일반 모드
            $secret = $sessionData['secret'];
            $qrCodeImage = $sessionData['qr'];
            $backupCodes = $sessionData['backup'];
            $isRegenerating = false;
        }

        // 재생성된 백업 코드 확인
        if ($sessionData['regenerated_backup_codes']) {
            $backupCodes = $sessionData['regenerated_backup_codes'];
            $this->twoFactorService->clearSession($user->id, ['regenerated_backup_codes']);
        }
        
        // 2FA 상태 정보 가져오기
        $twoFactorStatus = $this->twoFactorService->getStatus($user);

        return view('jiny-admin::admin.admin_user2fa.edit', compact(
            'user', 'secret', 'qrCodeImage', 'backupCodes', 'twoFactorStatus', 'isRegenerating'
        ));
    }

    /**
     * Generate QR code and backup codes for initial setup
     */
    public function generate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 2FA가 이미 활성화된 경우 - QR 코드 숨기기
        if ($user->two_factor_enabled) {
            // 세션에서 QR 코드 정보 삭제
            $this->twoFactorService->clearSession($user->id, ['secret', 'qr']);
            
            return redirect()->route('admin.user.2fa.edit', $id)
                ->with('info', 'QR 코드가 숨겨졌습니다.');
        }

        // 2FA 초기 설정 생성
        $setupData = $this->twoFactorService->setupTwoFactor($user);

        // 세션에 임시 저장
        $this->twoFactorService->storeInSession($user->id, [
            'secret' => $setupData['secret'],
            'qr' => $setupData['qrCodeImage'],
            'backup' => $setupData['backupCodes']
        ]);

        return redirect()->route('admin.user.2fa.edit', $id)
            ->with('info', 'QR 코드와 백업 코드가 생성되었습니다. 인증 앱으로 QR 코드를 스캔하고 인증 코드를 입력해주세요.');
    }

    /**
     * Show QR code for already enabled 2FA
     */
    public function showQr(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 2FA가 활성화되어 있지 않으면 설정 페이지로 리다이렉트
        if (!$user->two_factor_enabled) {
            return redirect()->route('admin.user.2fa.edit', $id)
                ->with('error', '2FA가 활성화되어 있지 않습니다.');
        }

        // 저장된 secret으로 QR 코드 재생성
        $secret = decrypt($user->two_factor_secret);
        $companyName = config('app.name', 'JinyAdmin');
        $qrCodeImage = $this->twoFactorService->generateQRCodeImage($companyName, $user->email, $secret);

        // 세션에 임시 저장 (보안상 일시적으로만 표시)
        $this->twoFactorService->storeInSession($user->id, [
            'secret' => $secret,
            'qr' => $qrCodeImage
        ]);
        
        // 활동 로그 기록
        if (method_exists($this, 'logActivity')) {
            $this->logActivity($user, 'qr_displayed', 'QR 코드가 재표시되었습니다');
        }

        return redirect()->route('admin.user.2fa.edit', $id)
            ->with('warning', 'QR 코드가 표시되었습니다. 보안을 위해 사용 후 숨기기를 권장합니다.');
    }

    /**
     * Regenerate QR code with new secret
     * QR 코드와 시크릿 키를 완전히 재생성합니다.
     * 주의: 기존 Google Authenticator 설정이 무효화됩니다.
     */
    public function regenerateQr(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Hook: 재생성 전 확인
        if (method_exists($this, 'hookBeforeRegenerateQr')) {
            $result = $this->hookBeforeRegenerateQr($user);
            if ($result === false) {
                return redirect()->route('admin.user.2fa.edit', $id)
                    ->with('error', 'QR 코드 재생성이 차단되었습니다.');
            }
        }

        // 새로운 secret과 QR 코드 생성
        $setupData = $this->twoFactorService->setupTwoFactor($user);

        // 세션에 임시 저장 (아직 DB에는 저장하지 않음)
        $this->twoFactorService->storeInSession($user->id, [
            'new_secret' => $setupData['secret'],
            'new_qr' => $setupData['qrCodeImage'],
            'regenerating' => true  // 재생성 모드 플래그
        ]);

        // Hook: 재생성 후 처리
        if (method_exists($this, 'hookAfterRegenerateQr')) {
            $this->hookAfterRegenerateQr($user, $setupData);
        }

        return redirect()->route('admin.user.2fa.edit', $id)
            ->with('danger', 'QR 코드가 재생성되었습니다. 새 코드를 스캔하고 인증 코드를 입력하여 확인해주세요. 확인하지 않으면 기존 설정이 유지됩니다.');
    }

    /**
     * Confirm regenerated QR code
     * 재생성된 QR 코드를 확인하고 저장합니다.
     */
    public function confirmRegenerateQr(Request $request, $id)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6'
        ]);

        $user = User::findOrFail($id);

        // 세션에서 재생성 데이터 가져오기
        $sessionData = $this->twoFactorService->getFromSession($user->id, ['new_secret', 'regenerating']);
        
        if (!$sessionData['regenerating'] || !$sessionData['new_secret']) {
            return redirect()->route('admin.user.2fa.edit', $id)
                ->with('error', '재생성 세션이 만료되었습니다. 다시 시도해주세요.');
        }

        // 새 secret으로 인증 코드 확인
        if (!$this->twoFactorService->verifyCode($sessionData['new_secret'], $request->verification_code)) {
            return redirect()->route('admin.user.2fa.edit', $id)
                ->with('error', '인증 코드가 올바르지 않습니다.');
        }

        // Hook: 확인 전 처리
        if (method_exists($this, 'hookBeforeConfirmRegenerate')) {
            $this->hookBeforeConfirmRegenerate($user, $sessionData['new_secret']);
        }

        // 새 secret으로 업데이트
        $user->two_factor_secret = encrypt($sessionData['new_secret']);
        $user->two_factor_confirmed_at = now();
        $user->save();

        // 세션 정리
        $this->twoFactorService->clearSession($user->id);

        // 활동 로그 기록
        if (method_exists($this, 'logActivity')) {
            $this->logActivity($user, 'qr_regenerated', 'QR 코드가 재생성되고 확인되었습니다');
        }

        // Hook: 확인 후 처리
        if (method_exists($this, 'hookAfterConfirmRegenerate')) {
            $this->hookAfterConfirmRegenerate($user);
        }

        // 알림 발송
        $notificationService = app(NotificationService::class);
        $notificationService->notify2FAChanged($user->email, 'regenerated');

        return redirect()->route('admin.user.2fa.edit', $id)
            ->with('success', 'QR 코드가 성공적으로 재생성되었습니다. 기존 설정은 더 이상 사용할 수 없습니다.');
    }

    /**
     * Hook: QR 코드 재생성 전 처리
     * @return bool false를 반환하면 재생성 중단
     */
    protected function hookBeforeRegenerateQr($user)
    {
        // 커스텀 로직 구현 가능
        // 예: 관리자 권한 확인, 재생성 횟수 제한 등
        return true;
    }

    /**
     * Hook: QR 코드 재생성 후 처리
     */
    protected function hookAfterRegenerateQr($user, $setupData)
    {
        // 커스텀 로직 구현 가능
        // 예: 이메일 알림 발송, 로그 기록 등
    }

    /**
     * Hook: 재생성 확인 전 처리
     */
    protected function hookBeforeConfirmRegenerate($user, $newSecret)
    {
        // 커스텀 로직 구현 가능
    }

    /**
     * Hook: 재생성 확인 후 처리
     */
    protected function hookAfterConfirmRegenerate($user)
    {
        // 커스텀 로직 구현 가능
        // 예: 보안 알림 발송
    }

    /**
     * Store and verify 2FA setup
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6',
            'secret' => 'required|string',
            'backup_codes' => 'required|array',
        ]);

        $user = User::findOrFail($id);

        // 2FA 활성화 시도
        $success = $this->twoFactorService->enableTwoFactor(
            $user,
            $request->secret,
            $request->backup_codes,
            $request->verification_code
        );

        if (!$success) {
            // 인증 실패 시 세션 데이터 유지
            return redirect()->route('admin.user.2fa.edit', $id)
                ->with('error', '인증 코드가 올바르지 않습니다. 다시 시도해주세요.')
                ->withInput();
        }

        // 2FA 활성화 알림 발송
        app(NotificationService::class)->notify2FAChanged($id, 'enabled');

        // 성공 시 세션 데이터 삭제
        $this->twoFactorService->clearSession($user->id);

        return redirect()->route('admin.user.2fa.edit', $id)
            ->with('success', '2FA가 성공적으로 활성화되었습니다.');
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackup(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 백업 코드 재생성
        $backupCodes = $this->twoFactorService->regenerateBackupCodes($user);
        
        if (!$backupCodes) {
            return redirect()->route('admin.user.2fa.edit', $id)
                ->with('error', '2FA가 활성화되어 있지 않습니다.');
        }

        // 세션에 저장하여 표시
        $this->twoFactorService->storeInSession($user->id, [
            'regenerated_backup_codes' => $backupCodes
        ]);

        return redirect()->route('admin.user.2fa.edit', $id)
            ->with('success', '백업 코드가 재생성되었습니다. 새로운 코드를 안전한 곳에 보관하세요.');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$user->two_factor_enabled) {
            return redirect()->route('admin.user.2fa.edit', $id)
                ->with('error', '2FA가 이미 비활성화되어 있습니다.');
        }

        // 2FA 비활성화
        $this->twoFactorService->disableTwoFactor($user, false);

        // 2FA 비활성화 알림 발송
        app(NotificationService::class)->notify2FAChanged($id, 'disabled');

        // 세션 데이터 삭제
        $this->twoFactorService->clearSession($user->id);

        return redirect()->route('admin.user.2fa.edit', $id)
            ->with('success', '2FA가 비활성화되었습니다.');
    }

    /**
     * Force disable 2FA (for admin use)
     */
    public function forceDisable($id)
    {
        $user = User::findOrFail($id);

        // 강제로 2FA 비활성화
        $this->twoFactorService->disableTwoFactor($user, true);

        // 2FA 강제 비활성화 알림 발송  
        app(NotificationService::class)->notify2FAChanged($id, 'force_disabled');

        // 세션 데이터 삭제
        $this->twoFactorService->clearSession($user->id);

        return redirect()->route('admin.user.2fa.index')
            ->with('success', $user->name.'님의 2FA가 강제로 비활성화되었습니다.');
    }

    /**
     * Check 2FA status (AJAX)
     */
    public function status($id)
    {
        $user = User::findOrFail($id);
        
        // 2FA 상태 정보 가져오기
        $status = $this->twoFactorService->getStatus($user);

        return response()->json($status);
    }
}

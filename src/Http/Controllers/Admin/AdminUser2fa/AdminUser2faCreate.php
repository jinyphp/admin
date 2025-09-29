<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUser2fa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jiny\Admin\Models\User;
use Jiny\Admin\Services\JsonConfigService;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * AdminUser2faCreate Controller
 */
class AdminUser2faCreate extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 2FA 설정 페이지 처리
     */
    public function __invoke(Request $request, $id = null)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 사용자 확인
        $user = $id ? User::findOrFail($id) : Auth::user();

        // 이미 2FA가 설정되어 있으면 편집 페이지로 이동
        if ($user->two_factor_enabled) {
            return redirect()->route('admin.user.2fa.edit', $user->id);
        }

        // Google2FA 객체 생성
        $google2fa = new Google2FA;

        // 비밀 키 생성
        $secret = $google2fa->generateSecretKey();

        // QR 코드 URL 생성
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'Jiny Admin'),
            $user->email,
            $secret
        );

        // QR 코드 이미지 생성 (base64)
        $qrCodeImage = 'data:image/svg+xml;base64,'.base64_encode(
            QrCode::size(200)
                ->generate($qrCodeUrl)
        );

        // 백업 코드 생성 (8개)
        $backupCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $backupCodes[] = strtoupper(Str::random(4).'-'.Str::random(4));
        }

        // 세션에 임시 저장
        session([
            '2fa_secret' => $secret,
            '2fa_backup_codes' => $backupCodes,
            '2fa_user_id' => $user->id,
        ]);

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.create view 경로 확인
        if (! isset($this->jsonData['template']['create'])) {
            $debugInfo = 'JSON template section: '.json_encode($this->jsonData['template'] ?? 'not found');

            return response('Error: 화면을 출력하기 위한 template.create 설정이 필요합니다. '.$debugInfo, 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUser2fa.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'user' => $user,
            'secret' => $secret,
            'qrCodeImage' => $qrCodeImage,
            'backupCodes' => $backupCodes,
            'title' => '2차 인증 설정',
            'subtitle' => 'Google Authenticator를 사용한 2차 인증을 설정합니다.',
        ]);
    }

    /**
     * 생성폼이 실행될때 호출됩니다.
     */
    public function hookCreating($wire, $value)
    {
        // 2FA 설정에는 기본값이 필요 없음
        return [];
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire, $form)
    {
        // 2FA 인증 코드 확인
        $google2fa = new Google2FA;
        $secret = session('2fa_secret');

        if (! $google2fa->verifyKey($secret, $form['verification_code'] ?? '')) {
            $wire->dispatch('notify', [
                'type' => 'error',
                'message' => '인증 코드가 올바르지 않습니다.',
            ]);

            return false;
        }

        // 사용자 정보 업데이트
        $userId = session('2fa_user_id');
        $user = User::find($userId);

        if ($user) {
            $user->two_factor_secret = encrypt($secret);
            $user->two_factor_recovery_codes = encrypt(json_encode(session('2fa_backup_codes')));
            $user->two_factor_confirmed_at = now();
            $user->two_factor_enabled = true;
            $user->save();

            // 세션 정리
            session()->forget(['2fa_secret', '2fa_backup_codes', '2fa_user_id']);

            $wire->dispatch('notify', [
                'type' => 'success',
                'message' => '2차 인증이 성공적으로 설정되었습니다.',
            ]);
        }

        return false; // DB 직접 삽입 방지
    }

    /**
     * 신규 데이터 DB 삽입후에 호출됩니다.
     */
    public function hookStored($wire, $form)
    {
        // 필요시 추가 작업 수행
        return $form;
    }
}

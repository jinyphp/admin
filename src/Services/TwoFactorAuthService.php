<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;
use Jiny\Admin\Models\User;
use Illuminate\Support\Facades\DB;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Two-Factor Authentication Service
 * 
 * 2FA 관련 모든 기능을 처리하는 서비스 클래스
 * QR 코드 생성, 백업 코드 관리, 인증 검증 등을 담당합니다.
 * 
 * @package Jiny\Admin\App\Services
 * @author  @jiny/admin Team
 * @since   1.0.0
 */
class TwoFactorAuthService
{
    /**
     * Google2FA 인스턴스
     *
     * @var Google2FAQRCode
     */
    protected $google2fa;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->google2fa = new Google2FAQRCode();
    }

    /**
     * 새로운 2FA 비밀키 생성
     *
     * @param  int  $length  비밀키 길이 (기본: 32)
     * @return string
     */
    public function generateSecretKey($length = 32)
    {
        return $this->google2fa->generateSecretKey($length);
    }

    /**
     * QR 코드 URL 생성
     *
     * @param  string  $companyName  회사/앱 이름
     * @param  string  $email  사용자 이메일
     * @param  string  $secret  비밀키
     * @return string
     */
    public function getQRCodeUrl($companyName, $email, $secret)
    {
        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $email,
            $secret
        );
    }

    /**
     * QR 코드 이미지 생성 (Base64 인코딩)
     *
     * @param  string  $companyName  회사/앱 이름
     * @param  string  $email  사용자 이메일
     * @param  string  $secret  비밀키
     * @param  int  $size  QR 코드 크기 (기본: 200)
     * @return string  Base64 인코딩된 이미지 또는 URL
     */
    public function generateQRCodeImage($companyName, $email, $secret, $size = 200)
    {
        // otpauth URL 생성
        $otpauthUrl = $this->getQRCodeUrl($companyName, $email, $secret);
        
        // QR Server API 사용 (가장 안정적인 방법)
        $qrServerUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'size' => $size . 'x' . $size,
            'data' => $otpauthUrl,
            'format' => 'svg'
        ]);
        
        return $qrServerUrl;
    }

    /**
     * 백업 코드 생성
     *
     * @param  int  $count  생성할 코드 개수 (기본: 8)
     * @return array
     */
    public function generateBackupCodes($count = 8)
    {
        $backupCodes = [];
        for ($i = 0; $i < $count; $i++) {
            $backupCodes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }
        return $backupCodes;
    }

    /**
     * 인증 코드 검증
     *
     * @param  string  $secret  비밀키
     * @param  string  $code  인증 코드
     * @param  int|null  $window  시간 창 (기본: null)
     * @return bool
     */
    public function verifyCode($secret, $code, $window = null)
    {
        return $this->google2fa->verifyKey($secret, $code, $window);
    }

    /**
     * 백업 코드 검증 및 사용
     *
     * @param  User  $user  사용자 모델
     * @param  string  $code  백업 코드
     * @return bool
     */
    public function verifyBackupCode(User $user, $code)
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $backupCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $code = strtoupper($code);

        if (in_array($code, $backupCodes)) {
            // 사용된 코드 제거
            $backupCodes = array_values(array_diff($backupCodes, [$code]));
            
            // 업데이트된 코드 저장
            $user->two_factor_recovery_codes = encrypt(json_encode($backupCodes));
            $user->save();

            // 사용 로그 기록
            $this->logBackupCodeUsage($user, $code);

            return true;
        }

        return false;
    }

    /**
     * 2FA 초기 설정
     *
     * @param  User  $user  사용자 모델
     * @return array  설정 정보 (secret, qrCode, backupCodes)
     */
    public function setupTwoFactor(User $user)
    {
        // 비밀키 생성
        $secret = $this->generateSecretKey();

        // QR 코드 이미지 생성 (직접 이미지 생성)
        $companyName = config('app.name', 'Laravel');
        $qrCodeImage = $this->generateQRCodeImage($companyName, $user->email, $secret);

        // 백업 코드 생성
        $backupCodes = $this->generateBackupCodes();

        return [
            'secret' => $secret,
            'qrCodeImage' => $qrCodeImage,
            'backupCodes' => $backupCodes,
        ];
    }

    /**
     * 2FA 활성화
     *
     * @param  User  $user  사용자 모델
     * @param  string  $secret  비밀키
     * @param  array  $backupCodes  백업 코드
     * @param  string  $verificationCode  검증 코드
     * @return bool
     */
    public function enableTwoFactor(User $user, $secret, array $backupCodes, $verificationCode)
    {
        // 인증 코드 검증
        if (!$this->verifyCode($secret, $verificationCode)) {
            return false;
        }

        // 2FA 설정 저장
        $user->two_factor_secret = encrypt($secret);
        $user->two_factor_recovery_codes = encrypt(json_encode($backupCodes));
        $user->two_factor_confirmed_at = now();
        $user->two_factor_enabled = true;
        $user->save();

        // 활성화 로그 기록
        $this->logTwoFactorAction($user, 'enabled', '2FA가 활성화되었습니다');

        return true;
    }

    /**
     * 2FA 비활성화
     *
     * @param  User  $user  사용자 모델
     * @param  bool  $force  강제 비활성화 여부
     * @return void
     */
    public function disableTwoFactor(User $user, $force = false)
    {
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_enabled = false;
        $user->last_2fa_used_at = null;
        $user->save();

        // 비활성화 로그 기록
        $action = $force ? 'force_disabled' : 'disabled';
        $description = $force ? '2FA가 강제로 비활성화되었습니다' : '2FA가 비활성화되었습니다';
        $this->logTwoFactorAction($user, $action, $description);
    }

    /**
     * 백업 코드 재생성
     *
     * @param  User  $user  사용자 모델
     * @return array|null  새로운 백업 코드
     */
    public function regenerateBackupCodes(User $user)
    {
        if (!$user->two_factor_enabled) {
            return null;
        }

        $backupCodes = $this->generateBackupCodes();
        
        $user->two_factor_recovery_codes = encrypt(json_encode($backupCodes));
        $user->save();

        // 재생성 로그 기록
        $this->logTwoFactorAction($user, 'backup_codes_regenerated', '백업 코드가 재생성되었습니다');

        return $backupCodes;
    }

    /**
     * 2FA 상태 확인
     *
     * @param  User  $user  사용자 모델
     * @return array
     */
    public function getStatus(User $user)
    {
        $backupCodesCount = 0;
        if ($user->two_factor_recovery_codes) {
            $backupCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            $backupCodesCount = count($backupCodes);
        }

        return [
            'enabled' => $user->two_factor_enabled,
            'confirmed_at' => $user->two_factor_confirmed_at,
            'last_used_at' => $user->last_2fa_used_at,
            'backup_codes_count' => $backupCodesCount,
            'has_secret' => !empty($user->two_factor_secret),
        ];
    }

    /**
     * 2FA 사용 시간 업데이트
     *
     * @param  User  $user  사용자 모델
     * @return void
     */
    public function updateLastUsedAt(User $user)
    {
        $user->last_2fa_used_at = now();
        $user->save();
    }

    /**
     * 2FA 작업 로그 기록
     *
     * @param  User  $user  사용자 모델
     * @param  string  $action  작업 유형
     * @param  string  $description  설명
     * @param  array|null  $metadata  추가 메타데이터
     * @return void
     */
    protected function logTwoFactorAction(User $user, $action, $description, $metadata = null)
    {
        DB::table('admin_2fa_codes')->insert([
            'user_id' => $user->id,
            'method' => 'google2fa',
            'enabled' => $user->two_factor_enabled,
            'last_used_at' => $user->last_2fa_used_at,
            'backup_codes_used' => 0,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 일반 로그에도 기록
        DB::table('admin_user_logs')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'action' => '2fa_' . $action,
            'description' => $description,
            'details' => json_encode([
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email ?? 'system',
                'metadata' => $metadata,
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'logged_at' => now(),
            'created_at' => now(),
        ]);
    }

    /**
     * 백업 코드 사용 로그 기록
     *
     * @param  User  $user  사용자 모델
     * @param  string  $code  사용된 백업 코드
     * @return void
     */
    protected function logBackupCodeUsage(User $user, $code)
    {
        // 사용된 백업 코드 수 업데이트
        DB::table('admin_2fa_codes')
            ->where('user_id', $user->id)
            ->increment('backup_codes_used');

        // 로그 기록
        $this->logTwoFactorAction(
            $user,
            'backup_code_used',
            '백업 코드가 사용되었습니다',
            ['code' => substr($code, 0, 4) . '****'] // 보안을 위해 일부만 저장
        );
    }

    /**
     * 세션에 임시 2FA 데이터 저장
     *
     * @param  int  $userId  사용자 ID
     * @param  array  $data  저장할 데이터
     * @return void
     */
    public function storeInSession($userId, array $data)
    {
        foreach ($data as $key => $value) {
            session(['2fa_' . $key . '_' . $userId => $value]);
        }
    }

    /**
     * 세션에서 임시 2FA 데이터 가져오기
     *
     * @param  int  $userId  사용자 ID
     * @param  array  $keys  가져올 키 목록
     * @return array
     */
    public function getFromSession($userId, array $keys)
    {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = session('2fa_' . $key . '_' . $userId);
        }
        return $data;
    }

    /**
     * 세션에서 임시 2FA 데이터 삭제
     *
     * @param  int  $userId  사용자 ID
     * @param  array|null  $keys  삭제할 키 목록 (null이면 모든 키 삭제)
     * @return void
     */
    public function clearSession($userId, ?array $keys = null)
    {
        if ($keys === null) {
            $keys = ['secret', 'qr', 'backup', 'regenerated_backup_codes'];
        }

        $sessionKeys = [];
        foreach ($keys as $key) {
            $sessionKeys[] = '2fa_' . $key . '_' . $userId;
        }

        session()->forget($sessionKeys);
    }

    /**
     * 6자리 숫자 코드 생성
     *
     * @return string
     */
    public function generateNumericCode()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * SMS 코드 발송
     *
     * @param  User  $user  사용자 모델
     * @param  string|null  $phoneNumber  전화번호 (null이면 user의 phone_number 사용)
     * @return array  결과 정보
     */
    public function sendSmsCode(User $user, $phoneNumber = null)
    {
        // 재발송 제한 확인 (1분)
        if ($user->last_code_sent_at && $user->last_code_sent_at->diffInSeconds(now()) < 60) {
            $remainingSeconds = 60 - $user->last_code_sent_at->diffInSeconds(now());
            return [
                'success' => false,
                'message' => "{$remainingSeconds}초 후에 다시 시도해주세요.",
                'remaining_seconds' => $remainingSeconds
            ];
        }

        $phone = $phoneNumber ?? $user->phone_number;
        
        if (!$phone) {
            return [
                'success' => false,
                'message' => '전화번호가 등록되지 않았습니다.'
            ];
        }

        // 6자리 코드 생성
        $code = $this->generateNumericCode();

        // 기존 미사용 코드 삭제
        DB::table('admin_2fa_codes')
            ->where('user_id', $user->id)
            ->where('method', 'sms')
            ->where('used', false)
            ->delete();

        // 새 코드 저장
        DB::table('admin_2fa_codes')->insert([
            'user_id' => $user->id,
            'method' => 'sms',
            'code' => $code,
            'destination' => $phone,
            'expires_at' => Carbon::now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // SMS 발송 (실제 구현시 SMS 서비스 사용)
        $sent = $this->sendSms($phone, $code);

        if ($sent) {
            // 발송 시간 업데이트
            $user->last_code_sent_at = now();
            $user->save();

            // 로그 기록
            $this->logTwoFactorAction($user, 'sms_code_sent', 'SMS 인증 코드가 발송되었습니다', [
                'phone' => substr($phone, 0, -4) . '****'
            ]);

            return [
                'success' => true,
                'message' => 'SMS 인증 코드가 발송되었습니다.',
                'expires_in' => 300 // 5분
            ];
        }

        return [
            'success' => false,
            'message' => 'SMS 발송에 실패했습니다. 잠시 후 다시 시도해주세요.'
        ];
    }

    /**
     * 이메일 코드 발송
     *
     * @param  User  $user  사용자 모델
     * @return array  결과 정보
     */
    public function sendEmailCode(User $user)
    {
        // 재발송 제한 확인 (1분)
        if ($user->last_code_sent_at && $user->last_code_sent_at->diffInSeconds(now()) < 60) {
            $remainingSeconds = 60 - $user->last_code_sent_at->diffInSeconds(now());
            return [
                'success' => false,
                'message' => "{$remainingSeconds}초 후에 다시 시도해주세요.",
                'remaining_seconds' => $remainingSeconds
            ];
        }

        // 6자리 코드 생성
        $code = $this->generateNumericCode();

        // 기존 미사용 코드 삭제
        DB::table('admin_2fa_codes')
            ->where('user_id', $user->id)
            ->where('method', 'email')
            ->where('used', false)
            ->delete();

        // 새 코드 저장
        DB::table('admin_2fa_codes')->insert([
            'user_id' => $user->id,
            'method' => 'email',
            'code' => $code,
            'destination' => $user->email,
            'expires_at' => Carbon::now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 이메일 발송
        try {
            Mail::send('jiny-admin::emails.2fa-code', [
                'user' => $user,
                'code' => $code,
                'expires_in' => 5
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('[' . config('app.name') . '] 2FA 인증 코드');
            });

            // 발송 시간 업데이트
            $user->last_code_sent_at = now();
            $user->save();

            // 로그 기록
            $this->logTwoFactorAction($user, 'email_code_sent', '이메일 인증 코드가 발송되었습니다', [
                'email' => $user->email
            ]);

            return [
                'success' => true,
                'message' => '이메일로 인증 코드가 발송되었습니다.',
                'expires_in' => 300 // 5분
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '이메일 발송에 실패했습니다. 잠시 후 다시 시도해주세요.'
            ];
        }
    }

    /**
     * SMS 코드 검증
     *
     * @param  User  $user  사용자 모델
     * @param  string  $code  인증 코드
     * @return bool
     */
    public function verifySmsCode(User $user, $code)
    {
        return $this->verifyTemporaryCode($user, 'sms', $code);
    }

    /**
     * 이메일 코드 검증
     *
     * @param  User  $user  사용자 모델
     * @param  string  $code  인증 코드
     * @return bool
     */
    public function verifyEmailCode(User $user, $code)
    {
        return $this->verifyTemporaryCode($user, 'email', $code);
    }

    /**
     * 임시 코드 검증 (SMS/Email 공통)
     *
     * @param  User  $user  사용자 모델
     * @param  string  $method  인증 방법 (sms/email)
     * @param  string  $code  인증 코드
     * @return bool
     */
    protected function verifyTemporaryCode(User $user, $method, $code)
    {
        $record = DB::table('admin_2fa_codes')
            ->where('user_id', $user->id)
            ->where('method', $method)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            // 실패 시도 횟수 증가
            DB::table('admin_2fa_codes')
                ->where('user_id', $user->id)
                ->where('method', $method)
                ->where('used', false)
                ->increment('attempts');

            return false;
        }

        // 코드를 사용됨으로 표시
        DB::table('admin_2fa_codes')
            ->where('id', $record->id)
            ->update([
                'used' => true,
                'updated_at' => now()
            ]);

        // 2FA 사용 시간 업데이트
        $this->updateLastUsedAt($user);

        // 로그 기록
        $this->logTwoFactorAction($user, "{$method}_code_verified", "{$method} 인증 코드가 확인되었습니다");

        return true;
    }

    /**
     * 실제 SMS 발송 (SMS 서비스 통합 필요)
     *
     * @param  string  $phone  전화번호
     * @param  string  $code  인증 코드
     * @return bool
     */
    protected function sendSms($phone, $code)
    {
        // SMS 서비스 설정 확인
        $smsProvider = config('services.sms.provider');
        
        if (!$smsProvider) {
            // 개발 환경에서는 로그에만 기록
            if (app()->environment('local', 'development')) {
                \Log::info("SMS 2FA Code for {$phone}: {$code}");
                return true;
            }
            return false;
        }

        try {
            // Twilio 예시
            if ($smsProvider === 'twilio') {
                return $this->sendViaTwilio($phone, $code);
            }
            
            // 기타 SMS 서비스 추가 가능
            
            return false;
        } catch (\Exception $e) {
            \Log::error('SMS 발송 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Twilio를 통한 SMS 발송
     *
     * @param  string  $phone  전화번호
     * @param  string  $code  인증 코드
     * @return bool
     */
    protected function sendViaTwilio($phone, $code)
    {
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');
        $twilioFrom = config('services.twilio.from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
            return false;
        }

        $message = sprintf(
            '[%s] 2FA 인증 코드: %s (5분간 유효)',
            config('app.name'),
            $code
        );

        $response = Http::withBasicAuth($twilioSid, $twilioToken)
            ->asForm()
            ->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json",
                [
                    'From' => $twilioFrom,
                    'To' => $phone,
                    'Body' => $message
                ]
            );

        return $response->successful();
    }

    /**
     * 2FA 방법 변경
     *
     * @param  User  $user  사용자 모델
     * @param  string  $method  새로운 방법 (totp/sms/email)
     * @return bool
     */
    public function changeMethod(User $user, $method)
    {
        if (!in_array($method, ['totp', 'sms', 'email'])) {
            return false;
        }

        // SMS 선택 시 전화번호 확인
        if ($method === 'sms' && !$user->phone_number) {
            return false;
        }

        $user->two_factor_method = $method;
        $user->save();

        // 로그 기록
        $this->logTwoFactorAction($user, 'method_changed', "2FA 방법이 {$method}로 변경되었습니다");

        return true;
    }

    /**
     * 백업 코드 다운로드용 텍스트 생성
     *
     * @param  User  $user  사용자 모델
     * @return string|null
     */
    public function generateBackupCodesText(User $user)
    {
        if (!$user->two_factor_recovery_codes) {
            return null;
        }

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $usedCodes = json_decode($user->used_backup_codes ?? '[]', true);

        $text = "=====================================\n";
        $text .= config('app.name') . " - 2FA 백업 코드\n";
        $text .= "=====================================\n\n";
        $text .= "사용자: {$user->name} ({$user->email})\n";
        $text .= "생성일: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $text .= "백업 코드:\n";
        $text .= "-------------------------------------\n";

        foreach ($codes as $code) {
            $status = in_array($code, $usedCodes) ? ' [사용됨]' : '';
            $text .= $code . $status . "\n";
        }

        $text .= "\n-------------------------------------\n";
        $text .= "⚠️ 주의사항:\n";
        $text .= "- 이 코드들을 안전한 곳에 보관하세요.\n";
        $text .= "- 각 코드는 한 번만 사용할 수 있습니다.\n";
        $text .= "- 2FA 앱에 접근할 수 없을 때 사용하세요.\n";

        return $text;
    }

    /**
     * 백업 코드 재생성 (개선된 버전)
     *
     * @param  User  $user  사용자 모델
     * @param  bool  $keepUsed  사용된 코드 기록 유지 여부
     * @return array|null  새로운 백업 코드
     */
    public function regenerateBackupCodesEnhanced(User $user, $keepUsed = false)
    {
        if (!$user->two_factor_enabled) {
            return null;
        }

        $backupCodes = $this->generateBackupCodes();
        
        $user->two_factor_recovery_codes = encrypt(json_encode($backupCodes));
        
        // 사용된 코드 기록 초기화 옵션
        if (!$keepUsed) {
            $user->used_backup_codes = null;
        }
        
        $user->save();

        // 재생성 로그 기록
        $this->logTwoFactorAction($user, 'backup_codes_regenerated', '백업 코드가 재생성되었습니다', [
            'count' => count($backupCodes),
            'keep_used' => $keepUsed
        ]);

        return $backupCodes;
    }

    /**
     * 백업 코드 검증 (개선된 버전)
     *
     * @param  User  $user  사용자 모델
     * @param  string  $code  백업 코드
     * @return bool
     */
    public function verifyBackupCodeEnhanced(User $user, $code)
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $backupCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $usedCodes = json_decode($user->used_backup_codes ?? '[]', true);
        $code = strtoupper($code);

        // 이미 사용된 코드인지 확인
        if (in_array($code, $usedCodes)) {
            return false;
        }

        if (in_array($code, $backupCodes)) {
            // 사용된 코드로 표시
            $usedCodes[] = $code;
            $user->used_backup_codes = json_encode($usedCodes);
            $user->save();

            // 사용 로그 기록
            $this->logBackupCodeUsage($user, $code);

            return true;
        }

        return false;
    }

    /**
     * 2FA 상태 확인 (개선된 버전)
     *
     * @param  User  $user  사용자 모델
     * @return array
     */
    public function getStatusEnhanced(User $user)
    {
        $backupCodesTotal = 0;
        $backupCodesUsed = 0;
        $backupCodesRemaining = 0;

        if ($user->two_factor_recovery_codes) {
            $backupCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            $usedCodes = json_decode($user->used_backup_codes ?? '[]', true);
            
            $backupCodesTotal = count($backupCodes);
            $backupCodesUsed = count($usedCodes);
            $backupCodesRemaining = $backupCodesTotal - $backupCodesUsed;
        }

        return [
            'enabled' => $user->two_factor_enabled,
            'method' => $user->two_factor_method ?? 'totp',
            'confirmed_at' => $user->two_factor_confirmed_at,
            'last_used_at' => $user->last_2fa_used_at,
            'backup_codes' => [
                'total' => $backupCodesTotal,
                'used' => $backupCodesUsed,
                'remaining' => $backupCodesRemaining
            ],
            'has_secret' => !empty($user->two_factor_secret),
            'has_phone' => !empty($user->phone_number),
            'phone_verified' => $user->phone_verified ?? false
        ];
    }

    /**
     * 활성 2FA 코드 정리 (만료된 코드 삭제)
     *
     * @return int  삭제된 코드 수
     */
    public function cleanupExpiredCodes()
    {
        return DB::table('admin_2fa_codes')
            ->where('expires_at', '<', now())
            ->orWhere('used', true)
            ->delete();
    }
}
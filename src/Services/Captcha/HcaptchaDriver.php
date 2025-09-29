<?php

namespace Jiny\Admin\Services\Captcha;

use Illuminate\Support\Facades\Http;

class HcaptchaDriver implements CaptchaDriverInterface
{
    private string $siteKey;
    private string $secretKey;
    private ?string $lastError = null;
    private string $verifyUrl = 'https://hcaptcha.com/siteverify';

    public function __construct()
    {
        $this->siteKey = config('captcha.drivers.hcaptcha.site_key', '');
        $this->secretKey = config('captcha.drivers.hcaptcha.secret_key', '');
    }

    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    public function getWidget($options = [])
    {
        $theme = $options['theme'] ?? 'light';
        $size = $options['size'] ?? 'normal';
        $tabindex = $options['tabindex'] ?? 0;
        
        return sprintf(
            '<div class="h-captcha" data-sitekey="%s" data-theme="%s" data-size="%s" data-tabindex="%s"></div>',
            $this->siteKey,
            $theme,
            $size,
            $tabindex
        );
    }

    public function getScriptUrl()
    {
        return 'https://js.hcaptcha.com/1/api.js';
    }

    public function verify($token, $ipAddress = null)
    {
        $data = [
            'secret' => $this->secretKey,
            'response' => $token,
        ];
        
        if ($ipAddress) {
            $data['remoteip'] = $ipAddress;
        }
        
        try {
            $response = Http::asForm()->post($this->verifyUrl, $data);
            
            if (!$response->successful()) {
                $this->lastError = 'hCaptcha 서버와 통신할 수 없습니다.';
                return [
                    'success' => false,
                    'error_code' => 'network_error',
                    'error_message' => $this->lastError,
                    'http_status' => $response->status()
                ];
            }
            
            $result = $response->json();
            
            if (!isset($result['success']) || !$result['success']) {
                $errorCodes = $result['error-codes'] ?? ['unknown-error'];
                $this->lastError = $this->translateError($errorCodes[0]);
                return [
                    'success' => false,
                    'error_code' => $errorCodes[0] ?? 'unknown-error',
                    'error_message' => $this->lastError,
                    'error_codes' => $errorCodes
                ];
            }
            
            return [
                'success' => true,
                'challenge_ts' => $result['challenge_ts'] ?? null,
                'hostname' => $result['hostname'] ?? null,
                'credit' => $result['credit'] ?? null
            ];
        } catch (\Exception $e) {
            $this->lastError = 'hCaptcha 검증 중 오류가 발생했습니다: ' . $e->getMessage();
            return [
                'success' => false,
                'error_code' => 'exception',
                'error_message' => $this->lastError
            ];
        }
    }

    public function getErrorMessage(): ?string
    {
        return $this->lastError;
    }

    public function getScore(): ?float
    {
        // hCaptcha는 점수 시스템을 사용하지 않음
        return null;
    }

    private function translateError(string $errorCode): string
    {
        $errors = [
            'missing-input-secret' => 'Secret 키가 누락되었습니다.',
            'invalid-input-secret' => 'Secret 키가 유효하지 않습니다.',
            'missing-input-response' => 'CAPTCHA 응답이 누락되었습니다.',
            'invalid-input-response' => 'CAPTCHA 응답이 유효하지 않습니다.',
            'bad-request' => '잘못된 요청입니다.',
            'invalid-or-already-seen-response' => 'CAPTCHA가 이미 사용되었습니다.',
            'not-using-dummy-passcode' => '테스트 모드에서는 더미 패스코드를 사용해야 합니다.',
            'sitekey-secret-mismatch' => 'Site key와 Secret key가 일치하지 않습니다.',
        ];
        
        return $errors[$errorCode] ?? 'CAPTCHA 검증에 실패했습니다.';
    }
}
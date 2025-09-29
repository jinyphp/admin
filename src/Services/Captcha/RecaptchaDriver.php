<?php

namespace Jiny\Admin\Services\Captcha;

use ReCaptcha\ReCaptcha;
use ReCaptcha\Response as RecaptchaResponse;

class RecaptchaDriver implements CaptchaDriverInterface
{
    private string $siteKey;
    private string $secretKey;
    private string $version;
    private ?string $lastError = null;
    private ?float $score = null;
    private float $threshold;

    public function __construct()
    {
        $this->siteKey = config('captcha.drivers.recaptcha.site_key', '');
        $this->secretKey = config('captcha.drivers.recaptcha.secret_key', '');
        $this->version = config('captcha.drivers.recaptcha.version', 'v2');
        $this->threshold = config('captcha.drivers.recaptcha.threshold', 0.5);
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
        
        if ($this->version === 'v3') {
            // reCAPTCHA v3는 보이지 않는 위젯
            return sprintf(
                '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute("%s", {action: "login"}).then(function(token) {
                            document.getElementById("g-recaptcha-response").value = token;
                        });
                    });
                </script>',
                $this->siteKey
            );
        }
        
        // reCAPTCHA v2
        return sprintf(
            '<div class="g-recaptcha" data-sitekey="%s" data-theme="%s" data-size="%s" data-tabindex="%s"></div>',
            $this->siteKey,
            $theme,
            $size,
            $tabindex
        );
    }

    public function getScriptUrl()
    {
        if ($this->version === 'v3') {
            return 'https://www.google.com/recaptcha/api.js?render=' . $this->siteKey;
        }
        
        return 'https://www.google.com/recaptcha/api.js';
    }

    public function verify($token, $ipAddress = null)
    {
        // 디버그 로깅
        \Log::info('CAPTCHA Verification Debug', [
            'response' => substr($token, 0, 20) . '...',
            'remoteIp' => $ipAddress,
            'secretKey' => substr($this->secretKey, 0, 20) . '...',
        ]);
        
        $recaptcha = new ReCaptcha($this->secretKey);
        
        if ($ipAddress) {
            $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'] ?? null);
        }
        
        $result = $recaptcha->verify($token, $ipAddress);
        
        if (!$result->isSuccess()) {
            $errors = $result->getErrorCodes();
            $this->lastError = $this->translateError($errors[0] ?? 'unknown-error');
            
            \Log::error('CAPTCHA Verification Failed', [
                'errors' => $errors,
                'lastError' => $this->lastError,
            ]);
            
            return [
                'success' => false,
                'error_code' => $errors[0] ?? 'unknown-error',
                'error_message' => $this->lastError,
                'error_codes' => $errors
            ];
        }
        
        // reCAPTCHA v3의 경우 점수 확인
        if ($this->version === 'v3') {
            $this->score = $result->getScore();
            
            if ($this->score < $this->threshold) {
                $this->lastError = sprintf(
                    '점수가 너무 낮습니다. (점수: %.2f, 임계값: %.2f)',
                    $this->score,
                    $this->threshold
                );
                return [
                    'success' => false,
                    'score' => $this->score,
                    'error_code' => 'low_score',
                    'error_message' => $this->lastError
                ];
            }
        }
        
        return [
            'success' => true,
            'score' => $this->score,
            'hostname' => $result->getHostname(),
            'challenge_ts' => $result->getChallengeTs(),
            'apk_package_name' => $result->getApkPackageName()
        ];
    }

    public function getErrorMessage(): ?string
    {
        return $this->lastError;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    private function translateError(string $errorCode): string
    {
        $errors = [
            'missing-input-secret' => 'Secret 키가 누락되었습니다.',
            'invalid-input-secret' => 'Secret 키가 유효하지 않습니다.',
            'missing-input-response' => 'CAPTCHA 응답이 누락되었습니다.',
            'invalid-input-response' => 'CAPTCHA 응답이 유효하지 않습니다.',
            'bad-request' => '잘못된 요청입니다.',
            'timeout-or-duplicate' => 'CAPTCHA가 만료되었거나 이미 사용되었습니다.',
        ];
        
        return $errors[$errorCode] ?? 'CAPTCHA 검증에 실패했습니다.';
    }
}
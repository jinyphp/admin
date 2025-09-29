<?php

namespace Jiny\Admin\Services\Captcha;

use Illuminate\Support\Manager;
use InvalidArgumentException;

class CaptchaManager extends Manager
{
    /**
     * 기본 드라이버 가져오기
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('admin.setting.captcha.driver', 'recaptcha');
    }

    /**
     * Google reCAPTCHA 드라이버 생성
     *
     * @return RecaptchaDriver
     */
    protected function createRecaptchaDriver()
    {
        $config = config('admin.setting.captcha.recaptcha', []);
        
        if (empty($config['site_key']) || empty($config['secret_key'])) {
            throw new InvalidArgumentException('reCAPTCHA site_key와 secret_key가 설정되어 있지 않습니다.');
        }
        
        return new RecaptchaDriver($config);
    }

    /**
     * hCaptcha 드라이버 생성
     *
     * @return HcaptchaDriver
     */
    protected function createHcaptchaDriver()
    {
        $config = config('admin.setting.captcha.hcaptcha', []);
        
        if (empty($config['site_key']) || empty($config['secret_key'])) {
            throw new InvalidArgumentException('hCaptcha site_key와 secret_key가 설정되어 있지 않습니다.');
        }
        
        return new HcaptchaDriver($config);
    }

    /**
     * CAPTCHA가 필요한지 확인
     *
     * @param string|null $email
     * @param string|null $ip
     * @return bool
     */
    public function isRequired(?string $email = null, ?string $ip = null): bool
    {
        // CAPTCHA가 비활성화된 경우
        if (!config('admin.setting.captcha.enabled', false)) {
            return false;
        }
        
        $mode = config('admin.setting.captcha.mode', 'conditional');
        
        // 비활성화 모드
        if ($mode === 'disabled') {
            return false;
        }
        
        // 항상 표시 모드
        if ($mode === 'always') {
            return true;
        }
        
        // 조건부 모드: 로그인 실패 횟수 확인
        if ($email || $ip) {
            $failedAttempts = $this->getFailedAttempts($email, $ip);
            $threshold = config('admin.setting.captcha.show_after_attempts', 3);
            
            return $failedAttempts >= $threshold;
        }
        
        return false;
    }

    /**
     * 로그인 실패 횟수 가져오기
     *
     * @param string|null $email
     * @param string|null $ip
     * @return int
     */
    private function getFailedAttempts(?string $email, ?string $ip): int
    {
        $attempts = 0;
        
        // 이메일 기반 실패 횟수
        if ($email) {
            $emailKey = 'captcha_failed_attempts:email:' . $email;
            $attempts = max($attempts, (int) cache()->get($emailKey, 0));
        }
        
        // IP 기반 실패 횟수
        if ($ip) {
            $ipKey = 'captcha_failed_attempts:ip:' . $ip;
            $attempts = max($attempts, (int) cache()->get($ipKey, 0));
        }
        
        return $attempts;
    }

    /**
     * 로그인 실패 횟수 증가
     *
     * @param string|null $email
     * @param string|null $ip
     * @return void
     */
    public function incrementFailedAttempts(?string $email, ?string $ip): void
    {
        $ttl = config('admin.setting.captcha.cache_ttl', 3600);
        
        if ($email) {
            $emailKey = 'captcha_failed_attempts:email:' . $email;
            $current = (int) cache()->get($emailKey, 0);
            cache()->put($emailKey, $current + 1, $ttl);
        }
        
        if ($ip) {
            $ipKey = 'captcha_failed_attempts:ip:' . $ip;
            $current = (int) cache()->get($ipKey, 0);
            cache()->put($ipKey, $current + 1, $ttl);
        }
    }

    /**
     * 로그인 실패 횟수 초기화
     *
     * @param string|null $email
     * @param string|null $ip
     * @return void
     */
    public function resetFailedAttempts(?string $email, ?string $ip): void
    {
        if ($email) {
            cache()->forget('captcha_failed_attempts:email:' . $email);
        }
        
        if ($ip) {
            cache()->forget('captcha_failed_attempts:ip:' . $ip);
        }
    }
}
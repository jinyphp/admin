<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Admin\Services\Captcha\CaptchaManager;
use Jiny\Admin\Models\AdminUserLog;
use Symfony\Component\HttpFoundation\Response;

/**
 * CAPTCHA 검증 미들웨어
 * 
 * 목적:
 * - 자동화된 봇 공격 방지
 * - 무차별 로그인 시도 차단
 * - 의심스러운 활동 필터링
 * 
 * 주요 기능:
 * 1. CAPTCHA 필요 여부 판단
 * 2. CAPTCHA 응답 검증
 * 3. 실패 시도 추적
 * 4. 로그 기록
 * 
 * 설정 의존성:
 * - config('admin.setting.captcha.enabled') - CAPTCHA 활성화 여부
 * - config('admin.setting.captcha.mode') - 작동 모드 (always/smart)
 * - config('admin.setting.captcha.log.enabled') - 로그 기록 여부
 * 
 * @package Jiny\Admin
 * @since 1.0.0
 */
class CaptchaMiddleware
{
    /**
     * CAPTCHA 매니저 인스턴스
     * 
     * @var CaptchaManager
     */
    protected CaptchaManager $captchaManager;

    /**
     * 생성자 - 의존성 주입
     * 
     * @param CaptchaManager $captchaManager
     */
    public function __construct(CaptchaManager $captchaManager)
    {
        $this->captchaManager = $captchaManager;
    }

    /**
     * HTTP 요청 처리
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  Closure  $next  다음 미들웨어
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // STEP 1: CAPTCHA 활성화 여부 확인
        if (!$this->isCaptchaEnabled()) {
            return $next($request);
        }

        // STEP 2: POST 요청만 처리 (GET 요청은 통과)
        if (!$request->isMethod('post')) {
            return $next($request);
        }

        // STEP 3: CAPTCHA 필요 여부 판단
        $email = $request->input('email');
        $ip = $request->ip();
        
        if (!$this->captchaManager->isRequired($email, $ip)) {
            return $next($request);
        }

        // STEP 4: CAPTCHA 응답 확인
        $captchaResponse = $this->getCaptchaResponse($request);
        
        if (empty($captchaResponse)) {
            return $this->handleMissingCaptcha($request);
        }

        // STEP 5: CAPTCHA 검증
        try {
            $driver = $this->captchaManager->driver();
            
            if (!$driver->verify($captchaResponse, $ip)) {
                return $this->handleFailedCaptcha($request, $driver->getErrorMessage());
            }
            
            // CAPTCHA 성공 로그
            $this->logCaptchaSuccess($request, $driver->getScore());
            
        } catch (\Exception $e) {
            \Log::error('CAPTCHA middleware error: ' . $e->getMessage());
            
            // 엄격 모드에서는 에러 시 차단
            if ($this->isStrictMode()) {
                return $this->handleCaptchaError($request);
            }
        }

        return $next($request);
    }

    /**
     * CAPTCHA 활성화 여부 확인
     * 
     * @return bool
     */
    protected function isCaptchaEnabled(): bool
    {
        return config('admin.setting.captcha.enabled', false);
    }

    /**
     * 엄격 모드 여부 확인
     * 
     * @return bool
     */
    protected function isStrictMode(): bool
    {
        return config('admin.setting.captcha.mode') === 'always';
    }

    /**
     * CAPTCHA 응답 가져오기
     *
     * @param Request $request
     * @return string|null
     */
    private function getCaptchaResponse(Request $request): ?string
    {
        // reCAPTCHA 응답 확인
        if ($request->has('g-recaptcha-response')) {
            return $request->input('g-recaptcha-response');
        }
        
        // hCaptcha 응답 확인
        if ($request->has('h-captcha-response')) {
            return $request->input('h-captcha-response');
        }
        
        // Cloudflare Turnstile 응답 확인
        if ($request->has('cf-turnstile-response')) {
            return $request->input('cf-turnstile-response');
        }
        
        return null;
    }

    /**
     * CAPTCHA 누락 처리
     *
     * @param Request $request
     * @return Response
     */
    private function handleMissingCaptcha(Request $request): Response
    {
        // 로그 기록
        if (config('admin.setting.captcha.log.enabled')) {
            AdminUserLog::log('captcha_missing_middleware', null, [
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempt_time' => now()->toDateTimeString(),
            ]);
        }

        session()->flash('notification', [
            'type' => 'error',
            'title' => 'CAPTCHA 필요',
            'message' => config('admin.setting.captcha.messages.required', 'CAPTCHA 인증이 필요합니다.'),
        ]);

        return redirect()->back()
            ->withErrors(['captcha' => config('admin.setting.captcha.messages.required', 'CAPTCHA 인증이 필요합니다.')])
            ->withInput($request->except('password'));
    }

    /**
     * CAPTCHA 실패 처리
     *
     * @param Request $request
     * @param string|null $errorMessage
     * @return Response
     */
    private function handleFailedCaptcha(Request $request, ?string $errorMessage = null): Response
    {
        // 실패 횟수 증가
        $this->captchaManager->incrementFailedAttempts(
            $request->input('email'),
            $request->ip()
        );

        // 로그 기록
        if (config('admin.setting.captcha.log.enabled')) {
            AdminUserLog::log('captcha_failed_middleware', null, [
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'error' => $errorMessage,
                'attempt_time' => now()->toDateTimeString(),
            ]);
        }

        session()->flash('notification', [
            'type' => 'error',
            'title' => 'CAPTCHA 실패',
            'message' => config('admin.setting.captcha.messages.failed', 'CAPTCHA 인증에 실패했습니다.'),
        ]);

        return redirect()->back()
            ->withErrors(['captcha' => config('admin.setting.captcha.messages.failed', 'CAPTCHA 인증에 실패했습니다.')])
            ->withInput($request->except('password'));
    }

    /**
     * CAPTCHA 에러 처리
     *
     * @param Request $request
     * @return Response
     */
    private function handleCaptchaError(Request $request): Response
    {
        session()->flash('notification', [
            'type' => 'error',
            'title' => 'CAPTCHA 오류',
            'message' => config('admin.setting.captcha.messages.not_configured', 'CAPTCHA 설정 오류가 발생했습니다.'),
        ]);

        return redirect()->back()
            ->withErrors(['captcha' => config('admin.setting.captcha.messages.not_configured', 'CAPTCHA 설정 오류가 발생했습니다.')])
            ->withInput($request->except('password'));
    }

    /**
     * CAPTCHA 성공 로그
     *
     * @param Request $request
     * @param float|null $score
     * @return void
     */
    private function logCaptchaSuccess(Request $request, ?float $score = null): void
    {
        if (config('admin.setting.captcha.log.enabled') && !config('admin.setting.captcha.log.failed_only')) {
            AdminUserLog::log('captcha_success_middleware', null, [
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'score' => $score,
                'attempt_time' => now()->toDateTimeString(),
            ]);
        }
    }
}
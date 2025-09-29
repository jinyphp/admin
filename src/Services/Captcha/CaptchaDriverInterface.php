<?php

namespace Jiny\Admin\Services\Captcha;

interface CaptchaDriverInterface
{
    /**
     * 사이트 키 반환
     *
     * @return string
     */
    public function getSiteKey(): string;

    /**
     * CAPTCHA 위젯을 렌더링하기 위한 HTML 반환
     *
     * @param array $options
     * @return string
     */
    public function render(array $options = []): string;

    /**
     * CAPTCHA 위젯을 위한 JavaScript 스크립트 반환
     *
     * @return string
     */
    public function getScript(): string;

    /**
     * CAPTCHA 응답 검증
     *
     * @param string $response
     * @param string|null $remoteIp
     * @return bool
     */
    public function verify(string $response, ?string $remoteIp = null): bool;

    /**
     * 검증 실패 시 에러 메시지 반환
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string;

    /**
     * 검증 점수 반환 (reCAPTCHA v3용)
     *
     * @return float|null
     */
    public function getScore(): ?float;
}
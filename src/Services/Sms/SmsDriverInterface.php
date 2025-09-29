<?php

namespace Jiny\Admin\Services\Sms;

/**
 * SMS 드라이버 인터페이스
 * 
 * 모든 SMS 제공업체 드라이버가 구현해야 하는 공통 인터페이스
 */
interface SmsDriverInterface
{
    /**
     * SMS 발송
     *
     * @param string $toNumber 수신 번호
     * @param string $message 메시지 내용
     * @param string|null $fromNumber 발신 번호
     * @return array 발송 결과
     */
    public function send(string $toNumber, string $message, ?string $fromNumber = null): array;

    /**
     * 잔액 조회
     *
     * @return array 잔액 정보
     */
    public function getBalance(): array;

    /**
     * 발송 상태 조회
     *
     * @param string $messageId 메시지 ID
     * @return array 상태 정보
     */
    public function getStatus(string $messageId): array;

    /**
     * 드라이버 이름 반환
     *
     * @return string
     */
    public function getName(): string;

    /**
     * 전화번호 포맷팅
     *
     * @param string $phoneNumber
     * @return string
     */
    public function formatPhoneNumber(string $phoneNumber): string;
}
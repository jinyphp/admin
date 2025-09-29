<?php

namespace Jiny\Admin\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Vonage(Nexmo) SMS 드라이버
 */
class VonageDriver implements SmsDriverInterface
{
    private $apiKey;
    private $apiSecret;
    private $fromNumber;
    private $baseUrl = 'https://rest.nexmo.com';
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? '';
        $this->fromNumber = $config['from_number'] ?? 'JINYPHP';
    }

    /**
     * SMS 발송
     */
    public function send(string $toNumber, string $message, ?string $fromNumber = null): array
    {
        try {
            // 발신번호 설정
            if (!empty($fromNumber)) {
                $from = $fromNumber;
            } elseif (!empty($this->fromNumber)) {
                $from = $this->fromNumber;
            } else {
                $from = 'JINYPHP';
            }

            // 전화번호 포맷팅
            $toNumber = $this->formatPhoneNumber($toNumber);

            // Vonage API 호출
            $response = Http::asForm()->post($this->baseUrl . '/sms/json', [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'to' => $toNumber,
                'from' => $from,
                'text' => $message,
                'type' => 'unicode' // 한글 지원
            ]);

            $result = $response->json();
            
            Log::info('Vonage SMS API Response', [
                'to' => $toNumber,
                'from' => $from,
                'response' => $result
            ]);

            // 응답 처리
            if ($response->successful() && isset($result['messages'])) {
                $messageData = $result['messages'][0] ?? [];
                
                if ($messageData['status'] == '0') {
                    // 성공
                    return [
                        'success' => true,
                        'message_id' => $messageData['message-id'] ?? null,
                        'remaining_balance' => $messageData['remaining-balance'] ?? null,
                        'message_price' => $messageData['message-price'] ?? null,
                        'network' => $messageData['network'] ?? null,
                        'response_data' => $result
                    ];
                } else {
                    // 오류 처리
                    $errorMessage = $this->getErrorMessage($messageData['status']);
                    return [
                        'success' => false,
                        'error_code' => $messageData['status'],
                        'error_message' => $messageData['error-text'] ?? $errorMessage,
                        'response_data' => $result
                    ];
                }
            }

            return [
                'success' => false,
                'error_message' => 'API 호출 실패',
                'response_data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Vonage SMS Send Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * 잔액 조회
     */
    public function getBalance(): array
    {
        try {
            $response = Http::get($this->baseUrl . '/account/get-balance', [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret
            ]);

            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'balance' => $result['value'] ?? 0,
                    'currency' => 'EUR',
                    'auto_reload' => $result['autoReload'] ?? false
                ];
            }

            return [
                'success' => false,
                'error_message' => '잔액 조회 실패'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * 발송 상태 조회
     */
    public function getStatus(string $messageId): array
    {
        try {
            $response = Http::get($this->baseUrl . '/search/message', [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'id' => $messageId
            ]);

            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $result['status'] ?? 'unknown',
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'error_message' => '상태 조회 실패'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * 드라이버 이름 반환
     */
    public function getName(): string
    {
        return 'vonage';
    }

    /**
     * 전화번호 포맷팅
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // 모든 특수문자 제거
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // 한국 번호인 경우
        if (substr($number, 0, 2) == '01') {
            // 010-1234-5678 -> 821012345678
            $number = '82' . substr($number, 1);
        }
        
        return $number;
    }

    /**
     * 오류 코드 메시지 매핑
     */
    private function getErrorMessage($status)
    {
        $messages = [
            '1' => '스로틀링 - 너무 많은 요청',
            '2' => '누락된 매개변수',
            '3' => '잘못된 매개변수',
            '4' => '잘못된 자격 증명',
            '5' => '내부 오류',
            '6' => '잘못된 메시지',
            '7' => '번호 차단',
            '8' => '계정 차단',
            '9' => '할당량 초과',
            '10' => '서명 누락',
            '11' => '불법 발신자 주소',
            '12' => '지원되지 않는 지역',
            '13' => '잘못된 서명',
            '14' => '잘못된 발신자 주소',
            '15' => '잘못된 수신 번호',
            '16' => '메시지가 너무 김',
            '19' => '시설 지원 안됨',
            '20' => '잘못된 메시지 클래스',
            '23' => '캐리어 차단',
            '29' => '수신자 옵트아웃',
            '33' => '번호 휴대성 오류',
            '34' => '잘못된 네트워크 코드'
        ];

        return $messages[$status] ?? '알 수 없는 오류';
    }
}
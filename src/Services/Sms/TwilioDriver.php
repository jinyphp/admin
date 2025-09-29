<?php

namespace Jiny\Admin\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Twilio SMS 드라이버
 */
class TwilioDriver implements SmsDriverInterface
{
    private $accountSid;
    private $authToken;
    private $fromNumber;
    private $baseUrl = 'https://api.twilio.com/2010-04-01';
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->accountSid = $config['account_sid'] ?? '';
        $this->authToken = $config['auth_token'] ?? '';
        $this->fromNumber = $config['from_number'] ?? '';
    }

    /**
     * SMS 발송
     */
    public function send(string $toNumber, string $message, ?string $fromNumber = null): array
    {
        try {
            // 발신번호 설정
            $from = !empty($fromNumber) ? $fromNumber : $this->fromNumber;
            
            if (empty($from)) {
                return [
                    'success' => false,
                    'error_message' => 'Twilio 발신번호가 설정되지 않았습니다. Twilio 계정에서 전화번호를 구매하거나 Trial 계정의 경우 Verified Caller ID를 설정해주세요.'
                ];
            }

            // 전화번호 포맷팅
            $toNumber = $this->formatPhoneNumber($toNumber);

            // Twilio API 호출
            $url = $this->baseUrl . '/Accounts/' . $this->accountSid . '/Messages.json';
            
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post($url, [
                    'From' => $from,
                    'To' => '+' . $toNumber,
                    'Body' => $message
                ]);

            $result = $response->json();
            
            Log::info('Twilio SMS API Response', [
                'to' => $toNumber,
                'from' => $from,
                'response' => $result
            ]);

            if ($response->successful()) {
                // 성공
                return [
                    'success' => true,
                    'message_id' => $result['sid'] ?? null,
                    'status' => $result['status'] ?? null,
                    'price' => $result['price'] ?? null,
                    'price_unit' => $result['price_unit'] ?? null,
                    'response_data' => $result
                ];
            } else {
                // 오류 처리
                $errorMessage = $this->getErrorMessage($result['code'] ?? null, $result['message'] ?? 'API 호출 실패');
                
                return [
                    'success' => false,
                    'error_code' => $result['code'] ?? null,
                    'error_message' => $errorMessage,
                    'response_data' => $result
                ];
            }

        } catch (\Exception $e) {
            Log::error('Twilio SMS Send Error', [
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
            // Twilio Balance API
            $url = $this->baseUrl . '/Accounts/' . $this->accountSid . '/Balance.json';
            
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)->get($url);
            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'balance' => $result['balance'] ?? 0,
                    'currency' => $result['currency'] ?? 'USD',
                    'account_sid' => $result['account_sid'] ?? null
                ];
            }

            return [
                'success' => false,
                'error_message' => $result['message'] ?? '잔액 조회 실패'
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
            $url = $this->baseUrl . '/Accounts/' . $this->accountSid . '/Messages/' . $messageId . '.json';
            
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)->get($url);
            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $result['status'] ?? 'unknown',
                    'error_code' => $result['error_code'] ?? null,
                    'error_message' => $result['error_message'] ?? null,
                    'date_sent' => $result['date_sent'] ?? null,
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'error_message' => $result['message'] ?? '상태 조회 실패'
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
        return 'twilio';
    }

    /**
     * 전화번호 포맷팅
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // 모든 특수문자 제거
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // + 기호가 이미 있으면 제거
        $number = ltrim($number, '+');
        
        // 한국 번호인 경우
        if (substr($number, 0, 2) == '01') {
            // 010-1234-5678 -> 821012345678
            $number = '82' . substr($number, 1);
        }
        
        // 이미 국가 코드가 있는지 확인
        if (substr($number, 0, 2) != '82' && strlen($number) == 10) {
            // 한국 번호로 가정
            $number = '82' . ltrim($number, '0');
        }
        
        return $number;
    }
    
    /**
     * Twilio 오류 코드를 사용자 친화적 메시지로 변환
     */
    private function getErrorMessage($code, $defaultMessage)
    {
        $errorMessages = [
            21608 => 'Twilio Trial 계정 제한: 수신번호가 검증되지 않았습니다. https://console.twilio.com/us1/develop/phone-numbers/manage/verified 에서 수신번호를 검증하거나, 유료 계정으로 업그레이드하세요.',
            21610 => 'SMS 메시지 발송 시도가 차단되었습니다. 수신자가 STOP 메시지를 보냈을 수 있습니다.',
            21611 => '유효하지 않은 수신 번호입니다. 번호 형식을 확인해주세요.',
            21612 => '수신 번호가 SMS를 지원하지 않습니다.',
            21614 => '수신 번호가 모바일 번호가 아닙니다.',
            21617 => '메시지 내용이 비어있습니다.',
            21659 => '발신번호가 해당 계정과 일치하지 않습니다. 다른 Twilio 계정의 번호를 사용하고 있을 수 있습니다.',
            21660 => '발신번호가 이 Twilio 계정에 속하지 않습니다. Twilio Console에서 전화번호를 확인해주세요.',
            30003 => 'Twilio 계정이 일시 정지되었습니다. 결제 정보를 확인해주세요.',
            30004 => 'SMS 발송이 차단되었습니다. Twilio 계정 설정을 확인해주세요.',
            30005 => '알 수 없는 목적지입니다. 수신 번호의 국가를 확인해주세요.',
            30006 => '유선 전화 번호로는 SMS를 보낼 수 없습니다.',
            30007 => '스팸으로 의심되는 메시지가 차단되었습니다.',
            30008 => '알 수 없는 오류가 발생했습니다. Twilio 지원팀에 문의해주세요.',
            30034 => 'Trial 계정에서는 검증된 번호로만 SMS를 보낼 수 있습니다. Twilio Console에서 수신 번호를 검증해주세요.'
        ];
        
        if (isset($errorMessages[$code])) {
            return $errorMessages[$code];
        }
        
        // Trial 계정 관련 메시지 개선
        if (strpos($defaultMessage, 'Trial') !== false) {
            return 'Trial 계정 제한: Twilio Console에서 수신 번호를 먼저 검증(Verify)해야 합니다.';
        }
        
        if (strpos($defaultMessage, 'From') !== false && strpos($defaultMessage, 'not a Twilio phone number') !== false) {
            return '발신번호 오류: Twilio 계정에서 전화번호를 구매하거나, Trial 계정의 경우 Messaging Geographic Permissions를 확인해주세요.';
        }
        
        return $defaultMessage;
    }
}
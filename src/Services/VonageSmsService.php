<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Jiny\Admin\Models\AdminSmsProvider;
use Jiny\Admin\Models\AdminSmsSend;

class VonageSmsService
{
    private $provider;
    private $config;
    private $apiKey;
    private $apiSecret;
    private $fromNumber;
    private $baseUrl = 'https://rest.nexmo.com';
    private $enabled = false;

    public function __construct($providerId = null)
    {
        if ($providerId) {
            $this->loadProviderById($providerId);
        } else {
            $this->loadDefaultProvider();
        }
    }

    /**
     * 기본 Vonage 제공업체 로드
     */
    private function loadDefaultProvider()
    {
        // 기본 제공업체 찾기
        $this->provider = AdminSmsProvider::where('is_active', true)
            ->where('is_default', true)
            ->where('driver', 'vonage')
            ->first();

        if (!$this->provider) {
            // Vonage 중 활성화된 첫 번째 선택
            $this->provider = AdminSmsProvider::where('is_active', true)
                ->where('driver', 'vonage')
                ->orderBy('priority', 'asc')
                ->first();
        }

        if ($this->provider) {
            $this->initializeFromProvider();
        }
    }

    /**
     * ID로 제공업체 로드
     */
    private function loadProviderById($providerId)
    {
        $this->provider = AdminSmsProvider::where('id', $providerId)
            ->where('is_active', true)
            ->where('driver', 'vonage')
            ->first();

        if ($this->provider) {
            $this->initializeFromProvider();
        }
    }

    /**
     * 제공업체 정보로 초기화
     */
    private function initializeFromProvider()
    {
        $this->config = json_decode($this->provider->config, true) ?? [];
        
        $this->apiKey = $this->config['api_key'] ?? '';
        $this->apiSecret = $this->config['api_secret'] ?? '';
        $this->fromNumber = $this->config['from'] ?? '';
        $this->baseUrl = $this->config['api_endpoint'] ?? 'https://rest.nexmo.com';
        
        if ($this->apiKey && $this->apiSecret) {
            $this->enabled = true;
        } else {
            Log::warning('Vonage SMS 서비스: API 키 또는 시크릿이 설정되지 않았습니다');
        }
    }

    /**
     * SMS 발송
     */
    public function sendSms($toNumber, $message, $from = null)
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error_message' => 'Vonage SMS 서비스가 활성화되지 않았습니다'
            ];
        }

        // 발송 이력 생성
        $smsLog = null;
        if ($this->provider) {
            $smsLog = AdminSmsSend::create([
                'provider_id' => $this->provider->id,
                'provider_name' => $this->provider->name,
                'to_number' => $toNumber,
                'from_number' => $from ?? $this->fromNumber ?? 'JINYPHP',
                'from_name' => $this->provider->name,
                'message' => $message,
                'message_length' => mb_strlen($message),
                'message_count' => $this->calculateMessageCount($message),
                'status' => 'pending',
                'sent_by' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        try {
            // 발신번호 설정
            $fromNumber = $from ?? $this->fromNumber ?? 'JINYPHP';
            
            // 한국 번호 포맷 정리
            $toNumber = $this->formatPhoneNumber($toNumber);

            // Vonage API 호출
            $response = Http::asForm()
                ->timeout($this->config['timeout'] ?? 30)
                ->post($this->baseUrl . '/sms/json', [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'to' => $toNumber,
                    'from' => $fromNumber,
                    'text' => $message,
                    'type' => 'unicode' // 한글 지원
                ]);

            $result = $response->json();
            
            Log::info('Vonage SMS API Response', [
                'to' => $toNumber,
                'from' => $fromNumber,
                'response' => $result
            ]);

            // 응답 처리
            if ($response->successful() && isset($result['messages'])) {
                $messageData = $result['messages'][0] ?? [];
                
                if ($messageData['status'] == '0') {
                    // 성공
                    if ($smsLog) {
                        $smsLog->update([
                            'status' => 'sent',
                            'message_id' => $messageData['message-id'] ?? null,
                            'cost' => $messageData['message-price'] ?? null,
                            'currency' => 'EUR',
                            'response' => $result,
                            'sent_at' => now(),
                        ]);
                    }

                    // 제공업체 통계 업데이트
                    if ($this->provider) {
                        $this->provider->increment('sent_count');
                        $this->provider->update(['last_used_at' => now()]);
                    }

                    return [
                        'success' => true,
                        'message_id' => $messageData['message-id'] ?? null,
                        'log_id' => $smsLog ? $smsLog->id : null,
                        'remaining_balance' => $messageData['remaining-balance'] ?? null,
                        'message_price' => $messageData['message-price'] ?? null,
                        'network' => $messageData['network'] ?? null,
                        'response_data' => $result
                    ];
                } else {
                    // 오류 처리
                    $errorMessage = $this->getErrorMessage($messageData['status']);
                    
                    if ($smsLog) {
                        $smsLog->update([
                            'status' => 'failed',
                            'error_code' => $messageData['status'],
                            'error_message' => $messageData['error-text'] ?? $errorMessage,
                            'failed_at' => now(),
                        ]);
                    }

                    return [
                        'success' => false,
                        'error_code' => $messageData['status'],
                        'error_message' => $messageData['error-text'] ?? $errorMessage,
                        'response_data' => $result
                    ];
                }
            }

            // API 호출 실패
            if ($smsLog) {
                $smsLog->update([
                    'status' => 'failed',
                    'error_message' => 'API 호출 실패',
                    'failed_at' => now(),
                ]);
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

            if ($smsLog) {
                $smsLog->update([
                    'status' => 'failed',
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'failed_at' => now(),
                ]);
            }

            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * 대량 SMS 발송
     */
    public function sendBulk(array $recipients)
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($recipients as $recipient) {
            $result = $this->sendSms(
                $recipient['to'],
                $recipient['message'],
                $recipient['from'] ?? null
            );

            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }

            $results[] = [
                'to' => $recipient['to'],
                'success' => $result['success'],
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error_message'] ?? null,
            ];

            // Rate limiting
            usleep(100000); // 0.1초 대기
        }

        return [
            'total' => count($recipients),
            'success' => $successCount,
            'failure' => $failureCount,
            'results' => $results
        ];
    }

    /**
     * 전화번호 포맷 변환
     */
    private function formatPhoneNumber($number)
    {
        // 이미 국제 형식인 경우
        if (str_starts_with($number, '+')) {
            return $number;
        }
        
        // 모든 특수문자 제거
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // 한국 번호인 경우
        if (substr($number, 0, 2) == '01') {
            // 010-1234-5678 -> +821012345678
            $number = '+82' . substr($number, 1);
        } elseif (substr($number, 0, 1) == '0') {
            // 다른 0으로 시작하는 번호
            $number = '+82' . substr($number, 1);
        } else {
            // 그 외의 경우 한국 번호로 가정
            $number = '+82' . $number;
        }
        
        return $number;
    }

    /**
     * 메시지 분할 수 계산
     */
    private function calculateMessageCount($message)
    {
        $length = mb_strlen($message);
        
        // 한글 포함 여부 확인
        if (preg_match('/[\x{AC00}-\x{D7AF}]/u', $message)) {
            // 한글 메시지 (70자 기준)
            if ($length <= 70) {
                return 1;
            } else {
                return ceil($length / 67);
            }
        } else {
            // 영문 메시지 (160자 기준)
            if ($length <= 160) {
                return 1;
            } else {
                return ceil($length / 153);
            }
        }
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

    /**
     * 계정 잔액 조회
     */
    public function getBalance()
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error_message' => 'Vonage SMS 서비스가 활성화되지 않았습니다'
            ];
        }

        try {
            $response = Http::timeout($this->config['timeout'] ?? 30)
                ->get($this->baseUrl . '/account/get-balance', [
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
                'error_message' => '잔액 조회 실패',
                'response' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Vonage 잔액 조회 실패', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * 서비스 활성화 여부
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * 현재 제공업체 정보
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
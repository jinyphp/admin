<?php

namespace Jiny\Admin\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 알리고 SMS 드라이버 (한국 SMS 서비스)
 */
class AligoDriver implements SmsDriverInterface
{
    protected $apiKey;
    protected $userId;
    protected $sender;
    protected $baseUrl = 'https://apis.aligo.in';
    protected $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->userId = $config['user_id'] ?? '';
        $this->sender = $config['sender'] ?? '';
    }
    
    /**
     * Send SMS message
     */
    public function send(string $toNumber, string $message, ?string $fromNumber = null): array
    {
        try {
            $sender = $fromNumber ?: $this->sender;
            
            if (empty($sender)) {
                return [
                    'success' => false,
                    'error_message' => '발신번호가 설정되지 않았습니다.'
                ];
            }
            
            // 전화번호 포맷팅 (하이픈 제거)
            $toNumber = preg_replace('/[^0-9]/', '', $toNumber);
            $sender = preg_replace('/[^0-9]/', '', $sender);
            
            // 메시지 타입 결정 (90바이트 초과시 LMS)
            $msgType = mb_strlen($message, 'UTF-8') > 90 ? 'LMS' : 'SMS';
            
            $params = [
                'key' => $this->apiKey,
                'user_id' => $this->userId,
                'sender' => $sender,
                'receiver' => $toNumber,
                'msg' => $message,
                'msg_type' => $msgType,
                'testmode_yn' => $this->config['test_mode'] ?? 'N',
            ];
            
            // 제목 설정 (LMS의 경우)
            if ($msgType === 'LMS' && isset($this->config['title'])) {
                $params['title'] = $this->config['title'];
            }
            
            $response = Http::asForm()->post($this->baseUrl . '/send/', $params);
            $result = $response->json();
            
            Log::info('Aligo SMS API Response', [
                'to' => $toNumber,
                'response' => $result
            ]);
            
            if ($result['result_code'] == '1') {
                return [
                    'success' => true,
                    'message_id' => $result['msg_id'] ?? null,
                    'msg_type' => $result['msg_type'] ?? $msgType,
                    'success_cnt' => $result['success_cnt'] ?? 1,
                    'error_cnt' => $result['error_cnt'] ?? 0,
                    'response_data' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'error_code' => $result['result_code'] ?? null,
                    'error_message' => $this->getErrorMessage($result['result_code'], $result['message'] ?? '발송 실패'),
                    'response_data' => $result
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Aligo SMS Send Error', [
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
     * Send bulk SMS
     */
    public function sendBulk(array $recipients, string $message, ?string $fromNumber = null): array
    {
        try {
            $sender = $fromNumber ?: $this->sender;
            
            if (empty($sender)) {
                return [
                    'success' => false,
                    'error_message' => '발신번호가 설정되지 않았습니다.'
                ];
            }
            
            // 수신번호 목록 생성
            $receivers = [];
            $messages = [];
            
            foreach ($recipients as $recipient) {
                if (is_array($recipient)) {
                    $receivers[] = preg_replace('/[^0-9]/', '', $recipient['to']);
                    $messages[] = $recipient['message'] ?? $message;
                } else {
                    $receivers[] = preg_replace('/[^0-9]/', '', $recipient);
                    $messages[] = $message;
                }
            }
            
            // 메시지 타입 결정
            $maxLength = max(array_map(function($msg) {
                return mb_strlen($msg, 'UTF-8');
            }, $messages));
            $msgType = $maxLength > 90 ? 'LMS' : 'SMS';
            
            $params = [
                'key' => $this->apiKey,
                'user_id' => $this->userId,
                'sender' => preg_replace('/[^0-9]/', '', $sender),
                'receiver' => implode(',', $receivers),
                'msg' => implode('||', $messages),
                'msg_type' => $msgType,
                'cnt' => count($receivers),
                'testmode_yn' => $this->config['test_mode'] ?? 'N',
            ];
            
            $response = Http::asForm()->post($this->baseUrl . '/send_mass/', $params);
            $result = $response->json();
            
            if ($result['result_code'] == '1') {
                return [
                    'success' => true,
                    'message_id' => $result['msg_id'] ?? null,
                    'success_cnt' => $result['success_cnt'] ?? 0,
                    'error_cnt' => $result['error_cnt'] ?? 0,
                    'msg_type' => $result['msg_type'] ?? $msgType,
                    'response_data' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'error_code' => $result['result_code'] ?? null,
                    'error_message' => $this->getErrorMessage($result['result_code'], $result['message'] ?? '발송 실패'),
                    'response_data' => $result
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Aligo Bulk SMS Send Error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get SMS balance
     */
    public function getBalance(): array
    {
        try {
            $params = [
                'key' => $this->apiKey,
                'user_id' => $this->userId,
            ];
            
            $response = Http::asForm()->post($this->baseUrl . '/remain/', $params);
            $result = $response->json();
            
            if ($result['result_code'] == '1') {
                return [
                    'success' => true,
                    'sms_count' => $result['SMS_CNT'] ?? 0,
                    'lms_count' => $result['LMS_CNT'] ?? 0,
                    'mms_count' => $result['MMS_CNT'] ?? 0,
                    'response_data' => $result
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
     * Get message status
     */
    public function getStatus(string $messageId): array
    {
        try {
            $params = [
                'key' => $this->apiKey,
                'user_id' => $this->userId,
                'mid' => $messageId,
            ];
            
            $response = Http::asForm()->post($this->baseUrl . '/sms_list/', $params);
            $result = $response->json();
            
            if ($result['result_code'] == '1' && isset($result['list'][0])) {
                $status = $result['list'][0];
                return [
                    'success' => true,
                    'status' => $this->mapStatus($status['rslt'] ?? ''),
                    'report_time' => $status['report_time'] ?? null,
                    'send_time' => $status['send_date'] ?? null,
                    'response_data' => $status
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
     * Get driver name
     */
    public function getName(): string
    {
        return 'aligo';
    }
    
    /**
     * Format phone number
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // 하이픈, 공백 등 제거
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // 국가코드 제거 (82로 시작하는 경우)
        if (substr($number, 0, 2) == '82') {
            $number = '0' . substr($number, 2);
        }
        
        return $number;
    }
    
    /**
     * Map Aligo status to standard status
     */
    protected function mapStatus($aligoStatus)
    {
        $statusMap = [
            '00' => 'delivered',     // 성공
            '06' => 'delivered',     // 성공
            '07' => 'failed',        // 실패
            '08' => 'failed',        // 실패
            '09' => 'failed',        // 실패
            '10' => 'failed',        // 실패
            '11' => 'failed',        // 실패
            '12' => 'failed',        // 실패
            '13' => 'failed',        // 실패
            '14' => 'failed',        // 실패
            '15' => 'failed',        // 실패
            '99' => 'pending',       // 전송중
        ];
        
        return $statusMap[$aligoStatus] ?? 'unknown';
    }
    
    /**
     * Get error message
     */
    protected function getErrorMessage($code, $defaultMessage)
    {
        $errorMessages = [
            '-100' => 'API 키가 유효하지 않습니다.',
            '-101' => '등록된 발신번호가 아닙니다.',
            '-102' => '발신번호 등록 후 사용 가능합니다.',
            '-103' => '수신번호가 유효하지 않습니다.',
            '-104' => '문자 내용이 없습니다.',
            '-105' => '메시지 ID가 유효하지 않습니다.',
            '-106' => '전송 가능한 건수가 부족합니다.',
            '-201' => '잘못된 전화번호입니다.',
            '-301' => '등록되지 않은 사용자입니다.',
            '-302' => '사용이 정지된 사용자입니다.',
            '-303' => '전송 권한이 없습니다.',
            '-900' => '알 수 없는 오류가 발생했습니다.',
        ];
        
        return $errorMessages[$code] ?? $defaultMessage;
    }
}
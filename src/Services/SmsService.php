<?php

namespace Jiny\Admin\Services;

use Twilio\Rest\Client as TwilioClient;
use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic as VonageBasic;
use Jiny\Admin\Models\AdminSmsProvider;
use Jiny\Admin\Models\AdminSmsSend;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    protected $client;
    protected $provider;
    protected $enabled;
    
    /**
     * SMS 서비스 생성자
     */
    public function __construct()
    {
        $this->loadDefaultProvider();
    }
    
    /**
     * 기본 SMS 제공업체 로드
     */
    protected function loadDefaultProvider()
    {
        // 기본 제공업체를 찾습니다
        $this->provider = AdminSmsProvider::where('is_active', true)
            ->where('is_default', true)
            ->first();
            
        if (!$this->provider) {
            // 기본이 없으면 활성화된 제공업체 중 우선순위가 높은 것을 선택
            $this->provider = AdminSmsProvider::where('is_active', true)
                ->orderBy('priority', 'asc')
                ->first();
        }
        
        if ($this->provider) {
            $this->initializeClient();
            $this->enabled = true;
        } else {
            // 데이터베이스에 제공업체가 없으면 비활성화
            $this->enabled = false;
            Log::warning('활성화된 SMS 제공업체가 없습니다');
        }
    }
    
    
    /**
     * 특정 제공업체로 설정
     */
    public function setProvider($providerId)
    {
        $this->provider = AdminSmsProvider::find($providerId);
        
        if ($this->provider && $this->provider->is_active) {
            $this->initializeClient();
            return true;
        }
        
        return false;
    }
    
    /**
     * SMS 클라이언트 초기화
     */
    protected function initializeClient()
    {
        if (!$this->provider) {
            return;
        }
        
        $driver = strtolower($this->provider->driver ?? '');
        
        // config가 이미 배열인 경우 그대로 사용, 문자열인 경우 json_decode
        if (is_array($this->provider->config)) {
            $config = $this->provider->config;
        } elseif (is_string($this->provider->config)) {
            $config = json_decode($this->provider->config, true) ?? [];
        } else {
            $config = [];
        }
        
        if ($driver === 'vonage') {
            try {
                $apiKey = $config['api_key'] ?? '';
                $apiSecret = $config['api_secret'] ?? '';
                
                if (!$apiKey || !$apiSecret) {
                    throw new Exception('Vonage API 키 또는 시크릿이 설정되지 않았습니다');
                }
                
                $basic = new VonageBasic($apiKey, $apiSecret);
                $this->client = new VonageClient($basic);
                $this->enabled = true;
            } catch (Exception $e) {
                Log::error("Vonage 초기화 실패: {$e->getMessage()}");
                $this->enabled = false;
            }
        } elseif ($driver === 'twilio') {
            try {
                $accountSid = $config['account_sid'] ?? '';
                $authToken = $config['auth_token'] ?? '';
                
                if (!$accountSid || !$authToken) {
                    throw new Exception('Twilio 계정 SID 또는 인증 토큰이 설정되지 않았습니다');
                }
                
                $this->client = new TwilioClient($accountSid, $authToken);
                $this->enabled = true;
            } catch (Exception $e) {
                Log::error("Twilio 초기화 실패: {$e->getMessage()}");
                $this->enabled = false;
            }
        } else {
            $this->enabled = false;
            Log::warning("지원하지 않는 SMS 제공업체: {$this->provider->name} (드라이버: {$driver})");
        }
    }
    
    /**
     * SMS 발송
     */
    public function send($to, $message, $from = null)
    {
        if (!$this->enabled || !$this->client) {
            throw new Exception('SMS 서비스가 구성되지 않았습니다');
        }
        
        // 전화번호 형식화
        $to = $this->formatPhoneNumber($to);
        
        // 발송 이력 레코드 생성 (provider가 있는 경우만)
        $smsLog = null;
        if ($this->provider) {
            $config = json_decode($this->provider->config, true) ?? [];
            $smsLog = AdminSmsSend::create([
                'provider_id' => $this->provider->id,
                'provider_name' => $this->provider->name,
                'to_number' => $to,
                'from_number' => $from ?? $config['from'] ?? $config['sender'] ?? 'System',
                'from_name' => $this->provider->name ?? 'System',
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
            // driver에 따라 적절한 발송 메서드 호출
            $driver = strtolower($this->provider->driver ?? '');
            
            if ($driver === 'vonage') {
                $response = $this->sendViaVonage($to, $message, $from);
            } elseif ($driver === 'twilio') {
                $response = $this->sendViaTwilio($to, $message, $from);
            } else {
                throw new Exception("지원하지 않는 드라이버: {$driver}");
            }
            
            // 성공 시 업데이트
            if ($smsLog) {
                $smsLog->update([
                    'status' => 'sent',
                    'message_id' => $response['message_id'] ?? null,
                    'cost' => $response['cost'] ?? null,
                    'currency' => $response['currency'] ?? null,
                    'response' => $response,
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
                'message_id' => $response['message_id'] ?? null,
                'log_id' => $smsLog ? $smsLog->id : null,
                'response' => $response,
            ];
            
        } catch (Exception $e) {
            // 실패 시 업데이트
            if ($smsLog) {
                $smsLog->update([
                    'status' => 'failed',
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'failed_at' => now(),
                ]);
            }
            
            Log::error('SMS 발송 실패', [
                'provider' => $this->provider ? $this->provider->name : 'Unknown',
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Vonage를 통한 SMS 발송
     */
    protected function sendViaVonage($to, $message, $from = null)
    {
        $config = json_decode($this->provider->config, true) ?? [];
        $from = $from ?? $config['from'] ?? 'VONAGE';
        
        try {
            $text = new \Vonage\SMS\Message\SMS($to, $from, $message);
            $response = $this->client->sms()->send($text);
            
            $messages = $response->current();
            
            return [
                'message_id' => $messages->getMessageId(),
                'status' => $messages->getStatus() == 0 ? 'sent' : 'failed',
                'to' => $messages->getTo(),
                'from' => $from,
                'body' => $message,
                'cost' => $messages->getMessagePrice() ?? null,
                'currency' => $messages->getNetwork() ?? null,
                'segments' => 1,
                'direction' => 'outbound-api',
                'remaining_balance' => $messages->getRemainingBalance() ?? null,
            ];
        } catch (Exception $e) {
            throw new Exception("Vonage SMS 발송 실패: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Twilio를 통한 SMS 발송
     */
    protected function sendViaTwilio($to, $message, $from = null)
    {
        $config = json_decode($this->provider->config, true) ?? [];
        $from = $from ?? $config['from'] ?? null;
        
        if (!$from) {
            throw new Exception('발신 번호가 설정되지 않았습니다');
        }
        
        try {
            $twilioMessage = $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message
                ]
            );
            
            return [
                'message_id' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
                'to' => $twilioMessage->to,
                'from' => $twilioMessage->from,
                'body' => $twilioMessage->body,
                'cost' => $twilioMessage->price,
                'currency' => $twilioMessage->priceUnit,
                'segments' => $twilioMessage->numSegments,
                'direction' => $twilioMessage->direction,
            ];
        } catch (Exception $e) {
            throw new Exception("Twilio SMS 발송 실패: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * 계정 잠금 SMS 발송
     *
     * @param string $to 수신자 전화번호
     * @param string $userName 사용자 이름
     * @param string $unlockUrl 잠금 해제 URL
     * @param int $expiresInMinutes 링크 유효 시간 (분)
     * @return array 결과 배열
     */
    public function sendAccountLockedSms(
        string $to, 
        string $userName, 
        string $unlockUrl, 
        int $expiresInMinutes = 60
    ): array {
        $message = sprintf(
            "[보안 알림] %s님의 계정이 잠겼습니다.\n%d분 내 잠금 해제:\n%s",
            $userName,
            $expiresInMinutes,
            $unlockUrl
        );

        try {
            return $this->send($to, $message);
        } catch (Exception $e) {
            Log::error('계정 잠금 SMS 발송 실패', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 2FA 코드 SMS 발송
     *
     * @param string $to 수신자 전화번호
     * @param string $code 인증 코드
     * @return array 결과 배열
     */
    public function send2FACode(string $to, string $code): array
    {
        $message = sprintf(
            "[보안] 인증 코드: %s\n이 코드를 타인과 공유하지 마세요.",
            $code
        );

        try {
            return $this->send($to, $message);
        } catch (Exception $e) {
            Log::error('2FA SMS 발송 실패', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 전화번호를 국제 형식으로 변환
     *
     * @param string $phoneNumber 전화번호
     * @param string $defaultCountryCode 기본 국가 코드 (예: '82')
     * @return string
     */
    public function formatPhoneNumber(string $phoneNumber, string $defaultCountryCode = '82'): string
    {
        // 이미 국제 형식인 경우
        if (str_starts_with($phoneNumber, '+')) {
            return $phoneNumber;
        }
        
        // 숫자만 추출
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // 한국 전화번호 처리 (010으로 시작하는 경우)
        if (str_starts_with($phoneNumber, '010')) {
            $phoneNumber = substr($phoneNumber, 1); // 맨 앞 0 제거
            return '+' . $defaultCountryCode . $phoneNumber;
        }
        
        // 0으로 시작하는 다른 번호들
        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = substr($phoneNumber, 1);
            return '+' . $defaultCountryCode . $phoneNumber;
        }
        
        // 그 외의 경우
        return '+' . $defaultCountryCode . $phoneNumber;
    }
    
    /**
     * 메시지 분할 수 계산
     */
    protected function calculateMessageCount($message)
    {
        $length = mb_strlen($message);
        
        // SMS 표준: 한글 70자, 영문 160자
        // 간단하게 한글 기준으로 계산
        if ($length <= 70) {
            return 1;
        } elseif ($length <= 134) {
            return 2;
        } elseif ($length <= 201) {
            return 3;
        } else {
            return ceil($length / 67);
        }
    }
    
    /**
     * SMS 서비스 활성화 여부 확인
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * 발송 이력 조회
     */
    public function getHistory($filters = [])
    {
        $query = AdminSmsSend::query();
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }
        
        if (isset($filters['to_number'])) {
            $query->where('to_number', 'like', '%' . $filters['to_number'] . '%');
        }
        
        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        
        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }
    
    /**
     * 제공업체 목록 조회
     */
    public function getProviders($activeOnly = false)
    {
        $query = AdminSmsProvider::query();
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('priority', 'desc')->get();
    }
    
    /**
     * 대량 SMS 발송 (비동기 권장)
     *
     * @param array $recipients [['to' => '+821012345678', 'message' => '메시지'], ...]
     * @return array 결과 배열
     */
    public function sendBulk(array $recipients): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($recipients as $recipient) {
            if (!isset($recipient['to']) || !isset($recipient['message'])) {
                $failureCount++;
                continue;
            }

            try {
                $result = $this->send($recipient['to'], $recipient['message']);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }

                $results[] = [
                    'to' => $recipient['to'],
                    'success' => $result['success'],
                    'message_id' => $result['message_id'] ?? null,
                ];
            } catch (Exception $e) {
                $failureCount++;
                $results[] = [
                    'to' => $recipient['to'],
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }

            // Rate limiting (Twilio 권장)
            usleep(100000); // 0.1초 대기
        }

        return [
            'total' => count($recipients),
            'success' => $successCount,
            'failure' => $failureCount,
            'results' => $results
        ];
    }
}
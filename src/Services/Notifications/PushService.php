<?php

namespace Jiny\Admin\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * 푸시 알림 서비스
 * 
 * 브라우저 푸시(Web Push API) 및 모바일 푸시(FCM) 알림을 관리합니다.
 * 
 * @package Jiny\Admin
 * @since 1.0.0
 */
class PushService
{
    /**
     * 푸시 타입
     */
    const TYPE_WEB = 'web';
    const TYPE_MOBILE = 'mobile';
    
    /**
     * FCM 설정
     */
    protected $fcmServerKey;
    protected $fcmSenderId;
    
    /**
     * VAPID 키 (Web Push용)
     */
    protected $vapidPublicKey;
    protected $vapidPrivateKey;
    
    /**
     * 생성자
     */
    public function __construct()
    {
        $this->loadConfiguration();
    }
    
    /**
     * 설정 로드
     */
    protected function loadConfiguration(): void
    {
        // FCM 설정
        $this->fcmServerKey = config('admin.push.fcm.server_key');
        $this->fcmSenderId = config('admin.push.fcm.sender_id');
        
        // VAPID 설정
        $this->vapidPublicKey = config('admin.push.vapid.public_key');
        $this->vapidPrivateKey = config('admin.push.vapid.private_key');
    }
    
    /**
     * 푸시 알림 발송
     * 
     * @param int $userId 사용자 ID
     * @param string $title 제목
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array 발송 결과
     */
    public function send(int $userId, string $title, string $message, array $data = []): array
    {
        $results = [
            'web' => false,
            'mobile' => false
        ];
        
        // 사용자의 푸시 구독 정보 조회
        $subscriptions = $this->getUserSubscriptions($userId);
        
        foreach ($subscriptions as $subscription) {
            if ($subscription->type === self::TYPE_WEB) {
                $results['web'] = $this->sendWebPush($subscription, $title, $message, $data);
            } elseif ($subscription->type === self::TYPE_MOBILE) {
                $results['mobile'] = $this->sendMobilePush($subscription, $title, $message, $data);
            }
        }
        
        return $results;
    }
    
    /**
     * 여러 사용자에게 푸시 발송
     * 
     * @param array $userIds 사용자 ID 목록
     * @param string $title 제목
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array 발송 결과
     */
    public function sendToMultiple(array $userIds, string $title, string $message, array $data = []): array
    {
        $results = [];
        
        foreach ($userIds as $userId) {
            $results[$userId] = $this->send($userId, $title, $message, $data);
        }
        
        return $results;
    }
    
    /**
     * 브로드캐스트 푸시 발송
     * 
     * @param string $title 제목
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @param array $conditions 조건 (role, permission 등)
     * @return int 발송 건수
     */
    public function broadcast(string $title, string $message, array $data = [], array $conditions = []): int
    {
        // 조건에 맞는 사용자 조회
        $query = DB::table('users')
            ->join('admin_push_subscriptions', 'users.id', '=', 'admin_push_subscriptions.user_id')
            ->where('admin_push_subscriptions.is_active', true);
        
        // 조건 적용
        if (isset($conditions['role'])) {
            $query->whereIn('users.role', (array) $conditions['role']);
        }
        
        if (isset($conditions['permission'])) {
            $query->whereExists(function ($q) use ($conditions) {
                $q->select(DB::raw(1))
                    ->from('user_permissions')
                    ->whereColumn('user_permissions.user_id', 'users.id')
                    ->whereIn('permission', (array) $conditions['permission']);
            });
        }
        
        $subscriptions = $query->get();
        $sentCount = 0;
        
        foreach ($subscriptions as $subscription) {
            if ($subscription->type === self::TYPE_WEB) {
                if ($this->sendWebPush($subscription, $title, $message, $data)) {
                    $sentCount++;
                }
            } elseif ($subscription->type === self::TYPE_MOBILE) {
                if ($this->sendMobilePush($subscription, $title, $message, $data)) {
                    $sentCount++;
                }
            }
        }
        
        return $sentCount;
    }
    
    /**
     * 웹 푸시 발송
     * 
     * @param object $subscription 구독 정보
     * @param string $title 제목
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return bool
     */
    protected function sendWebPush($subscription, string $title, string $message, array $data = []): bool
    {
        try {
            if (!$this->vapidPublicKey || !$this->vapidPrivateKey) {
                Log::warning('VAPID keys not configured for web push');
                return false;
            }
            
            $endpoint = $subscription->endpoint;
            $auth = json_decode($subscription->auth_keys, true);
            
            // 페이로드 생성
            $payload = json_encode([
                'title' => $title,
                'body' => $message,
                'icon' => $data['icon'] ?? '/icon-192x192.png',
                'badge' => $data['badge'] ?? '/badge-72x72.png',
                'image' => $data['image'] ?? null,
                'tag' => $data['tag'] ?? 'admin-notification',
                'requireInteraction' => $data['require_interaction'] ?? false,
                'silent' => $data['silent'] ?? false,
                'data' => array_merge($data, [
                    'timestamp' => time(),
                    'url' => $data['url'] ?? '/admin/notifications'
                ]),
                'actions' => $data['actions'] ?? []
            ]);
            
            // Web Push 프로토콜 헤더 생성
            $headers = $this->generateWebPushHeaders($endpoint, $payload);
            
            // 암호화된 페이로드 생성 (실제 구현시 web-push-php 라이브러리 사용 권장)
            $encryptedPayload = $this->encryptPayload($payload, $auth);
            
            // 푸시 발송
            $response = Http::withHeaders($headers)
                ->withBody($encryptedPayload, 'application/octet-stream')
                ->post($endpoint);
            
            // 로그 기록
            $this->logPush($subscription->user_id, self::TYPE_WEB, $title, $response->successful());
            
            // 실패한 엔드포인트 처리
            if ($response->status() === 410) {
                // Gone - 구독 제거
                $this->removeSubscription($subscription->id);
            }
            
            return $response->successful();
            
        } catch (Exception $e) {
            Log::error('Web push failed: ' . $e->getMessage());
            $this->logPush($subscription->user_id ?? null, self::TYPE_WEB, $title, false, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 모바일 푸시 발송 (FCM)
     * 
     * @param object $subscription 구독 정보
     * @param string $title 제목
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return bool
     */
    protected function sendMobilePush($subscription, string $title, string $message, array $data = []): bool
    {
        try {
            if (!$this->fcmServerKey) {
                Log::warning('FCM server key not configured');
                return false;
            }
            
            $fcmToken = $subscription->endpoint; // FCM 토큰
            
            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                    'icon' => $data['icon'] ?? 'notification_icon',
                    'color' => $data['color'] ?? '#007BFF',
                    'sound' => $data['sound'] ?? 'default',
                    'badge' => $data['badge'] ?? 1,
                    'click_action' => $data['click_action'] ?? 'OPEN_ADMIN_ACTIVITY'
                ],
                'data' => array_merge($data, [
                    'timestamp' => time(),
                    'type' => 'admin_notification'
                ]),
                'priority' => $data['priority'] ?? 'high',
                'content_available' => true
            ];
            
            // iOS 특정 설정
            if (isset($data['ios'])) {
                $payload['apns'] = [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body' => $message
                            ],
                            'badge' => $data['badge'] ?? 1,
                            'sound' => $data['sound'] ?? 'default'
                        ]
                    ]
                ];
            }
            
            // FCM 발송
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json'
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);
            
            // 로그 기록
            $this->logPush($subscription->user_id, self::TYPE_MOBILE, $title, $response->successful());
            
            // 응답 처리
            $result = $response->json();
            if (isset($result['results'][0]['error'])) {
                $error = $result['results'][0]['error'];
                if (in_array($error, ['InvalidRegistration', 'NotRegistered'])) {
                    // 토큰 제거
                    $this->removeSubscription($subscription->id);
                }
            }
            
            return $response->successful() && isset($result['success']) && $result['success'] > 0;
            
        } catch (Exception $e) {
            Log::error('Mobile push failed: ' . $e->getMessage());
            $this->logPush($subscription->user_id ?? null, self::TYPE_MOBILE, $title, false, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 사용자 구독 정보 조회
     * 
     * @param int $userId 사용자 ID
     * @return \Illuminate\Support\Collection
     */
    protected function getUserSubscriptions(int $userId)
    {
        return DB::table('admin_push_subscriptions')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();
    }
    
    /**
     * 푸시 구독 등록
     * 
     * @param int $userId 사용자 ID
     * @param string $type 푸시 타입
     * @param string $endpoint 엔드포인트/토큰
     * @param array|null $authKeys 인증 키 (Web Push용)
     * @return int 구독 ID
     */
    public function subscribe(int $userId, string $type, string $endpoint, ?array $authKeys = null): int
    {
        // 기존 구독 확인
        $existing = DB::table('admin_push_subscriptions')
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('endpoint', $endpoint)
            ->first();
        
        if ($existing) {
            // 업데이트
            DB::table('admin_push_subscriptions')
                ->where('id', $existing->id)
                ->update([
                    'auth_keys' => $authKeys ? json_encode($authKeys) : null,
                    'is_active' => true,
                    'updated_at' => now()
                ]);
            
            return $existing->id;
        }
        
        // 새로 등록
        return DB::table('admin_push_subscriptions')->insertGetId([
            'user_id' => $userId,
            'type' => $type,
            'endpoint' => $endpoint,
            'auth_keys' => $authKeys ? json_encode($authKeys) : null,
            'device_info' => json_encode($this->getDeviceInfo()),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * 푸시 구독 해제
     * 
     * @param int $userId 사용자 ID
     * @param string|null $type 푸시 타입 (null이면 모든 타입)
     * @param string|null $endpoint 특정 엔드포인트
     * @return bool
     */
    public function unsubscribe(int $userId, ?string $type = null, ?string $endpoint = null): bool
    {
        $query = DB::table('admin_push_subscriptions')
            ->where('user_id', $userId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        if ($endpoint) {
            $query->where('endpoint', $endpoint);
        }
        
        return $query->update(['is_active' => false, 'updated_at' => now()]) > 0;
    }
    
    /**
     * 구독 제거
     * 
     * @param int $subscriptionId 구독 ID
     * @return bool
     */
    protected function removeSubscription(int $subscriptionId): bool
    {
        return DB::table('admin_push_subscriptions')
            ->where('id', $subscriptionId)
            ->delete() > 0;
    }
    
    /**
     * Web Push 헤더 생성
     * 
     * @param string $endpoint 엔드포인트
     * @param string $payload 페이로드
     * @return array
     */
    protected function generateWebPushHeaders(string $endpoint, string $payload): array
    {
        // 실제 구현시 web-push-php 라이브러리 사용 권장
        // 여기서는 기본 헤더만 제공
        return [
            'TTL' => '86400', // 24시간
            'Content-Encoding' => 'aesgcm',
            'Content-Type' => 'application/octet-stream',
            'Authorization' => 'WebPush ' . $this->generateVapidHeader($endpoint)
        ];
    }
    
    /**
     * VAPID 헤더 생성
     * 
     * @param string $endpoint 엔드포인트
     * @return string
     */
    protected function generateVapidHeader(string $endpoint): string
    {
        // 실제 구현시 JWT 토큰 생성 필요
        // 여기서는 더미 값 반환
        return 'dummy-vapid-header';
    }
    
    /**
     * 페이로드 암호화
     * 
     * @param string $payload 페이로드
     * @param array $authKeys 인증 키
     * @return string
     */
    protected function encryptPayload(string $payload, array $authKeys): string
    {
        // 실제 구현시 ECDH + HKDF + AES-GCM 암호화 필요
        // 여기서는 원본 반환
        return $payload;
    }
    
    /**
     * 디바이스 정보 수집
     * 
     * @return array
     */
    protected function getDeviceInfo(): array
    {
        return [
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'platform' => $this->detectPlatform(),
            'browser' => $this->detectBrowser()
        ];
    }
    
    /**
     * 플랫폼 감지
     * 
     * @return string
     */
    protected function detectPlatform(): string
    {
        $userAgent = request()->userAgent();
        
        if (stripos($userAgent, 'android') !== false) {
            return 'Android';
        } elseif (stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false) {
            return 'iOS';
        } elseif (stripos($userAgent, 'windows') !== false) {
            return 'Windows';
        } elseif (stripos($userAgent, 'mac') !== false) {
            return 'macOS';
        } elseif (stripos($userAgent, 'linux') !== false) {
            return 'Linux';
        }
        
        return 'Unknown';
    }
    
    /**
     * 브라우저 감지
     * 
     * @return string
     */
    protected function detectBrowser(): string
    {
        $userAgent = request()->userAgent();
        
        if (stripos($userAgent, 'chrome') !== false) {
            return 'Chrome';
        } elseif (stripos($userAgent, 'firefox') !== false) {
            return 'Firefox';
        } elseif (stripos($userAgent, 'safari') !== false) {
            return 'Safari';
        } elseif (stripos($userAgent, 'edge') !== false) {
            return 'Edge';
        }
        
        return 'Unknown';
    }
    
    /**
     * 푸시 발송 로그 기록
     * 
     * @param int|null $userId 사용자 ID
     * @param string $type 푸시 타입
     * @param string $title 제목
     * @param bool $success 성공 여부
     * @param string|null $error 에러 메시지
     */
    protected function logPush(?int $userId, string $type, string $title, bool $success, ?string $error = null): void
    {
        try {
            DB::table('admin_push_logs')->insert([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'status' => $success ? 'sent' : 'failed',
                'error_message' => $error,
                'sent_at' => now(),
                'created_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log push notification: ' . $e->getMessage());
        }
    }
    
    /**
     * VAPID 키 생성
     * 
     * @return array
     */
    public function generateVapidKeys(): array
    {
        // 실제 구현시 openssl 사용하여 ECDH 키 페어 생성
        // 여기서는 예시 값 반환
        return [
            'public_key' => base64_encode(random_bytes(65)),
            'private_key' => base64_encode(random_bytes(32))
        ];
    }
    
    /**
     * 푸시 통계 조회
     * 
     * @param int|null $userId 사용자 ID (null이면 전체)
     * @param string|null $type 푸시 타입
     * @param \DateTime|null $from 시작일
     * @param \DateTime|null $to 종료일
     * @return array
     */
    public function getStatistics(?int $userId = null, ?string $type = null, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $query = DB::table('admin_push_logs');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($type) {
            $query->where('type', $type);
        }
        
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        
        $total = clone $query;
        $success = clone $query;
        $failed = clone $query;
        
        return [
            'total' => $total->count(),
            'success' => $success->where('status', 'sent')->count(),
            'failed' => $failed->where('status', 'failed')->count(),
            'by_type' => $query->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray()
        ];
    }
}
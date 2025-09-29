# 통합 알림 센터 문서

## 개요

Jiny Admin의 통합 알림 센터는 이메일, SMS, 웹훅(Slack/Discord/Teams), 푸시 알림을 통합 관리하는 멀티채널 알림 시스템입니다.

## 시스템 아키텍처

```
┌──────────────────────────────────────────────────────────┐
│                   NotificationService                      │
│              (통합 알림 오케스트레이터)                       │
└────────────────┬─────────────────────────────────────────┘
                 │
    ┌────────────┼────────────┬────────────┬──────────────┐
    ▼            ▼            ▼            ▼              ▼
EmailService  SmsService  WebhookService  PushService  EventBus
    │            │            │            │              │
    ├─Templates  ├─Twilio    ├─Slack     ├─FCM         ├─Rules
    ├─Logs      ├─Vonage    ├─Discord   ├─WebPush     ├─Triggers
    └─Queue     └─Aligo     └─Teams     └─APNS        └─Hooks
```

## 채널별 상세 문서

1. [이메일 알림](./email-notifications.md)
2. [SMS 알림](./sms-notifications.md)
3. [웹훅 알림 (Slack/Discord/Teams)](./webhook-notifications.md)
4. [푸시 알림](./push-notifications.md)

## 핵심 기능

### 1. 멀티채널 동시 발송

```php
// 모든 채널로 동시 발송
$result = $notificationService->notifyMultiChannel(
    'critical_alert',
    [
        'message' => '시스템 장애 발생',
        'severity' => 'high',
        'affected_service' => 'payment_gateway'
    ],
    ['email', 'sms', 'webhook', 'push']
);

// 결과
// [
//     'email' => true,
//     'sms' => true,
//     'webhook' => ['slack' => true, 'discord' => true],
//     'push' => ['web' => 5, 'mobile' => 3]  // 발송 건수
// ]
```

### 2. 이벤트 기반 자동 알림

```php
/**
 * 이벤트 처리 플로우:
 * 
 * Event 발생
 * ├── EventListener 감지
 * ├── NotificationService::notify() 호출
 * ├── 규칙 엔진 평가
 * │   ├── 조건 확인
 * │   ├── 수신자 결정
 * │   └── 채널 선택
 * └── 멀티채널 발송
 */
```

### 3. 브로드캐스트

```php
// 조건별 대량 발송
$notificationService->broadcast(
    '시스템 점검 안내',
    '오후 11시부터 새벽 2시까지 점검 예정입니다.',
    ['email', 'push'],
    ['role' => ['admin', 'moderator']]  // 조건
);
```

## 통합 설정

### 기본 설정 구조

```php
// config/admin/notifications.php
return [
    'channels' => [
        'email' => [
            'enabled' => true,
            'default_from' => 'noreply@example.com',
            'queue' => 'notifications'
        ],
        'sms' => [
            'enabled' => true,
            'provider' => 'twilio',  // twilio|vonage|aws|aligo
            'from' => '+1234567890'
        ],
        'webhook' => [
            'enabled' => true,
            'timeout' => 10,
            'retry' => 3
        ],
        'push' => [
            'enabled' => true,
            'fcm' => ['server_key' => env('FCM_SERVER_KEY')],
            'vapid' => [
                'public_key' => env('VAPID_PUBLIC_KEY'),
                'private_key' => env('VAPID_PRIVATE_KEY')
            ]
        ]
    ],
    
    'events' => [
        'login_failed' => ['email', 'webhook'],
        'account_locked' => ['email', 'sms', 'push'],
        'system_alert' => ['webhook', 'push'],
        'payment_received' => ['email'],
    ],
    
    'throttle' => [
        'enabled' => true,
        'max_per_minute' => 60,
        'max_per_hour' => 1000
    ]
];
```

## 이벤트 타입과 채널 매핑

| 이벤트 | 설명 | 기본 채널 | 우선순위 |
|--------|------|-----------|----------|
| `login_failed` | 로그인 실패 | 이메일 | 보통 |
| `account_locked` | 계정 잠금 | 이메일, SMS | 높음 |
| `password_changed` | 비밀번호 변경 | 이메일 | 높음 |
| `two_fa_changed` | 2FA 설정 변경 | 이메일, 푸시 | 높음 |
| `ip_blocked` | IP 차단 | 이메일, 웹훅 | 긴급 |
| `system_alert` | 시스템 경고 | 웹훅, 푸시 | 긴급 |
| `backup_completed` | 백업 완료 | 이메일 | 낮음 |
| `report_generated` | 보고서 생성 | 이메일 | 낮음 |

## 규칙 엔진

### 알림 규칙 생성

```php
// 복잡한 조건의 알림 규칙
$notificationService->createRule([
    'name' => '대량 삭제 경고',
    'event_type' => 'bulk_delete',
    'conditions' => [
        'deleted_count' => ['operator' => '>', 'value' => 100],
        'user_role' => ['operator' => 'not_in', 'value' => ['super_admin']],
        'time' => ['operator' => 'between', 'value' => ['22:00', '06:00']]
    ],
    'recipient_type' => 'role',
    'recipients' => ['admin', 'security'],
    'channels' => ['email', 'webhook', 'push'],
    'priority' => 'high',
    'throttle_minutes' => 5  // 5분 내 재발송 방지
]);
```

### 동적 수신자 결정

```php
/**
 * 수신자 결정 로직:
 * 
 * determineRecipients()
 * ├── recipient_type 확인
 * │   ├── 'user' - 이벤트 관련 사용자
 * │   ├── 'admin' - 모든 관리자
 * │   ├── 'role' - 특정 역할
 * │   ├── 'custom' - 지정된 이메일
 * │   └── 'dynamic' - 콜백 함수
 * └── 추가 수신자 (notify_admins 플래그)
 */
```

## Hook 시스템

### Hook 등록 및 사용

```php
// 전역 Hook 등록
$notificationService->registerHook('before_send', function($event, $data, $recipient) {
    // 업무 시간 외 알림 차단
    if (!isBusinessHours() && $data['priority'] !== 'urgent') {
        return false;  // 발송 취소
    }
    return true;
});

// 이벤트별 Hook
$notificationService->registerHook('after_send_account_locked', 
    function($event, $data, $recipient, $result) {
        if ($result['sms'] === false) {
            // SMS 실패 시 대체 처리
            SlackAlert::send("SMS 발송 실패: {$recipient['phone']}");
        }
    }
);
```

## 성능 최적화

### 1. 큐 사용

```php
// 대량 알림은 큐로 처리
NotificationJob::dispatch($event, $data)
    ->onQueue('high-priority')
    ->delay(now()->addSeconds(5));
```

### 2. 배치 처리

```php
// 100명씩 배치 처리
$users->chunk(100, function ($chunk) use ($notificationService) {
    $notificationService->batchNotify($chunk, 'newsletter', $data);
});
```

### 3. 캐싱

```php
// 템플릿 캐싱
Cache::remember("notification:template:{$slug}", 3600, function() {
    return NotificationTemplate::where('slug', $slug)->first();
});

// 수신자 목록 캐싱
Cache::remember("recipients:role:admin", 300, function() {
    return User::where('role', 'admin')->pluck('email');
});
```

## 모니터링 및 통계

### 실시간 대시보드

```php
// 알림 통계 조회
$stats = [
    'today' => [
        'total' => NotificationLog::today()->count(),
        'by_channel' => NotificationLog::today()
            ->groupBy('channel')
            ->selectRaw('channel, count(*) as count')
            ->pluck('count', 'channel'),
        'by_status' => NotificationLog::today()
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status'),
    ],
    'failures' => NotificationLog::failed()->today()->count(),
    'queue_size' => Queue::size('notifications')
];
```

### 알림 추적

```php
// 개별 알림 추적
$tracking = NotificationTracking::create([
    'notification_id' => $notificationId,
    'recipient' => $recipient,
    'channel' => 'email',
    'sent_at' => now(),
    'tracking_id' => Str::uuid()
]);

// 열람 추적
Route::get('/track/{trackingId}', function($trackingId) {
    NotificationTracking::where('tracking_id', $trackingId)
        ->update(['opened_at' => now()]);
});
```

## 오류 처리

### 재시도 로직

```php
/**
 * 재시도 전략:
 * 
 * 1차 시도 실패
 * ├── 30초 후 재시도
 * ├── 2분 후 재시도
 * ├── 10분 후 재시도
 * └── 실패 로그 및 알림
 */

class NotificationRetryStrategy {
    protected $attempts = [30, 120, 600];  // 초 단위
    
    public function retry($notification, $attempt) {
        if ($attempt >= count($this->attempts)) {
            $this->handleFailure($notification);
            return false;
        }
        
        NotificationJob::dispatch($notification)
            ->delay(now()->addSeconds($this->attempts[$attempt]));
        
        return true;
    }
}
```

### 폴백 처리

```php
// 채널 실패 시 대체 채널 사용
if (!$smsService->send($phone, $message)) {
    // SMS 실패 시 이메일로 대체
    $emailService->send($email, 'SMS 대체 알림', $message);
}
```

## 보안 고려사항

1. **데이터 암호화**: 민감한 정보는 암호화하여 저장
2. **접근 제어**: API 키와 웹훅 URL 보호
3. **Rate Limiting**: 채널별 발송 제한
4. **검증**: 수신자 주소 유효성 검사
5. **로깅**: 모든 알림 활동 기록
6. **감사**: 정기적인 로그 검토

## 테스트 가이드

### 통합 테스트

```php
public function testMultiChannelNotification()
{
    // Mock 설정
    Mail::fake();
    SMS::fake();
    Http::fake();
    
    // 알림 발송
    $result = $this->notificationService->notifyMultiChannel(
        'test_event',
        ['message' => 'Test'],
        ['email', 'sms', 'webhook']
    );
    
    // 검증
    Mail::assertSent(TestNotification::class);
    SMS::assertSent('+821012345678');
    Http::assertSent(function ($request) {
        return $request->url() == 'https://hooks.slack.com/test';
    });
}
```

## 관련 파일

### 서비스
- `App\Services\NotificationService.php`
- `App\Services\Notifications\WebhookService.php`
- `App\Services\Notifications\PushService.php`

### 컨트롤러
- `App\Http\Controllers\Admin\AdminNotificationSettings.php`

### 모델
- `App\Models\NotificationRule.php`
- `App\Models\NotificationLog.php`

### 마이그레이션
- `database\migrations\*_create_notification_*.php`
- `database\migrations\*_create_webhook_*.php`
- `database\migrations\*_create_push_*.php`
<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Jiny\Admin\Mail\EmailMailable;
use Jiny\Admin\Services\SmsService;
use Jiny\Admin\Services\Notifications\WebhookService;
use Jiny\Admin\Services\Notifications\PushService;
use Exception;

/**
 * 통합 알림 서비스 (Unified Notification Service)
 * 
 * 멀티채널 알림 발송을 관리하는 중앙 오케스트레이터입니다.
 * 이메일, SMS, 웹훅(Slack/Discord/Teams), 푸시 알림을 통합 관리합니다.
 * 
 * @package Jiny\Admin
 * @author Jiny Admin Team
 * @since 1.0.0
 * 
 * 의존성 트리:
 * NotificationService
 * ├── EmailTemplateService    - 이메일 템플릿 관리 및 렌더링
 * ├── EmailLogService         - 이메일 발송 로그 및 추적
 * ├── SmsService              - SMS 발송 (Twilio/Vonage/AWS/Aligo)
 * ├── WebhookService          - 웹훅 발송 (Slack/Discord/Teams)
 * └── PushService            - 푸시 알림 (FCM/WebPush)
 */
class NotificationService
{
    /** @var EmailTemplateService 이메일 템플릿 서비스 */
    protected $templateService;
    
    /** @var EmailLogService 이메일 로그 서비스 */
    protected $logService;
    
    /** @var SmsService SMS 발송 서비스 */
    protected $smsService;
    
    /** @var WebhookService 웹훅 알림 서비스 */
    protected $webhookService;
    
    /** @var PushService 푸시 알림 서비스 */
    protected $pushService;
    
    /** @var array Hook 콜백 저장소 */
    protected $hooks = [];

    /**
     * NotificationService 생성자
     * 
     * 모든 알림 채널 서비스를 초기화합니다.
     * 
     * 초기화 순서:
     * 1. EmailTemplateService - 템플릿 시스템
     * 2. EmailLogService     - 로깅 시스템
     * 3. SmsService         - SMS 채널
     * 4. WebhookService     - 웹훅 채널
     * 5. PushService        - 푸시 채널
     */
    public function __construct()
    {
        $this->templateService = new EmailTemplateService();
        $this->logService = new EmailLogService();
        $this->smsService = new SmsService();
        $this->webhookService = new WebhookService();
        $this->pushService = new PushService();
    }

    /**
     * Hook 등록
     * 
     * 이벤트 발생 전/후에 실행될 콜백을 등록합니다.
     * Hook을 통해 알림 발송을 커스터마이징할 수 있습니다.
     * 
     * @param string $event Hook 이벤트명 (예: 'before_send_login_failed')
     * @param callable $callback 실행할 콜백 함수
     * 
     * 사용 예시:
     * $service->registerHook('before_send', function($data, $recipient) {
     *     // false 반환 시 발송 취소
     *     return $data['priority'] === 'high';
     * });
     */
    public function registerHook(string $event, callable $callback): void
    {
        if (!isset($this->hooks[$event])) {
            $this->hooks[$event] = [];
        }
        $this->hooks[$event][] = $callback;
    }

    /**
     * 이벤트 기반 알림 발송 (메인 메서드)
     * 
     * 호출 관계 트리:
     * notify()
     * ├── getActiveRules()              // 활성 알림 규칙 조회
     * │   ├── DB 조회 (admin_email_notification_rules)
     * │   └── 시간/요일 필터링
     * ├── [규칙별 반복]
     * │   ├── checkConditions()         // 조건 확인
     * │   │   └── evaluateCondition()   // 조건 평가
     * │   ├── checkThrottle()          // 스로틀링 체크
     * │   │   └── DB 조회 (최근 발송 이력)
     * │   ├── determineRecipients()    // 수신자 결정
     * │   │   ├── user: 이벤트 사용자
     * │   │   ├── admin: 모든 관리자
     * │   │   ├── role: 특정 역할
     * │   │   └── custom: 지정 이메일
     * │   └── [수신자별 반복]
     * │       ├── executeHooks('before_send')
     * │       ├── sendNotification()   // 실제 발송
     * │       │   ├── templateService->render()
     * │       │   ├── logService->createLog()
     * │       │   └── Mail::send()
     * │       └── executeHooks('after_send')
     * └── updateRuleStatistics()       // 통계 업데이트
     * 
     * @param string $eventType 이벤트 타입 (예: 'login_failed', 'account_locked')
     * @param array $data 이벤트 데이터
     * @return bool 발송 성공 여부
     */
    public function notify(string $eventType, array $data = []): bool
    {
        try {
            // 알림 규칙 조회
            $rules = $this->getActiveRules($eventType);

            if ($rules->isEmpty()) {
                Log::info("No active notification rules for event: {$eventType}");
                return false;
            }

            $success = true;

            foreach ($rules as $rule) {
                // 조건 체크
                if (!$this->checkConditions($rule, $data)) {
                    continue;
                }

                // 스로틀링 체크
                if (!$this->checkThrottle($rule, $data)) {
                    Log::info("Notification throttled for rule: {$rule->name}");
                    continue;
                }

                // 수신자 결정
                $recipients = $this->determineRecipients($rule, $data);

                foreach ($recipients as $recipient) {
                    // 발송 전 Hook 실행
                    if (!$this->executeHooks('before_send', $eventType, $data, $recipient)) {
                        continue;
                    }

                    // 이메일 발송
                    $result = $this->sendNotification($rule, $recipient, $data);

                    if (!$result) {
                        $success = false;
                    }

                    // 발송 후 Hook 실행
                    $this->executeHooks('after_send', $eventType, $data, $recipient, $result);
                }

                // 규칙 통계 업데이트
                $this->updateRuleStatistics($rule->id);
            }

            return $success;

        } catch (Exception $e) {
            Log::error("Notification failed for event {$eventType}: " . $e->getMessage(), [
                'event' => $eventType,
                'data' => $data,
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * 특정 이벤트 알림 메서드들
     */
    
    /**
     * 로그인 실패 알림
     */
    public function notifyLoginFailed(string $email, int $failedAttempts, string $ipAddress = null): bool
    {
        return $this->notify('login_failed', [
            'user_email' => $email,
            'failed_attempts' => $failedAttempts,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => request()->userAgent(),
            'attempted_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * 2FA 설정 변경 알림
     */
    public function notify2FAChanged(int $userId, string $action = 'enabled'): bool
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user) {
            return false;
        }

        return $this->notify('two_fa_' . $action, [
            'user_id' => $userId,
            'user_name' => $user->name,
            'user_email' => $user->email,
            $action . '_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * IP 차단 알림
     */
    public function notifyIPBlocked(string $ipAddress, string $reason, int $blockedMinutes = 60): bool
    {
        return $this->notify('ip_blocked', [
            'ip_address' => $ipAddress,
            'blocked_reason' => $reason,
            'blocked_at' => now()->format('Y-m-d H:i:s'),
            'blocked_until' => now()->addMinutes($blockedMinutes)->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * 비밀번호 변경 알림
     */
    public function notifyPasswordChanged(int $userId): bool
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user) {
            return false;
        }

        return $this->notify('password_changed', [
            'user_id' => $userId,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'changed_at' => now()->format('Y-m-d H:i:s'),
            'ip_address' => request()->ip()
        ]);
    }

    /**
     * 계정 잠금 알림 (이메일)
     */
    public function notifyAccountLocked(int $userId, string $reason = null, array $additionalData = []): bool
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user) {
            return false;
        }

        $data = array_merge([
            'user_id' => $userId,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'locked_reason' => $reason ?? '반복된 로그인 실패',
            'locked_at' => now()->format('Y-m-d H:i:s'),
            'unlock_link' => $additionalData['unlock_url'] ?? url('/account/unlock/request')
        ], $additionalData);

        return $this->notify('account_locked', $data);
    }

    /**
     * 계정 잠금 SMS 알림
     */
    public function notifyAccountLockedBySms(int $userId, string $unlockUrl, int $expiresInMinutes = 60): bool
    {
        try {
            $user = DB::table('users')->where('id', $userId)->first();
            
            if (!$user || !$user->phone_number) {
                return false;
            }

            // SMS 서비스가 활성화되어 있는지 확인
            if (!$this->smsService->isEnabled()) {
                Log::warning('SMS 서비스가 비활성화되어 있습니다.');
                return false;
            }

            // SMS 발송
            $result = $this->smsService->sendAccountLockedSms(
                $user->phone_number,
                $user->name ?? 'User',
                $unlockUrl,
                $expiresInMinutes
            );

            // SMS 발송 로그 기록
            if ($result['success']) {
                DB::table('admin_user_logs')->insert([
                    'user_id' => $userId,
                    'action' => 'sms_sent',
                    'details' => json_encode(['description' => '계정 잠금 SMS 알림 발송']),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => json_encode([
                        'type' => 'account_locked',
                        'to' => $this->maskPhoneNumber($user->phone_number),
                        'message_id' => $result['message_id'] ?? null
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error('계정 잠금 SMS 알림 실패', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 계정 잠금 알림 (이메일과 SMS 동시 발송)
     */
    public function notifyAccountLockedAll(int $userId, string $reason = null, string $unlockUrl = null, int $expiresInMinutes = 60): array
    {
        $results = [
            'email' => false,
            'sms' => false
        ];

        // 이메일 발송
        $results['email'] = $this->notifyAccountLocked($userId, $reason, [
            'unlock_url' => $unlockUrl,
            'expires_in_minutes' => $expiresInMinutes
        ]);

        // SMS 발송
        if ($unlockUrl) {
            $results['sms'] = $this->notifyAccountLockedBySms($userId, $unlockUrl, $expiresInMinutes);
        }

        return $results;
    }

    /**
     * 전화번호 마스킹
     */
    protected function maskPhoneNumber(string $phoneNumber): string
    {
        if (strlen($phoneNumber) < 8) {
            return '***';
        }

        $visibleStart = 4;
        $visibleEnd = 2;
        $maskLength = strlen($phoneNumber) - $visibleStart - $visibleEnd;
        
        return substr($phoneNumber, 0, $visibleStart) 
            . str_repeat('*', $maskLength) 
            . substr($phoneNumber, -$visibleEnd);
    }

    /**
     * 활성 알림 규칙 조회
     */
    protected function getActiveRules(string $eventType)
    {
        $now = now();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->dayOfWeek;

        return DB::table('admin_email_notification_rules')
            ->where('event_type', $eventType)
            ->where('is_active', true)
            ->where(function ($query) use ($currentTime) {
                $query->whereNull('active_from')
                    ->orWhere('active_from', '<=', $currentTime);
            })
            ->where(function ($query) use ($currentTime) {
                $query->whereNull('active_to')
                    ->orWhere('active_to', '>=', $currentTime);
            })
            ->get()
            ->filter(function ($rule) use ($currentDay) {
                // 활성 요일 체크
                if ($rule->active_days) {
                    $activeDays = json_decode($rule->active_days, true);
                    if (!in_array($currentDay, $activeDays)) {
                        return false;
                    }
                }
                return true;
            });
    }

    /**
     * 조건 체크
     */
    protected function checkConditions($rule, array $data): bool
    {
        if (!$rule->conditions) {
            return true;
        }

        $conditions = json_decode($rule->conditions, true);

        foreach ($conditions as $field => $condition) {
            if (!isset($data[$field])) {
                return false;
            }

            // 조건 평가 (예: failed_attempts > 3)
            if (is_array($condition)) {
                $operator = $condition['operator'] ?? '=';
                $value = $condition['value'] ?? null;

                if (!$this->evaluateCondition($data[$field], $operator, $value)) {
                    return false;
                }
            } elseif ($data[$field] != $condition) {
                return false;
            }
        }

        return true;
    }

    /**
     * 조건 평가
     */
    protected function evaluateCondition($fieldValue, string $operator, $value): bool
    {
        switch ($operator) {
            case '>':
                return $fieldValue > $value;
            case '>=':
                return $fieldValue >= $value;
            case '<':
                return $fieldValue < $value;
            case '<=':
                return $fieldValue <= $value;
            case '!=':
                return $fieldValue != $value;
            case 'in':
                return in_array($fieldValue, (array) $value);
            case 'not_in':
                return !in_array($fieldValue, (array) $value);
            case '=':
            default:
                return $fieldValue == $value;
        }
    }

    /**
     * 스로틀링 체크
     */
    protected function checkThrottle($rule, array $data): bool
    {
        if (!$rule->throttle_minutes) {
            return true;
        }

        // 최근 발송 체크
        $lastSent = DB::table('admin_email_logs')
            ->where('event_type', $rule->event_type)
            ->where('status', 'sent')
            ->where('created_at', '>', now()->subMinutes($rule->throttle_minutes))
            ->exists();

        return !$lastSent;
    }

    /**
     * 수신자 결정
     */
    protected function determineRecipients($rule, array $data): array
    {
        $recipients = [];

        switch ($rule->recipient_type) {
            case 'user':
                // 이벤트 관련 사용자
                if (isset($data['user_email'])) {
                    $recipients[] = [
                        'email' => $data['user_email'],
                        'name' => $data['user_name'] ?? null
                    ];
                } elseif (isset($data['user_id'])) {
                    $user = DB::table('users')->where('id', $data['user_id'])->first();
                    if ($user) {
                        $recipients[] = [
                            'email' => $user->email,
                            'name' => $user->name
                        ];
                    }
                }
                break;

            case 'admin':
                // 모든 관리자
                $admins = DB::table('users')
                    ->where('is_admin', true)
                    ->get();
                foreach ($admins as $admin) {
                    $recipients[] = [
                        'email' => $admin->email,
                        'name' => $admin->name
                    ];
                }
                break;

            case 'role':
                // 특정 역할의 사용자들
                if ($rule->recipients) {
                    $roles = json_decode($rule->recipients, true);
                    $users = DB::table('users')
                        ->whereIn('role', $roles)
                        ->get();
                    foreach ($users as $user) {
                        $recipients[] = [
                            'email' => $user->email,
                            'name' => $user->name
                        ];
                    }
                }
                break;

            case 'custom':
                // 지정된 이메일 주소들
                if ($rule->recipients) {
                    $emails = json_decode($rule->recipients, true);
                    foreach ($emails as $email) {
                        $recipients[] = [
                            'email' => $email,
                            'name' => null
                        ];
                    }
                }
                break;
        }

        // 추가 수신자 설정
        if ($rule->notify_admins) {
            $admins = DB::table('users')
                ->where('is_admin', true)
                ->get();
            foreach ($admins as $admin) {
                $recipients[] = [
                    'email' => $admin->email,
                    'name' => $admin->name
                ];
            }
        }

        // 중복 제거
        $uniqueRecipients = [];
        $emails = [];
        foreach ($recipients as $recipient) {
            if (!in_array($recipient['email'], $emails)) {
                $uniqueRecipients[] = $recipient;
                $emails[] = $recipient['email'];
            }
        }

        return $uniqueRecipients;
    }

    /**
     * 알림 발송
     */
    protected function sendNotification($rule, array $recipient, array $data): bool
    {
        try {
            // 템플릿 가져오기
            $template = null;
            if ($rule->template_id) {
                $template = $this->templateService->getTemplateById($rule->template_id);
            } elseif ($rule->template_slug) {
                $template = $this->templateService->getTemplate($rule->template_slug);
            }

            if (!$template) {
                Log::error("No template found for rule: {$rule->name}");
                return false;
            }

            // 템플릿 렌더링
            $rendered = $this->templateService->render($template, $data);

            // 로그 생성
            $logId = $this->logService->createLog([
                'template_id' => $template->id,
                'template_slug' => $template->slug,
                'to_email' => $recipient['email'],
                'to_name' => $recipient['name'],
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'variables' => $data,
                'event_type' => $rule->event_type,
                'priority' => $rule->priority,
                'user_id' => $data['user_id'] ?? null
            ]);

            // 지연 발송 처리
            if ($rule->delay_seconds > 0) {
                // 큐에 지연 추가 (Laravel Queue 사용 시)
                // 여기서는 즉시 발송으로 구현
                sleep(min($rule->delay_seconds, 5)); // 최대 5초 지연
            }

            // 메일 발송
            Mail::to($recipient['email'])->send(new EmailMailable(
                $rendered['subject'],
                $rendered['body'],
                config('mail.from.address'),
                config('mail.from.name'),
                $recipient['email']
            ));

            // 발송 성공 기록
            $this->logService->markAsSent($logId);

            return true;

        } catch (Exception $e) {
            Log::error("Failed to send notification: " . $e->getMessage(), [
                'rule' => $rule->name,
                'recipient' => $recipient,
                'data' => $data,
                'exception' => $e
            ]);

            // 발송 실패 기록
            if (isset($logId)) {
                $this->logService->markAsFailed($logId, $e->getMessage());
            }

            return false;
        }
    }

    /**
     * Hook 실행
     */
    protected function executeHooks(string $timing, string $eventType, array $data, array $recipient, bool $result = null)
    {
        $hookKey = "{$timing}_{$eventType}";
        
        if (isset($this->hooks[$hookKey])) {
            foreach ($this->hooks[$hookKey] as $hook) {
                try {
                    $continue = call_user_func($hook, $data, $recipient, $result);
                    if ($continue === false) {
                        return false;
                    }
                } catch (Exception $e) {
                    Log::error("Hook execution failed: " . $e->getMessage());
                }
            }
        }

        return true;
    }

    /**
     * 규칙 통계 업데이트
     */
    protected function updateRuleStatistics(int $ruleId): void
    {
        DB::table('admin_email_notification_rules')
            ->where('id', $ruleId)
            ->update([
                'sent_count' => DB::raw('sent_count + 1'),
                'last_sent_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 알림 규칙 생성
     */
    public function createRule(array $data): int
    {
        return DB::table('admin_email_notification_rules')->insertGetId([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'event_type' => $data['event_type'],
            'conditions' => isset($data['conditions']) ? json_encode($data['conditions']) : null,
            'recipient_type' => $data['recipient_type'] ?? 'user',
            'recipients' => isset($data['recipients']) ? json_encode($data['recipients']) : null,
            'notify_user' => $data['notify_user'] ?? true,
            'notify_admins' => $data['notify_admins'] ?? false,
            'template_id' => $data['template_id'] ?? null,
            'template_slug' => $data['template_slug'] ?? null,
            'throttle_minutes' => $data['throttle_minutes'] ?? null,
            'max_per_day' => $data['max_per_day'] ?? null,
            'max_per_hour' => $data['max_per_hour'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'delay_seconds' => $data['delay_seconds'] ?? 0,
            'active_from' => $data['active_from'] ?? null,
            'active_to' => $data['active_to'] ?? null,
            'active_days' => isset($data['active_days']) ? json_encode($data['active_days']) : null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * 멀티채널 알림 발송
     * 
     * 이메일, SMS, 웹훅, 푸시 등 여러 채널로 동시에 알림을 발송합니다.
     * 
     * @param string $eventType 이벤트 타입
     * @param array $data 알림 데이터
     * @param array $channels 발송할 채널 목록
     * @return array 채널별 발송 결과
     */
    public function notifyMultiChannel(string $eventType, array $data, array $channels = []): array
    {
        $results = [
            'email' => false,
            'sms' => false,
            'webhook' => [],
            'push' => false
        ];

        // 채널이 지정되지 않았으면 이벤트 설정에서 조회
        if (empty($channels)) {
            $channels = $this->getEventChannels($eventType);
        }

        // 이메일 발송
        if (in_array('email', $channels)) {
            $results['email'] = $this->notify($eventType, $data);
        }

        // SMS 발송
        if (in_array('sms', $channels) && isset($data['user_id'])) {
            $user = DB::table('users')->where('id', $data['user_id'])->first();
            if ($user && $user->phone_number) {
                $message = $this->formatSmsMessage($eventType, $data);
                $results['sms'] = $this->smsService->send($user->phone_number, $message);
            }
        }

        // 웹훅 발송
        if (in_array('webhook', $channels)) {
            $message = $this->formatWebhookMessage($eventType, $data);
            $results['webhook'] = $this->webhookService->sendByEvent($eventType, $message, $data);
        }

        // 푸시 알림 발송
        if (in_array('push', $channels) && isset($data['user_id'])) {
            $pushData = $this->formatPushData($eventType, $data);
            $results['push'] = $this->pushService->send(
                $data['user_id'],
                $pushData['title'],
                $pushData['message'],
                $pushData['data']
            );
        }

        // 발송 결과 로그
        $this->logMultiChannelNotification($eventType, $data, $results);

        return $results;
    }

    /**
     * 웹훅 채널 설정
     * 
     * @param string $name 채널 이름
     * @param string $type 채널 타입 (slack, discord, teams, custom)
     * @param string $webhookUrl 웹훅 URL
     * @param array $options 추가 옵션
     * @return int 생성된 채널 ID
     */
    public function configureWebhookChannel(string $name, string $type, string $webhookUrl, array $options = []): int
    {
        return $this->webhookService->createChannel([
            'name' => $name,
            'type' => $type,
            'webhook_url' => $webhookUrl,
            'description' => $options['description'] ?? null,
            'custom_headers' => $options['headers'] ?? null,
            'is_active' => $options['active'] ?? true
        ]);
    }

    /**
     * 푸시 알림 구독
     * 
     * @param int $userId 사용자 ID
     * @param string $type 푸시 타입 (web, mobile)
     * @param string $endpoint 엔드포인트/토큰
     * @param array|null $authKeys 인증 키
     * @return int 구독 ID
     */
    public function subscribePush(int $userId, string $type, string $endpoint, ?array $authKeys = null): int
    {
        return $this->pushService->subscribe($userId, $type, $endpoint, $authKeys);
    }

    /**
     * 브로드캐스트 알림
     * 
     * 조건에 맞는 모든 사용자에게 알림을 발송합니다.
     * 
     * @param string $title 제목
     * @param string $message 메시지
     * @param array $channels 발송 채널
     * @param array $conditions 조건 (role, permission 등)
     * @return array 발송 결과
     */
    public function broadcast(string $title, string $message, array $channels = ['push'], array $conditions = []): array
    {
        $results = [
            'push' => 0,
            'email' => 0,
            'webhook' => []
        ];

        // 푸시 브로드캐스트
        if (in_array('push', $channels)) {
            $results['push'] = $this->pushService->broadcast($title, $message, [], $conditions);
        }

        // 이메일 브로드캐스트
        if (in_array('email', $channels)) {
            $users = $this->getUsersByConditions($conditions);
            foreach ($users as $user) {
                if ($this->sendBroadcastEmail($user, $title, $message)) {
                    $results['email']++;
                }
            }
        }

        // 웹훅 브로드캐스트
        if (in_array('webhook', $channels)) {
            $webhookData = [
                'title' => $title,
                'color' => 'info',
                'user_count' => $results['push'] + $results['email']
            ];
            $results['webhook'] = $this->webhookService->sendByEvent('broadcast', $message, $webhookData);
        }

        return $results;
    }

    /**
     * 이벤트별 채널 설정 조회
     * 
     * @param string $eventType 이벤트 타입
     * @return array 채널 목록
     */
    protected function getEventChannels(string $eventType): array
    {
        $channelConfig = DB::table('admin_notification_channels')
            ->where('event_type', $eventType)
            ->where('is_active', true)
            ->pluck('channel')
            ->toArray();

        return !empty($channelConfig) ? $channelConfig : ['email']; // 기본값은 이메일
    }

    /**
     * SMS 메시지 포맷팅
     * 
     * @param string $eventType 이벤트 타입
     * @param array $data 데이터
     * @return string
     */
    protected function formatSmsMessage(string $eventType, array $data): string
    {
        $templates = [
            'login_failed' => '[%s] 로그인 실패 %d회. IP: %s',
            'account_locked' => '[%s] 계정이 잠겼습니다. 해제: %s',
            'password_changed' => '[%s] 비밀번호가 변경되었습니다.',
            'two_fa_enabled' => '[%s] 2단계 인증이 활성화되었습니다.',
            'two_fa_disabled' => '[%s] 2단계 인증이 비활성화되었습니다.'
        ];

        $template = $templates[$eventType] ?? '[%s] 알림: %s';
        
        return sprintf(
            $template,
            config('app.name'),
            ...array_values(array_slice($data, 0, 3))
        );
    }

    /**
     * 웹훅 메시지 포맷팅
     * 
     * @param string $eventType 이벤트 타입
     * @param array $data 데이터
     * @return string
     */
    protected function formatWebhookMessage(string $eventType, array $data): string
    {
        $emoji = [
            'login_failed' => '⚠️',
            'account_locked' => '🔒',
            'password_changed' => '🔑',
            'two_fa_enabled' => '🛡️',
            'two_fa_disabled' => '🚫',
            'ip_blocked' => '🚫',
            'default' => '📢'
        ];

        $icon = $emoji[$eventType] ?? $emoji['default'];
        
        return sprintf(
            "%s **[%s Admin]** %s 이벤트 발생\n",
            $icon,
            config('app.name'),
            str_replace('_', ' ', ucfirst($eventType))
        );
    }

    /**
     * 푸시 데이터 포맷팅
     * 
     * @param string $eventType 이벤트 타입
     * @param array $data 데이터
     * @return array
     */
    protected function formatPushData(string $eventType, array $data): array
    {
        $titles = [
            'login_failed' => '로그인 실패 알림',
            'account_locked' => '계정 잠금 알림',
            'password_changed' => '비밀번호 변경 알림',
            'two_fa_enabled' => '2단계 인증 활성화',
            'two_fa_disabled' => '2단계 인증 비활성화'
        ];

        return [
            'title' => $titles[$eventType] ?? '관리자 알림',
            'message' => $data['message'] ?? '새로운 알림이 있습니다.',
            'data' => [
                'event_type' => $eventType,
                'url' => '/admin/notifications',
                'timestamp' => time()
            ]
        ];
    }

    /**
     * 조건별 사용자 조회
     * 
     * @param array $conditions 조건
     * @return \Illuminate\Support\Collection
     */
    protected function getUsersByConditions(array $conditions)
    {
        $query = DB::table('users');

        if (isset($conditions['role'])) {
            $query->whereIn('role', (array) $conditions['role']);
        }

        if (isset($conditions['is_admin'])) {
            $query->where('is_admin', $conditions['is_admin']);
        }

        if (isset($conditions['permission'])) {
            $query->whereExists(function ($q) use ($conditions) {
                $q->select(DB::raw(1))
                    ->from('user_permissions')
                    ->whereColumn('user_permissions.user_id', 'users.id')
                    ->whereIn('permission', (array) $conditions['permission']);
            });
        }

        return $query->get();
    }

    /**
     * 브로드캐스트 이메일 발송
     * 
     * @param object $user 사용자
     * @param string $title 제목
     * @param string $message 메시지
     * @return bool
     */
    protected function sendBroadcastEmail($user, string $title, string $message): bool
    {
        try {
            Mail::to($user->email)->send(new EmailMailable(
                $title,
                $message,
                config('mail.from.address'),
                config('mail.from.name'),
                $user->email
            ));

            return true;
        } catch (Exception $e) {
            Log::error('Broadcast email failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 멀티채널 알림 로그 기록
     * 
     * @param string $eventType 이벤트 타입
     * @param array $data 데이터
     * @param array $results 발송 결과
     */
    protected function logMultiChannelNotification(string $eventType, array $data, array $results): void
    {
        try {
            DB::table('admin_notification_logs')->insert([
                'event_type' => $eventType,
                'channels' => json_encode(array_keys($results)),
                'results' => json_encode($results),
                'data' => json_encode($data),
                'user_id' => $data['user_id'] ?? null,
                'created_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log multi-channel notification', [
                'event' => $eventType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 웹훅 채널 테스트
     * 
     * @param string $channel 채널 이름
     * @return bool
     */
    public function testWebhookChannel(string $channel): bool
    {
        return $this->webhookService->testChannel($channel);
    }

    /**
     * 푸시 알림 통계 조회
     * 
     * @param int|null $userId 사용자 ID
     * @param string|null $type 푸시 타입
     * @param \DateTime|null $from 시작일
     * @param \DateTime|null $to 종료일
     * @return array
     */
    public function getPushStatistics(?int $userId = null, ?string $type = null, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        return $this->pushService->getStatistics($userId, $type, $from, $to);
    }
}
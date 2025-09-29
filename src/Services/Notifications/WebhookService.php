<?php

namespace Jiny\Admin\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * 웹훅 알림 서비스
 * 
 * Slack, Discord, Teams 등 다양한 웹훅 서비스로 알림을 발송합니다.
 * 
 * @package Jiny\Admin
 * @since 1.0.0
 */
class WebhookService
{
    /**
     * 지원하는 웹훅 타입
     */
    const TYPE_SLACK = 'slack';
    const TYPE_DISCORD = 'discord';
    const TYPE_TEAMS = 'teams';
    const TYPE_CUSTOM = 'custom';

    /**
     * 웹훅 설정 캐시 키
     */
    const CACHE_KEY = 'admin_webhook_channels';
    const CACHE_TTL = 3600; // 1시간

    /**
     * 활성화된 웹훅 채널 목록
     * 
     * @var array
     */
    protected $channels = [];

    /**
     * 생성자
     */
    public function __construct()
    {
        // Webhook이 활성화되어 있을 때만 채널 로드
        if (config('admin.setting.webhook.enabled', false)) {
            $this->loadChannels();
        }
    }

    /**
     * 웹훅 채널 로드
     */
    protected function loadChannels(): void
    {
        $this->channels = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return DB::table('admin_webhook_channels')
                ->where('is_active', true)
                ->get()
                ->keyBy('name')
                ->toArray();
        });
    }

    /**
     * 웹훅으로 알림 발송
     * 
     * @param string $channel 채널 이름
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return bool
     */
    public function send(string $channel, string $message, array $data = []): bool
    {
        // Webhook이 비활성화되어 있으면 false 반환
        if (!config('admin.setting.webhook.enabled', false)) {
            return false;
        }

        try {
            if (!isset($this->channels[$channel])) {
                Log::warning("Webhook channel not found: {$channel}");
                return false;
            }

            $channelConfig = $this->channels[$channel];
            
            // 채널 타입에 따라 포맷팅
            $payload = $this->formatPayload($channelConfig->type, $message, $data);
            
            // 웹훅 발송
            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders($channelConfig))
                ->post($channelConfig->webhook_url, $payload);

            // 로그 기록
            $this->logWebhook($channel, $message, $response->successful());

            if (!$response->successful()) {
                Log::error("Webhook failed for channel {$channel}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return $response->successful();

        } catch (Exception $e) {
            Log::error("Webhook error for channel {$channel}: " . $e->getMessage());
            $this->logWebhook($channel, $message, false, $e->getMessage());
            return false;
        }
    }

    /**
     * 여러 채널로 동시 발송
     * 
     * @param array $channels 채널 목록
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array 발송 결과
     */
    public function sendToMultiple(array $channels, string $message, array $data = []): array
    {
        $results = [];
        
        foreach ($channels as $channel) {
            $results[$channel] = $this->send($channel, $message, $data);
        }
        
        return $results;
    }

    /**
     * 이벤트 타입별 자동 발송
     * 
     * @param string $eventType 이벤트 타입
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array 발송 결과
     */
    public function sendByEvent(string $eventType, string $message, array $data = []): array
    {
        // 이벤트에 등록된 채널 조회
        $channels = DB::table('admin_webhook_subscriptions')
            ->where('event_type', $eventType)
            ->where('is_active', true)
            ->pluck('channel_name')
            ->toArray();
        
        if (empty($channels)) {
            return [];
        }
        
        return $this->sendToMultiple($channels, $message, $data);
    }

    /**
     * Slack 메시지 포맷
     * 
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array
     */
    protected function formatSlackPayload(string $message, array $data): array
    {
        $payload = [
            'text' => $message,
            'username' => config('app.name') . ' Admin',
            'icon_emoji' => ':bell:',
        ];

        // 첨부 파일 (추가 정보)
        if (!empty($data)) {
            $payload['attachments'] = [[
                'color' => $data['color'] ?? 'info',
                'fields' => $this->formatFields($data),
                'footer' => config('app.name'),
                'ts' => time()
            ]];
        }

        // 멘션 추가
        if (isset($data['mention'])) {
            $payload['text'] = $data['mention'] . ' ' . $payload['text'];
        }

        return $payload;
    }

    /**
     * Discord 메시지 포맷
     * 
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array
     */
    protected function formatDiscordPayload(string $message, array $data): array
    {
        $payload = [
            'content' => $message,
            'username' => config('app.name') . ' Admin',
        ];

        // Embed 추가
        if (!empty($data)) {
            $embed = [
                'title' => $data['title'] ?? 'Admin Notification',
                'description' => $data['description'] ?? null,
                'color' => $this->getDiscordColor($data['color'] ?? 'info'),
                'fields' => [],
                'footer' => [
                    'text' => config('app.name')
                ],
                'timestamp' => date('c')
            ];

            foreach ($data as $key => $value) {
                if (!in_array($key, ['title', 'description', 'color', 'mention'])) {
                    $embed['fields'][] = [
                        'name' => ucfirst(str_replace('_', ' ', $key)),
                        'value' => (string) $value,
                        'inline' => true
                    ];
                }
            }

            $payload['embeds'] = [$embed];
        }

        // 멘션 추가
        if (isset($data['mention'])) {
            $payload['content'] = $data['mention'] . ' ' . $payload['content'];
        }

        return $payload;
    }

    /**
     * Teams 메시지 포맷
     * 
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array
     */
    protected function formatTeamsPayload(string $message, array $data): array
    {
        $payload = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'themeColor' => $this->getTeamsColor($data['color'] ?? 'info'),
            'summary' => $message,
            'sections' => [
                [
                    'activityTitle' => config('app.name') . ' Admin',
                    'activitySubtitle' => $data['title'] ?? 'Notification',
                    'activityImage' => 'https://api.dicebear.com/7.x/bottts/svg?seed=' . config('app.name'),
                    'text' => $message,
                    'facts' => $this->formatFacts($data)
                ]
            ]
        ];

        // 액션 버튼 추가
        if (isset($data['action_url'])) {
            $payload['potentialAction'] = [
                [
                    '@type' => 'OpenUri',
                    'name' => $data['action_text'] ?? 'View Details',
                    'targets' => [
                        ['os' => 'default', 'uri' => $data['action_url']]
                    ]
                ]
            ];
        }

        return $payload;
    }

    /**
     * 커스텀 웹훅 포맷
     * 
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array
     */
    protected function formatCustomPayload(string $message, array $data): array
    {
        return array_merge([
            'message' => $message,
            'source' => config('app.name') . ' Admin',
            'timestamp' => now()->toIso8601String(),
        ], $data);
    }

    /**
     * 페이로드 포맷팅
     * 
     * @param string $type 웹훅 타입
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return array
     */
    protected function formatPayload(string $type, string $message, array $data): array
    {
        switch ($type) {
            case self::TYPE_SLACK:
                return $this->formatSlackPayload($message, $data);
            
            case self::TYPE_DISCORD:
                return $this->formatDiscordPayload($message, $data);
            
            case self::TYPE_TEAMS:
                return $this->formatTeamsPayload($message, $data);
            
            case self::TYPE_CUSTOM:
            default:
                return $this->formatCustomPayload($message, $data);
        }
    }

    /**
     * 헤더 설정
     * 
     * @param object $channel 채널 설정
     * @return array
     */
    protected function getHeaders($channel): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => config('app.name') . ' Admin/1.0'
        ];

        // 추가 헤더가 있으면 적용
        if (isset($channel->headers) && $channel->headers) {
            $customHeaders = json_decode($channel->headers, true);
            if (is_array($customHeaders)) {
                $headers = array_merge($headers, $customHeaders);
            }
        }

        return $headers;
    }

    /**
     * 필드 포맷팅 (Slack용)
     * 
     * @param array $data 데이터
     * @return array
     */
    protected function formatFields(array $data): array
    {
        $fields = [];
        
        foreach ($data as $key => $value) {
            if (!in_array($key, ['color', 'mention', 'title', 'description'])) {
                $fields[] = [
                    'title' => ucfirst(str_replace('_', ' ', $key)),
                    'value' => (string) $value,
                    'short' => strlen($value) < 40
                ];
            }
        }
        
        return $fields;
    }

    /**
     * Facts 포맷팅 (Teams용)
     * 
     * @param array $data 데이터
     * @return array
     */
    protected function formatFacts(array $data): array
    {
        $facts = [];
        
        foreach ($data as $key => $value) {
            if (!in_array($key, ['color', 'mention', 'title', 'action_url', 'action_text'])) {
                $facts[] = [
                    'name' => ucfirst(str_replace('_', ' ', $key)) . ':',
                    'value' => (string) $value
                ];
            }
        }
        
        return $facts;
    }

    /**
     * Discord 색상 코드 가져오기
     * 
     * @param string $color 색상 이름
     * @return int
     */
    protected function getDiscordColor(string $color): int
    {
        $colors = [
            'danger' => 0xDC3545,
            'warning' => 0xFFC107,
            'info' => 0x17A2B8,
            'success' => 0x28A745,
            'primary' => 0x007BFF,
            'secondary' => 0x6C757D
        ];
        
        return $colors[$color] ?? 0x6C757D;
    }

    /**
     * Teams 색상 코드 가져오기
     * 
     * @param string $color 색상 이름
     * @return string
     */
    protected function getTeamsColor(string $color): string
    {
        $colors = [
            'danger' => 'DC3545',
            'warning' => 'FFC107',
            'info' => '17A2B8',
            'success' => '28A745',
            'primary' => '007BFF',
            'secondary' => '6C757D'
        ];
        
        return $colors[$color] ?? '6C757D';
    }

    /**
     * 웹훅 발송 로그 기록
     * 
     * @param string $channel 채널 이름
     * @param string $message 메시지
     * @param bool $success 성공 여부
     * @param string|null $error 에러 메시지
     */
    protected function logWebhook(string $channel, string $message, bool $success, ?string $error = null): void
    {
        try {
            DB::table('admin_webhook_logs')->insert([
                'channel_name' => $channel,
                'message' => $message,
                'status' => $success ? 'sent' : 'failed',
                'error_message' => $error,
                'sent_at' => now(),
                'created_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error("Failed to log webhook: " . $e->getMessage());
        }
    }

    /**
     * 웹훅 채널 생성
     * 
     * @param array $data 채널 데이터
     * @return int 생성된 채널 ID
     */
    public function createChannel(array $data): int
    {
        $id = DB::table('admin_webhook_channels')->insertGetId([
            'name' => $data['name'],
            'type' => $data['type'],
            'webhook_url' => $data['webhook_url'],
            'description' => $data['description'] ?? null,
            'headers' => isset($data['headers']) ? json_encode($data['headers']) : (isset($data['custom_headers']) ? json_encode($data['custom_headers']) : null),
            'config' => isset($data['config']) ? json_encode($data['config']) : null,
            'priority' => $data['priority'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->clearCache();
        
        return $id;
    }

    /**
     * 웹훅 채널 업데이트
     * 
     * @param int $id 채널 ID
     * @param array $data 업데이트 데이터
     * @return bool
     */
    public function updateChannel(int $id, array $data): bool
    {
        $updateData = ['updated_at' => now()];
        
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['type'])) $updateData['type'] = $data['type'];
        if (isset($data['webhook_url'])) $updateData['webhook_url'] = $data['webhook_url'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['headers'])) $updateData['headers'] = json_encode($data['headers']);
        if (isset($data['custom_headers'])) $updateData['headers'] = json_encode($data['custom_headers']); // backward compatibility
        if (isset($data['config'])) $updateData['config'] = json_encode($data['config']);
        if (isset($data['priority'])) $updateData['priority'] = $data['priority'];
        if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];

        $result = DB::table('admin_webhook_channels')
            ->where('id', $id)
            ->update($updateData);

        if ($result) {
            $this->clearCache();
        }

        return $result > 0;
    }

    /**
     * 웹훅 채널 삭제
     * 
     * @param int $id 채널 ID
     * @return bool
     */
    public function deleteChannel(int $id): bool
    {
        $result = DB::table('admin_webhook_channels')
            ->where('id', $id)
            ->delete();

        if ($result) {
            $this->clearCache();
        }

        return $result > 0;
    }

    /**
     * 웹훅 테스트
     * 
     * @param string $channel 채널 이름
     * @return bool
     */
    public function testChannel(string $channel): bool
    {
        $testMessage = sprintf(
            "🔔 %s Admin 웹훅 테스트 메시지입니다.\n시간: %s",
            config('app.name'),
            now()->format('Y-m-d H:i:s')
        );
        
        $testData = [
            'color' => 'info',
            'title' => 'Webhook Test',
            'test_time' => now()->toDateTimeString(),
            'server' => request()->getHost()
        ];
        
        return $this->send($channel, $testMessage, $testData);
    }

    /**
     * 캐시 클리어
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->loadChannels();
    }

    /**
     * 이벤트 구독 설정
     * 
     * @param string $channel 채널 이름
     * @param string $eventType 이벤트 타입
     * @param bool $subscribe 구독 여부
     * @return bool
     */
    public function setEventSubscription(string $channel, string $eventType, bool $subscribe = true): bool
    {
        if ($subscribe) {
            // 구독 추가
            return DB::table('admin_webhook_subscriptions')->insertOrIgnore([
                'channel_name' => $channel,
                'event_type' => $eventType,
                'is_active' => true,
                'created_at' => now()
            ]) > 0;
        } else {
            // 구독 제거
            return DB::table('admin_webhook_subscriptions')
                ->where('channel_name', $channel)
                ->where('event_type', $eventType)
                ->delete() > 0;
        }
    }
}
<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\NotificationService;
use Jiny\Admin\Services\Notifications\WebhookService;
use Jiny\Admin\Services\Notifications\PushService;

/**
 * 알림 설정 관리 컨트롤러
 * 
 * 웹훅, 푸시 알림 등 다양한 알림 채널을 설정하고 관리합니다.
 * 
 * @package Jiny\Admin
 * @since 1.0.0
 */
class AdminNotificationSettings extends Controller
{
    protected $notificationService;
    protected $webhookService;
    protected $pushService;

    public function __construct(
        NotificationService $notificationService,
        WebhookService $webhookService,
        PushService $pushService
    ) {
        $this->middleware('admin');
        $this->notificationService = $notificationService;
        $this->webhookService = $webhookService;
        $this->pushService = $pushService;
    }

    /**
     * 알림 설정 대시보드
     */
    public function index()
    {
        // 웹훅 채널 목록
        $webhookChannels = DB::table('admin_webhook_channels')
            ->orderBy('name')
            ->get();

        // 이벤트 채널 설정
        $eventChannels = DB::table('admin_notification_channels')
            ->select('event_type', DB::raw('GROUP_CONCAT(channel) as channels'))
            ->where('is_active', true)
            ->groupBy('event_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->event_type => explode(',', $item->channels)];
            });

        // 푸시 구독 통계
        $pushStats = DB::table('admin_push_subscriptions')
            ->select('type', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('type')
            ->pluck('count', 'type');

        // 최근 알림 로그
        $recentLogs = DB::table('admin_notification_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('jiny.admin::admin.notification-settings.index', compact(
            'webhookChannels',
            'eventChannels',
            'pushStats',
            'recentLogs'
        ));
    }

    /**
     * 웹훅 채널 목록
     */
    public function webhooks()
    {
        $channels = DB::table('admin_webhook_channels')
            ->orderBy('name')
            ->paginate(20);

        return view('jiny.admin::admin.notification-settings.webhooks', compact('channels'));
    }

    /**
     * 웹훅 채널 생성 폼
     */
    public function createWebhook()
    {
        return view('jiny.admin::admin.notification-settings.webhook-create');
    }

    /**
     * 웹훅 채널 저장
     */
    public function storeWebhook(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:admin_webhook_channels',
            'type' => 'required|in:slack,discord,teams,custom',
            'webhook_url' => 'required|url',
            'description' => 'nullable|string',
            'custom_headers' => 'nullable|json'
        ]);

        $channelId = $this->webhookService->createChannel($validated);

        return redirect()->route('admin.system.notifications.webhooks')
            ->with('success', '웹훅 채널이 생성되었습니다.');
    }

    /**
     * 웹훅 채널 수정 폼
     */
    public function editWebhook($id)
    {
        $channel = DB::table('admin_webhook_channels')->find($id);
        
        if (!$channel) {
            abort(404);
        }

        return view('jiny.admin::admin.notification-settings.webhook-edit', compact('channel'));
    }

    /**
     * 웹훅 채널 업데이트
     */
    public function updateWebhook(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:admin_webhook_channels,name,' . $id,
            'type' => 'required|in:slack,discord,teams,custom',
            'webhook_url' => 'required|url',
            'description' => 'nullable|string',
            'custom_headers' => 'nullable|json',
            'is_active' => 'boolean'
        ]);

        $this->webhookService->updateChannel($id, $validated);

        return redirect()->route('admin.system.notifications.webhooks')
            ->with('success', '웹훅 채널이 업데이트되었습니다.');
    }

    /**
     * 웹훅 채널 삭제
     */
    public function deleteWebhook($id)
    {
        $this->webhookService->deleteChannel($id);

        return redirect()->route('admin.system.notifications.webhooks')
            ->with('success', '웹훅 채널이 삭제되었습니다.');
    }

    /**
     * 웹훅 테스트
     */
    public function testWebhook(Request $request, $id)
    {
        $channel = DB::table('admin_webhook_channels')->find($id);
        
        if (!$channel) {
            return response()->json(['error' => '채널을 찾을 수 없습니다.'], 404);
        }

        $success = $this->webhookService->testChannel($channel->name);

        return response()->json([
            'success' => $success,
            'message' => $success ? '테스트 메시지가 발송되었습니다.' : '테스트 발송에 실패했습니다.'
        ]);
    }

    /**
     * 이벤트 채널 설정
     */
    public function eventChannels()
    {
        // 사용 가능한 이벤트 타입
        $eventTypes = [
            'login_failed' => '로그인 실패',
            'account_locked' => '계정 잠금',
            'password_changed' => '비밀번호 변경',
            'two_fa_enabled' => '2FA 활성화',
            'two_fa_disabled' => '2FA 비활성화',
            'ip_blocked' => 'IP 차단',
            'broadcast' => '브로드캐스트'
        ];

        // 사용 가능한 채널
        $availableChannels = ['email', 'sms', 'webhook', 'push'];

        // 현재 설정
        $currentSettings = DB::table('admin_notification_channels')
            ->get()
            ->groupBy('event_type')
            ->map(function ($channels) {
                return $channels->where('is_active', true)->pluck('channel')->toArray();
            });

        return view('jiny.admin::admin.notification-settings.event-channels', compact(
            'eventTypes',
            'availableChannels',
            'currentSettings'
        ));
    }

    /**
     * 이벤트 채널 저장
     */
    public function saveEventChannels(Request $request)
    {
        $validated = $request->validate([
            'channels' => 'required|array',
            'channels.*' => 'array'
        ]);

        // 기존 설정 삭제
        DB::table('admin_notification_channels')->truncate();

        // 새 설정 저장
        foreach ($validated['channels'] as $eventType => $channels) {
            foreach ($channels as $channel) {
                DB::table('admin_notification_channels')->insert([
                    'event_type' => $eventType,
                    'channel' => $channel,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return redirect()->back()->with('success', '이벤트 채널 설정이 저장되었습니다.');
    }

    /**
     * 푸시 알림 설정
     */
    public function pushSettings()
    {
        // VAPID 키 확인
        $vapidPublicKey = config('admin.push.vapid.public_key');
        $fcmServerKey = config('admin.push.fcm.server_key');

        // 푸시 구독 통계
        $subscriptions = DB::table('admin_push_subscriptions')
            ->select('type', 'is_active', DB::raw('count(*) as count'))
            ->groupBy('type', 'is_active')
            ->get();

        // 최근 푸시 로그
        $recentPushes = DB::table('admin_push_logs')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('jiny.admin::admin.notification-settings.push', compact(
            'vapidPublicKey',
            'fcmServerKey',
            'subscriptions',
            'recentPushes'
        ));
    }

    /**
     * VAPID 키 생성
     */
    public function generateVapidKeys()
    {
        $keys = $this->pushService->generateVapidKeys();

        return response()->json([
            'public_key' => $keys['public_key'],
            'private_key' => $keys['private_key'],
            'message' => 'VAPID 키가 생성되었습니다. .env 파일에 저장하세요.'
        ]);
    }

    /**
     * 브로드캐스트 알림 발송
     */
    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'channels' => 'required|array',
            'channels.*' => 'in:email,push,webhook',
            'conditions' => 'nullable|array'
        ]);

        $results = $this->notificationService->broadcast(
            $validated['title'],
            $validated['message'],
            $validated['channels'],
            $validated['conditions'] ?? []
        );

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => sprintf(
                '알림이 발송되었습니다. (이메일: %d, 푸시: %d)',
                $results['email'],
                $results['push']
            )
        ]);
    }

    /**
     * 알림 통계
     */
    public function statistics(Request $request)
    {
        $from = $request->input('from', now()->subDays(30));
        $to = $request->input('to', now());

        // 채널별 발송 통계
        $channelStats = DB::table('admin_notification_logs')
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw("JSON_EXTRACT(channels, '$[*]') as channel"), DB::raw('count(*) as count'))
            ->groupBy('channel')
            ->get();

        // 이벤트별 발송 통계
        $eventStats = DB::table('admin_notification_logs')
            ->whereBetween('created_at', [$from, $to])
            ->select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->get();

        // 일별 발송 추이
        $dailyStats = DB::table('admin_notification_logs')
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('jiny.admin::admin.notification-settings.statistics', compact(
            'channelStats',
            'eventStats',
            'dailyStats',
            'from',
            'to'
        ));
    }

    /**
     * 웹훅 이벤트 구독 설정
     */
    public function webhookSubscriptions($channelName)
    {
        $channel = DB::table('admin_webhook_channels')
            ->where('name', $channelName)
            ->first();

        if (!$channel) {
            abort(404);
        }

        // 사용 가능한 이벤트 타입
        $eventTypes = [
            'login_failed',
            'account_locked',
            'password_changed',
            'two_fa_enabled',
            'two_fa_disabled',
            'ip_blocked',
            'broadcast'
        ];

        // 현재 구독 중인 이벤트
        $subscriptions = DB::table('admin_webhook_subscriptions')
            ->where('channel_name', $channelName)
            ->where('is_active', true)
            ->pluck('event_type')
            ->toArray();

        return view('jiny.admin::admin.notification-settings.webhook-subscriptions', compact(
            'channel',
            'eventTypes',
            'subscriptions'
        ));
    }

    /**
     * 웹훅 이벤트 구독 저장
     */
    public function saveWebhookSubscriptions(Request $request, $channelName)
    {
        $validated = $request->validate([
            'events' => 'required|array',
            'events.*' => 'string'
        ]);

        // 기존 구독 제거
        DB::table('admin_webhook_subscriptions')
            ->where('channel_name', $channelName)
            ->delete();

        // 새 구독 추가
        foreach ($validated['events'] as $event) {
            $this->webhookService->setEventSubscription($channelName, $event, true);
        }

        return redirect()->back()->with('success', '이벤트 구독이 업데이트되었습니다.');
    }
}
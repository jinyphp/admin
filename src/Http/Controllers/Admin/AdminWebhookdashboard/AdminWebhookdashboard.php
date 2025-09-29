<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminWebhookdashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Jiny\Admin\Services\JsonConfigService;
use Carbon\Carbon;

/**
 * Webhook 대시보드 컨트롤러
 *
 * 웹훅 시스템의 통계, 상태, 활동 내역을 종합적으로 표시합니다.
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminWebhookdashboard
 * @since   1.0.0
 */
class AdminWebhookdashboard extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     */
    public function __construct()
    {
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Webhook 대시보드 페이지 표시
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // 통계 데이터 수집
        $stats = $this->collectStats();
        $recentLogs = $this->getRecentLogs();
        $channels = $this->getChannelStatus();
        $dailyStats = $this->getDailyStats();
        $channelPerformance = $this->getChannelPerformance();

        // JSON 데이터 확인
        if (!$this->jsonData) {
            $this->jsonData = [
                'template' => [
                    'index' => 'jiny-admin::admin.admin_webhookdashboard.table',
                    'layout' => 'jiny-admin::layouts.admin'
                ]
            ];
        }

        // Template index 확인
        if (!isset($this->jsonData['template']['index'])) {
            $this->jsonData['template']['index'] = 'jiny-admin::admin.admin_webhookdashboard.table';
        }

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'stats' => $stats,
            'recentLogs' => $recentLogs,
            'channels' => $channels,
            'dailyStats' => $dailyStats,
            'channelPerformance' => $channelPerformance,
            'controllerClass' => static::class,
        ]);
    }

    /**
     * 웹훅 통계 수집
     */
    private function collectStats()
    {
        $cacheKey = 'webhook_dashboard_stats';
        
        return Cache::remember($cacheKey, 300, function () {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $startOfToday = $now->copy()->startOfDay();
            $last24Hours = $now->copy()->subHours(24);

            // 전체 통계
            $totalChannels = DB::table('admin_webhook_channels')->count();
            $activeChannels = DB::table('admin_webhook_channels')
                ->where('is_active', true)
                ->count();

            // 로그 테이블이 없을 수도 있으므로 체크
            $hasLogsTable = DB::getSchemaBuilder()->hasTable('admin_webhook_logs');
            
            if ($hasLogsTable) {
                $totalSent = DB::table('admin_webhook_logs')->count();
                $totalSuccess = DB::table('admin_webhook_logs')
                    ->where('status', 'sent')
                    ->count();
                $totalFailed = DB::table('admin_webhook_logs')
                    ->where('status', 'failed')
                    ->count();

                // 오늘 통계
                $todaySent = DB::table('admin_webhook_logs')
                    ->where('created_at', '>=', $startOfToday)
                    ->count();
                $todaySuccess = DB::table('admin_webhook_logs')
                    ->where('created_at', '>=', $startOfToday)
                    ->where('status', 'sent')
                    ->count();
                $todayFailed = DB::table('admin_webhook_logs')
                    ->where('created_at', '>=', $startOfToday)
                    ->where('status', 'failed')
                    ->count();

                // 24시간 통계
                $last24HoursSent = DB::table('admin_webhook_logs')
                    ->where('created_at', '>=', $last24Hours)
                    ->count();

                // 이번 달 통계
                $monthSent = DB::table('admin_webhook_logs')
                    ->where('created_at', '>=', $startOfMonth)
                    ->count();
            } else {
                $totalSent = 0;
                $totalSuccess = 0;
                $totalFailed = 0;
                $todaySent = 0;
                $todaySuccess = 0;
                $todayFailed = 0;
                $last24HoursSent = 0;
                $monthSent = 0;
            }

            // 성공률 계산
            $successRate = $totalSent > 0 ? round(($totalSuccess / $totalSent) * 100, 2) : 0;
            $todaySuccessRate = $todaySent > 0 ? round(($todaySuccess / $todaySent) * 100, 2) : 0;

            return [
                'total_channels' => $totalChannels,
                'active_channels' => $activeChannels,
                'inactive_channels' => $totalChannels - $activeChannels,
                'total_sent' => $totalSent,
                'total_success' => $totalSuccess,
                'total_failed' => $totalFailed,
                'success_rate' => $successRate,
                'today_sent' => $todaySent,
                'today_success' => $todaySuccess,
                'today_failed' => $todayFailed,
                'today_success_rate' => $todaySuccessRate,
                'last_24_hours' => $last24HoursSent,
                'month_sent' => $monthSent,
            ];
        });
    }

    /**
     * 최근 웹훅 로그 조회
     */
    private function getRecentLogs()
    {
        if (!DB::getSchemaBuilder()->hasTable('admin_webhook_logs')) {
            return collect([]);
        }

        return DB::table('admin_webhook_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                $log->created_at_formatted = Carbon::parse($log->created_at)->diffForHumans();
                $log->status_class = $log->status === 'sent' ? 'success' : 'danger';
                return $log;
            });
    }

    /**
     * 채널 상태 조회
     */
    private function getChannelStatus()
    {
        // admin_webhook_logs 테이블 존재 여부 확인
        $hasLogsTable = DB::getSchemaBuilder()->hasTable('admin_webhook_logs');
        
        $query = DB::table('admin_webhook_channels')
            ->select('admin_webhook_channels.*');
        
        if ($hasLogsTable) {
            $query->selectRaw('(SELECT COUNT(*) FROM admin_webhook_logs WHERE admin_webhook_logs.channel_name = admin_webhook_channels.name) as total_sent')
                  ->selectRaw("(SELECT COUNT(*) FROM admin_webhook_logs WHERE admin_webhook_logs.channel_name = admin_webhook_channels.name AND status = 'sent') as success_count")
                  ->selectRaw("(SELECT COUNT(*) FROM admin_webhook_logs WHERE admin_webhook_logs.channel_name = admin_webhook_channels.name AND status = 'failed') as failed_count")
                  ->selectRaw('(SELECT MAX(created_at) FROM admin_webhook_logs WHERE admin_webhook_logs.channel_name = admin_webhook_channels.name) as last_used');
        } else {
            $query->selectRaw('0 as total_sent')
                  ->selectRaw('0 as success_count')
                  ->selectRaw('0 as failed_count')
                  ->selectRaw('NULL as last_used');
        }
        
        return $query->orderBy('priority', 'asc')
            ->get()
            ->map(function ($channel) {
                $channel->success_rate = $channel->total_sent > 0 
                    ? round(($channel->success_count / $channel->total_sent) * 100, 2) 
                    : 0;
                $channel->last_used_formatted = $channel->last_used 
                    ? Carbon::parse($channel->last_used)->diffForHumans() 
                    : 'Never';
                $channel->config = json_decode($channel->config, true);
                $channel->headers = json_decode($channel->headers, true);
                return $channel;
            });
    }

    /**
     * 일별 통계 (최근 7일)
     */
    private function getDailyStats()
    {
        if (!DB::getSchemaBuilder()->hasTable('admin_webhook_logs')) {
            return [];
        }

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[$date] = [
                'date' => Carbon::parse($date)->format('M d'),
                'sent' => 0,
                'failed' => 0,
            ];
        }

        $stats = DB::table('admin_webhook_logs')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent')
            ->selectRaw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->get();

        foreach ($stats as $stat) {
            if (isset($days[$stat->date])) {
                $days[$stat->date]['sent'] = $stat->sent;
                $days[$stat->date]['failed'] = $stat->failed;
            }
        }

        return array_values($days);
    }

    /**
     * 채널별 성능 통계
     */
    private function getChannelPerformance()
    {
        if (!DB::getSchemaBuilder()->hasTable('admin_webhook_logs')) {
            return [];
        }

        return DB::table('admin_webhook_logs')
            ->select('channel_name')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as success')
            ->selectRaw('AVG(CASE WHEN status = "sent" THEN 1 ELSE 0 END) * 100 as success_rate')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('channel_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    /**
     * 웹훅 테스트 발송
     */
    public function testWebhook(Request $request, $channelId)
    {
        $channel = DB::table('admin_webhook_channels')->find($channelId);
        
        if (!$channel) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        // WebhookService를 사용하여 테스트
        $webhookService = new \Jiny\Admin\App\Services\Notifications\WebhookService();
        $result = $webhookService->testChannel($channel->name);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Test webhook sent successfully' : 'Failed to send test webhook'
        ]);
    }
}
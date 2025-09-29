<?php
namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsDashboardController extends Controller
{
    public function index()
    {
        // SMS 통계 데이터 수집
        $stats = [
            'total_sent' => DB::table('admin_sms_send')->count(),
            'sent_today' => DB::table('admin_sms_send')
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'sent_this_week' => DB::table('admin_sms_send')
                ->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->count(),
            'sent_this_month' => DB::table('admin_sms_send')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'success_rate' => $this->calculateSuccessRate(),
            'pending_queue' => DB::table('admin_sms_queue')
                ->where('status', 'pending')
                ->count(),
            'failed_queue' => DB::table('admin_sms_queue')
                ->where('status', 'failed')
                ->count(),
            'providers_count' => DB::table('admin_sms_provider')
                ->where('active', true)
                ->count(),
        ];

        // 최근 SMS 발송 기록
        $recentSms = DB::table('admin_sms_send')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 일별 발송 통계 (최근 7일)
        $dailyStats = $this->getDailyStats();

        // 프로바이더별 통계
        $providerStats = $this->getProviderStats();

        // 큐 상태 통계
        $queueStats = DB::table('admin_sms_queue')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return view('jiny.admin.sms.dashboard', compact(
            'stats',
            'recentSms',
            'dailyStats',
            'providerStats',
            'queueStats'
        ));
    }

    private function calculateSuccessRate()
    {
        $total = DB::table('admin_sms_send')->count();
        if ($total == 0) return 0;

        $success = DB::table('admin_sms_send')
            ->where('status', 'success')
            ->count();

        return round(($success / $total) * 100, 2);
    }

    private function getDailyStats()
    {
        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = DB::table('admin_sms_send')
                ->whereDate('created_at', $date)
                ->count();
            
            $stats[] = [
                'date' => $date->format('m/d'),
                'count' => $count
            ];
        }
        return $stats;
    }

    private function getProviderStats()
    {
        return DB::table('admin_sms_send')
            ->select('provider', DB::raw('count(*) as count'), DB::raw('avg(cost) as avg_cost'))
            ->groupBy('provider')
            ->orderBy('count', 'desc')
            ->get();
    }
}
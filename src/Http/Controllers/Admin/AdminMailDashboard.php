<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Models\AdminEmailLog;
use Jiny\Admin\Models\AdminEmailTemplate;
use Carbon\Carbon;

class AdminMailDashboard extends Controller
{
    public function __invoke(Request $request)
    {
        // 통계 데이터 수집
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        
        // 오늘 발송 통계
        $todayStats = AdminEmailLog::whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN click_count > 0 THEN 1 ELSE 0 END) as clicked
            ')
            ->first();
        
        // 이번주 발송 통계
        $weekStats = AdminEmailLog::where('created_at', '>=', $thisWeek)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
            ')
            ->first();
        
        // 이번달 발송 통계
        $monthStats = AdminEmailLog::where('created_at', '>=', $thisMonth)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
            ')
            ->first();
        
        // 최근 7일간 일별 발송 추이
        $dailyTrend = AdminEmailLog::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
            ')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 템플릿별 사용 통계 (상위 5개)
        $templateStats = AdminEmailLog::select('template_id')
            ->selectRaw('COUNT(*) as usage_count')
            ->whereNotNull('template_id')
            ->where('created_at', '>=', $thisMonth)
            ->groupBy('template_id')
            ->orderByDesc('usage_count')
            ->limit(5)
            ->with('template:id,name')
            ->get();
        
        // 최근 발송 로그 (10개)
        $recentLogs = AdminEmailLog::with('template:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 대기중인 이메일 수
        $pendingCount = AdminEmailLog::where('status', 'pending')->count();
        
        // 실패한 이메일 수 (최근 24시간)
        $recentFailures = AdminEmailLog::where('status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();
        
        // 활성 템플릿 수
        $activeTemplates = AdminEmailTemplate::where('is_active', true)->count();
        
        // 열람률 통계 (최근 30일)
        $openRateStats = AdminEmailLog::where('status', 'sent')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('
                COUNT(*) as total_sent,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as total_opened,
                ROUND(SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as open_rate,
                SUM(CASE WHEN click_count > 0 THEN 1 ELSE 0 END) as total_clicked,
                ROUND(SUM(CASE WHEN click_count > 0 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as click_rate
            ')
            ->first();
        
        return view('jiny-admin::admin.mail_dashboard', compact(
            'todayStats',
            'weekStats',
            'monthStats',
            'dailyTrend',
            'templateStats',
            'recentLogs',
            'pendingCount',
            'recentFailures',
            'activeTemplates',
            'openRateStats'
        ));
    }
}
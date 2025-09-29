<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Admin\Models\AdminUserLog;
use Illuminate\Support\Facades\DB;

class CaptchaLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:captcha-logs 
                            {--days=7 : 최근 N일간의 로그 조회}
                            {--type= : 로그 타입 필터 (success, failed, missing)}
                            {--email= : 특정 이메일 필터}
                            {--ip= : 특정 IP 필터}
                            {--export= : CSV 파일로 내보내기}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CAPTCHA 로그를 조회하고 분석합니다';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CAPTCHA 로그 분석 ===');
        $this->newLine();
        
        $days = $this->option('days');
        $type = $this->option('type');
        $email = $this->option('email');
        $ip = $this->option('ip');
        
        // 쿼리 빌드
        $query = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->where('created_at', '>=', now()->subDays($days));
        
        // 필터 적용
        if ($type) {
            $query->where('action', 'captcha_' . $type);
        }
        
        if ($email) {
            $query->where('email', $email);
        }
        
        if ($ip) {
            $query->whereJsonContains('details->ip_address', $ip);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        if ($logs->isEmpty()) {
            $this->warn('조건에 맞는 CAPTCHA 로그가 없습니다.');
            return 0;
        }
        
        // CSV 내보내기
        if ($exportPath = $this->option('export')) {
            $this->exportToCsv($logs, $exportPath);
            return 0;
        }
        
        // 통계 표시
        $this->displayStatistics($logs, $days);
        
        // 최근 로그 표시
        $this->displayRecentLogs($logs->take(10));
        
        // IP별 통계
        $this->displayIpStatistics($logs);
        
        // 시간대별 분석
        $this->displayHourlyAnalysis($logs);
        
        return 0;
    }
    
    /**
     * 통계 표시
     */
    private function displayStatistics($logs, $days)
    {
        $total = $logs->count();
        $success = $logs->where('action', 'captcha_success')->count();
        $failed = $logs->where('action', 'captcha_failed')->count();
        $missing = $logs->where('action', 'captcha_missing')->count();
        
        $successRate = $total > 0 ? round(($success / $total) * 100, 2) : 0;
        
        $this->info("📊 최근 {$days}일 CAPTCHA 통계");
        $this->table(
            ['항목', '건수', '비율'],
            [
                ['총 시도', $total, '100%'],
                ['성공', $success, $successRate . '%'],
                ['실패', $failed, $total > 0 ? round(($failed / $total) * 100, 2) . '%' : '0%'],
                ['미입력', $missing, $total > 0 ? round(($missing / $total) * 100, 2) . '%' : '0%'],
            ]
        );
        
        if ($failed > 0) {
            $this->newLine();
            $this->warn("⚠️  실패율이 " . round(($failed / $total) * 100, 2) . "%입니다.");
        }
    }
    
    /**
     * 최근 로그 표시
     */
    private function displayRecentLogs($logs)
    {
        $this->newLine();
        $this->info('🕐 최근 CAPTCHA 로그');
        
        $rows = $logs->map(function ($log) {
            $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
            $status = match($log->action) {
                'captcha_success' => '✅ 성공',
                'captcha_failed' => '❌ 실패',
                'captcha_missing' => '⚠️ 미입력',
                default => $log->action
            };
            
            return [
                $log->created_at->format('m-d H:i'),
                $status,
                $log->email ?? '-',
                $details['ip_address'] ?? '-',
                isset($details['score']) ? $details['score'] : '-',
                isset($details['error']) ? substr($details['error'], 0, 30) : '-',
            ];
        });
        
        $this->table(
            ['시간', '상태', '이메일', 'IP', '점수', '오류'],
            $rows
        );
    }
    
    /**
     * IP별 통계
     */
    private function displayIpStatistics($logs)
    {
        $this->newLine();
        $this->info('🌐 IP별 CAPTCHA 시도');
        
        $ipStats = [];
        foreach ($logs as $log) {
            $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
            $ip = $details['ip_address'] ?? 'unknown';
            
            if (!isset($ipStats[$ip])) {
                $ipStats[$ip] = [
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'missing' => 0,
                ];
            }
            
            $ipStats[$ip]['total']++;
            
            switch ($log->action) {
                case 'captcha_success':
                    $ipStats[$ip]['success']++;
                    break;
                case 'captcha_failed':
                    $ipStats[$ip]['failed']++;
                    break;
                case 'captcha_missing':
                    $ipStats[$ip]['missing']++;
                    break;
            }
        }
        
        // 시도 횟수로 정렬
        arsort($ipStats);
        
        $rows = [];
        foreach (array_slice($ipStats, 0, 10, true) as $ip => $stats) {
            $successRate = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 0;
            $rows[] = [
                $ip,
                $stats['total'],
                $stats['success'],
                $stats['failed'],
                $stats['missing'],
                $successRate . '%',
            ];
        }
        
        if (!empty($rows)) {
            $this->table(
                ['IP 주소', '총 시도', '성공', '실패', '미입력', '성공률'],
                $rows
            );
        }
        
        // 의심스러운 IP 감지
        $suspiciousIps = [];
        foreach ($ipStats as $ip => $stats) {
            if ($stats['failed'] > 5 || ($stats['total'] > 10 && $stats['success'] == 0)) {
                $suspiciousIps[] = $ip;
            }
        }
        
        if (!empty($suspiciousIps)) {
            $this->newLine();
            $this->error('🚨 의심스러운 IP 감지:');
            foreach ($suspiciousIps as $ip) {
                $stats = $ipStats[$ip];
                $this->line("  • {$ip} - 시도: {$stats['total']}, 실패: {$stats['failed']}");
            }
        }
    }
    
    /**
     * 시간대별 분석
     */
    private function displayHourlyAnalysis($logs)
    {
        $this->newLine();
        $this->info('⏰ 시간대별 CAPTCHA 시도');
        
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyStats[$i] = 0;
        }
        
        foreach ($logs as $log) {
            $hour = $log->created_at->hour;
            $hourlyStats[$hour]++;
        }
        
        // 그래프 표시
        $maxCount = max($hourlyStats);
        if ($maxCount > 0) {
            foreach ($hourlyStats as $hour => $count) {
                $barLength = $maxCount > 0 ? round(($count / $maxCount) * 40) : 0;
                $bar = str_repeat('█', $barLength);
                $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT) . '시';
                $countStr = str_pad($count, 3, ' ', STR_PAD_LEFT);
                $this->line("{$hourStr} {$countStr} {$bar}");
            }
        }
    }
    
    /**
     * CSV로 내보내기
     */
    private function exportToCsv($logs, $path)
    {
        $this->info("CSV 파일로 내보내는 중: {$path}");
        
        $handle = fopen($path, 'w');
        
        // BOM 추가 (Excel에서 한글 깨짐 방지)
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 헤더
        fputcsv($handle, [
            '시간',
            '액션',
            '이메일',
            'IP 주소',
            '점수',
            '오류',
            '사용자 에이전트',
        ]);
        
        // 데이터
        foreach ($logs as $log) {
            $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
            
            fputcsv($handle, [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->action,
                $log->email ?? '',
                $details['ip_address'] ?? '',
                $details['score'] ?? '',
                $details['error'] ?? '',
                $details['user_agent'] ?? '',
            ]);
        }
        
        fclose($handle);
        
        $this->info("✓ CSV 파일이 생성되었습니다: {$path}");
        $this->info("  총 {$logs->count()}개의 로그가 내보내졌습니다.");
    }
}
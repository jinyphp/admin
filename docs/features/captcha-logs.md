# CAPTCHA 로그 분석 가이드

## CAPTCHA 로그 시스템 개요

CAPTCHA 로그는 모든 인증 시도를 기록하여 보안 모니터링과 분석을 가능하게 합니다.

## 로그 유형

### 1. captcha_success (성공)
```json
{
    "action": "captcha_success",
    "email": "user@example.com",
    "ip_address": "192.168.1.1",
    "details": {
        "score": 0.9,
        "hostname": "yourdomain.com",
        "challenge_ts": "2024-01-01T12:00:00Z"
    }
}
```

### 2. captcha_failed (실패)
```json
{
    "action": "captcha_failed",
    "email": "attacker@example.com",
    "ip_address": "suspicious.ip",
    "details": {
        "score": 0.2,
        "error": "Score below threshold",
        "error_codes": ["timeout-or-duplicate"]
    }
}
```

### 3. captcha_missing (미입력)
```json
{
    "action": "captcha_missing",
    "email": "user@example.com",
    "ip_address": "192.168.1.1",
    "details": null
}
```

## 로그 조회 및 분석

### 웹 인터페이스
```
URL: http://yourdomain.com/admin/user/captcha/logs
```

주요 기능:
- 실시간 로그 조회
- 상태별 필터링
- IP별 그룹화
- 시간대별 통계

### 데이터베이스 직접 조회

#### 1. 최근 CAPTCHA 실패 조회
```sql
SELECT 
    email,
    ip_address,
    COUNT(*) as failure_count,
    MAX(logged_at) as last_attempt
FROM admin_user_logs
WHERE action = 'captcha_failed'
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY email, ip_address
ORDER BY failure_count DESC
LIMIT 10;
```

#### 2. IP별 성공률 분석
```sql
SELECT 
    ip_address,
    SUM(CASE WHEN action = 'captcha_success' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN action = 'captcha_failed' THEN 1 ELSE 0 END) as failed,
    ROUND(
        SUM(CASE WHEN action = 'captcha_success' THEN 1 ELSE 0 END) * 100.0 / 
        COUNT(*), 2
    ) as success_rate
FROM admin_user_logs
WHERE action IN ('captcha_success', 'captcha_failed')
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY ip_address
HAVING COUNT(*) > 5
ORDER BY success_rate ASC;
```

#### 3. 시간대별 패턴 분석
```sql
SELECT 
    HOUR(logged_at) as hour,
    COUNT(*) as attempt_count,
    SUM(CASE WHEN action = 'captcha_failed' THEN 1 ELSE 0 END) as failures
FROM admin_user_logs
WHERE action LIKE 'captcha%'
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY HOUR(logged_at)
ORDER BY hour;
```

## Laravel Tinker를 통한 분석

### 기본 통계
```php
php artisan tinker

// 전체 CAPTCHA 로그 수
>>> DB::table('admin_user_logs')
    ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
    ->count();

// 오늘의 실패율
>>> $today = DB::table('admin_user_logs')
    ->whereIn('action', ['captcha_success', 'captcha_failed'])
    ->whereDate('logged_at', today())
    ->selectRaw('action, COUNT(*) as count')
    ->groupBy('action')
    ->pluck('count', 'action');

>>> $failureRate = ($today['captcha_failed'] ?? 0) / 
    (($today['captcha_success'] ?? 0) + ($today['captcha_failed'] ?? 0)) * 100;
```

### 의심스러운 IP 탐지
```php
// 높은 실패율을 가진 IP
>>> DB::table('admin_user_logs')
    ->select('ip_address')
    ->selectRaw('COUNT(*) as total')
    ->selectRaw('SUM(CASE WHEN action = "captcha_failed" THEN 1 ELSE 0 END) as failures')
    ->whereIn('action', ['captcha_success', 'captcha_failed'])
    ->where('logged_at', '>=', now()->subDays(7))
    ->groupBy('ip_address')
    ->havingRaw('failures > 5')
    ->havingRaw('(failures * 100.0 / total) > 80')
    ->get();
```

### 사용자별 분석
```php
// 특정 사용자의 CAPTCHA 이력
>>> DB::table('admin_user_logs')
    ->where('email', 'user@example.com')
    ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
    ->orderBy('logged_at', 'desc')
    ->limit(10)
    ->get();
```

## 모니터링 대시보드 구축

### 실시간 모니터링 쿼리
```php
// app/Services/CaptchaMonitor.php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CaptchaMonitor
{
    public function getDashboardStats()
    {
        return [
            'today' => $this->getTodayStats(),
            'weekly' => $this->getWeeklyStats(),
            'top_failures' => $this->getTopFailures(),
            'suspicious_ips' => $this->getSuspiciousIPs(),
            'hourly_pattern' => $this->getHourlyPattern(),
        ];
    }
    
    private function getTodayStats()
    {
        return DB::table('admin_user_logs')
            ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
            ->whereDate('logged_at', today())
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }
    
    private function getWeeklyStats()
    {
        return DB::table('admin_user_logs')
            ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
            ->where('logged_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(logged_at) as date, action, COUNT(*) as count')
            ->groupBy('date', 'action')
            ->get()
            ->groupBy('date');
    }
    
    private function getSuspiciousIPs()
    {
        return DB::table('admin_user_logs')
            ->select('ip_address')
            ->selectRaw('COUNT(*) as attempts')
            ->selectRaw('MAX(logged_at) as last_seen')
            ->where('action', 'captcha_failed')
            ->where('logged_at', '>=', now()->subHours(24))
            ->groupBy('ip_address')
            ->having('attempts', '>', 5)
            ->orderBy('attempts', 'desc')
            ->limit(10)
            ->get();
    }
}
```

## 알림 설정

### 자동 알림 시스템
```php
// app/Console/Commands/MonitorCaptcha.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CaptchaMonitor;
use App\Notifications\CaptchaAlert;

class MonitorCaptcha extends Command
{
    protected $signature = 'captcha:monitor';
    protected $description = 'Monitor CAPTCHA logs for suspicious activity';
    
    public function handle(CaptchaMonitor $monitor)
    {
        $stats = $monitor->getDashboardStats();
        
        // 의심스러운 활동 감지
        if (count($stats['suspicious_ips']) > 0) {
            foreach ($stats['suspicious_ips'] as $ip) {
                if ($ip->attempts > 10) {
                    // 알림 발송
                    $this->sendAlert($ip);
                    
                    // IP 자동 차단 (선택사항)
                    $this->blockIP($ip->ip_address);
                }
            }
        }
        
        // 실패율이 높은 경우
        $failureRate = $this->calculateFailureRate($stats['today']);
        if ($failureRate > 50) {
            $this->warn("High CAPTCHA failure rate: {$failureRate}%");
            // 관리자에게 알림
        }
    }
    
    private function calculateFailureRate($stats)
    {
        $total = array_sum($stats);
        if ($total == 0) return 0;
        
        $failures = $stats['captcha_failed'] ?? 0;
        return round(($failures / $total) * 100, 2);
    }
}
```

### Cron 설정
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // 매 시간 CAPTCHA 모니터링
    $schedule->command('captcha:monitor')->hourly();
    
    // 일일 리포트
    $schedule->command('captcha:daily-report')->dailyAt('09:00');
    
    // 오래된 로그 정리 (90일 이상)
    $schedule->command('captcha:cleanup --days=90')->weekly();
}
```

## 보안 분석 패턴

### 1. Brute Force 공격 탐지
```sql
-- 짧은 시간 내 반복 시도
SELECT 
    ip_address,
    COUNT(*) as attempts,
    MIN(logged_at) as first_attempt,
    MAX(logged_at) as last_attempt,
    TIMESTAMPDIFF(MINUTE, MIN(logged_at), MAX(logged_at)) as duration_minutes
FROM admin_user_logs
WHERE action IN ('captcha_failed', 'captcha_missing')
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_address
HAVING attempts > 10
    AND duration_minutes < 30;
```

### 2. Bot Detection
```sql
-- 비정상적으로 빠른 응답
SELECT *
FROM admin_user_logs
WHERE action = 'captcha_success'
    AND JSON_EXTRACT(details, '$.response_time') < 1000  -- 1초 미만
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

### 3. 지역별 분석
```php
// GeoIP를 이용한 지역 분석
use GeoIp2\Database\Reader;

$reader = new Reader('/path/to/GeoLite2-City.mmdb');

$logs = DB::table('admin_user_logs')
    ->whereIn('action', ['captcha_failed'])
    ->where('logged_at', '>=', now()->subDay())
    ->get();

$countries = [];
foreach ($logs as $log) {
    try {
        $record = $reader->city($log->ip_address);
        $country = $record->country->name;
        $countries[$country] = ($countries[$country] ?? 0) + 1;
    } catch (\Exception $e) {
        $countries['Unknown'] = ($countries['Unknown'] ?? 0) + 1;
    }
}

arsort($countries);
```

## 리포트 생성

### 일일 리포트 템플릿
```blade
{{-- resources/views/reports/captcha-daily.blade.php --}}

<h1>CAPTCHA 일일 리포트 - {{ $date }}</h1>

<h2>요약</h2>
<ul>
    <li>총 시도: {{ $stats['total'] }}</li>
    <li>성공: {{ $stats['success'] }} ({{ $stats['success_rate'] }}%)</li>
    <li>실패: {{ $stats['failed'] }} ({{ $stats['failure_rate'] }}%)</li>
    <li>미입력: {{ $stats['missing'] }}</li>
</ul>

<h2>상위 실패 IP</h2>
<table>
    <thead>
        <tr>
            <th>IP 주소</th>
            <th>실패 횟수</th>
            <th>마지막 시도</th>
            <th>조치</th>
        </tr>
    </thead>
    <tbody>
        @foreach($topFailures as $ip)
        <tr>
            <td>{{ $ip->ip_address }}</td>
            <td>{{ $ip->failures }}</td>
            <td>{{ $ip->last_attempt }}</td>
            <td>
                @if($ip->failures > 10)
                    <span class="badge badge-danger">차단 권장</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>시간대별 패턴</h2>
<canvas id="hourlyChart"></canvas>

<h2>권장 조치</h2>
<ul>
    @if($stats['failure_rate'] > 50)
        <li>⚠️ 높은 실패율 - CAPTCHA 난이도 조정 검토</li>
    @endif
    
    @if(count($suspiciousIPs) > 5)
        <li>⚠️ 의심스러운 IP {{ count($suspiciousIPs) }}개 발견 - IP 차단 검토</li>
    @endif
    
    @if($stats['missing'] > $stats['success'])
        <li>⚠️ CAPTCHA 미입력이 많음 - UI 개선 필요</li>
    @endif
</ul>
```

## 로그 정리 및 보관

### 자동 정리 스크립트
```php
// app/Console/Commands/CleanupCaptchaLogs.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupCaptchaLogs extends Command
{
    protected $signature = 'captcha:cleanup {--days=90}';
    protected $description = 'Clean up old CAPTCHA logs';
    
    public function handle()
    {
        $days = $this->option('days');
        
        // 백업 (선택사항)
        $this->backupOldLogs($days);
        
        // 삭제
        $deleted = DB::table('admin_user_logs')
            ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
            ->where('logged_at', '<', now()->subDays($days))
            ->delete();
        
        $this->info("Deleted {$deleted} CAPTCHA logs older than {$days} days");
    }
    
    private function backupOldLogs($days)
    {
        $logs = DB::table('admin_user_logs')
            ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
            ->where('logged_at', '<', now()->subDays($days))
            ->get();
        
        if ($logs->count() > 0) {
            $filename = storage_path('backups/captcha_logs_' . date('Y-m-d') . '.json');
            file_put_contents($filename, $logs->toJson());
            $this->info("Backed up {$logs->count()} logs to {$filename}");
        }
    }
}
```

## 성능 최적화

### 인덱스 추가
```sql
-- CAPTCHA 로그 조회 성능 개선
CREATE INDEX idx_captcha_logs ON admin_user_logs(action, logged_at);
CREATE INDEX idx_captcha_ip ON admin_user_logs(ip_address, action);
CREATE INDEX idx_captcha_email ON admin_user_logs(email, action);
```

### 파티셔닝 (대용량 데이터)
```sql
-- 월별 파티셔닝
ALTER TABLE admin_user_logs 
PARTITION BY RANGE (YEAR(logged_at) * 100 + MONTH(logged_at)) (
    PARTITION p202401 VALUES LESS THAN (202402),
    PARTITION p202402 VALUES LESS THAN (202403),
    -- ...
);
```

## 대시보드 위젯

### 실시간 CAPTCHA 모니터
```javascript
// resources/js/captcha-monitor.js

class CaptchaMonitor {
    constructor() {
        this.chart = null;
        this.updateInterval = 5000; // 5초
    }
    
    async fetchStats() {
        const response = await fetch('/api/admin/captcha/stats');
        return await response.json();
    }
    
    updateDashboard() {
        this.fetchStats().then(stats => {
            // 성공률 게이지
            this.updateGauge('success-rate', stats.successRate);
            
            // 실시간 카운터
            document.getElementById('total-attempts').textContent = stats.total;
            document.getElementById('failures-today').textContent = stats.failuresToday;
            
            // 의심 IP 목록
            this.updateSuspiciousList(stats.suspiciousIPs);
            
            // 차트 업데이트
            this.updateChart(stats.hourlyData);
        });
    }
    
    start() {
        this.updateDashboard();
        setInterval(() => this.updateDashboard(), this.updateInterval);
    }
}

// 초기화
document.addEventListener('DOMContentLoaded', () => {
    const monitor = new CaptchaMonitor();
    monitor.start();
});
```

---

## 관련 문서

- [CAPTCHA 설정 가이드](./captcha-setup-guide.md)
- [CAPTCHA 트러블슈팅](./captcha-troubleshooting.md)
- [보안 모범 사례](./security-best-practices.md)
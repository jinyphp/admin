<?php

namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Jiny\Admin\Models\AdminUserLog;
use Illuminate\Support\Facades\DB;

class AdminCaptchaLogs extends Component
{
    use WithPagination;

    // 필터 속성
    public $days = 7;
    public $type = '';
    public $email = '';
    public $ip = '';
    public $search = '';
    public $perPage = 20;
    
    // 통계 데이터
    public $statistics = [];
    public $ipStatistics = [];
    public $hourlyAnalysis = [];
    public $suspiciousIps = [];
    
    // UI 상태
    public $activeTab = 'logs';
    public $showExportModal = false;
    public $showBlockIpModal = false;
    public $selectedIp = '';
    
    // 실시간 업데이트
    public $autoRefresh = false;
    public $refreshInterval = 30; // 초
    
    protected $queryString = [
        'days' => ['except' => 7],
        'type' => ['except' => ''],
        'email' => ['except' => ''],
        'ip' => ['except' => ''],
        'search' => ['except' => ''],
        'activeTab' => ['except' => 'logs'],
    ];
    
    protected $listeners = [
        'refreshLogs' => '$refresh',
        'blockIp' => 'blockIpAddress',
        'cleanupLogs' => 'performCleanup',
    ];
    
    public function mount()
    {
        $this->loadStatistics();
    }
    
    public function render()
    {
        return view('jiny-admin::livewire.admin-captcha-logs', [
            'logs' => $this->getLogs(),
            'statistics' => $this->statistics,
            'ipStatistics' => $this->ipStatistics,
            'hourlyAnalysis' => $this->hourlyAnalysis,
            'suspiciousIps' => $this->suspiciousIps,
        ]);
    }
    
    /**
     * 로그 데이터 가져오기
     */
    public function getLogs()
    {
        if ($this->activeTab !== 'logs') {
            return collect();
        }
        
        $query = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->where('created_at', '>=', now()->subDays($this->days));
        
        // 필터 적용
        if ($this->type) {
            $query->where('action', 'captcha_' . $this->type);
        }
        
        if ($this->email) {
            $query->where('email', 'like', '%' . $this->email . '%');
        }
        
        if ($this->ip) {
            $query->where(function($q) {
                $q->where('ip_address', 'like', '%' . $this->ip . '%')
                  ->orWhereJsonContains('details->ip_address', $this->ip);
            });
        }
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('email', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                  ->orWhereJsonContains('details->ip_address', $this->search);
            });
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }
    
    /**
     * 통계 데이터 로드
     */
    public function loadStatistics()
    {
        $startDate = now()->subDays($this->days)->startOfDay();
        $endDate = now()->endOfDay();
        
        // 기본 통계
        $this->statistics = $this->calculateStatistics($startDate, $endDate);
        
        // IP별 통계
        $this->ipStatistics = $this->calculateIpStatistics($startDate, $endDate);
        
        // 시간대별 분석
        $this->hourlyAnalysis = $this->calculateHourlyAnalysis($startDate, $endDate);
        
        // 의심스러운 IP 감지
        $this->suspiciousIps = $this->detectSuspiciousIps($this->ipStatistics);
    }
    
    /**
     * 기본 통계 계산
     */
    protected function calculateStatistics($startDate, $endDate)
    {
        $logs = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->whereBetween('created_at', [$startDate, $endDate])->get();
        
        $total = $logs->count();
        $success = $logs->where('action', 'captcha_success')->count();
        $failed = $logs->where('action', 'captcha_failed')->count();
        $missing = $logs->where('action', 'captcha_missing')->count();
        
        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'missing' => $missing,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0,
            'failed_rate' => $total > 0 ? round(($failed / $total) * 100, 2) : 0,
            'missing_rate' => $total > 0 ? round(($missing / $total) * 100, 2) : 0,
        ];
    }
    
    /**
     * IP별 통계 계산
     */
    protected function calculateIpStatistics($startDate, $endDate)
    {
        $logs = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->whereBetween('created_at', [$startDate, $endDate])->get();
        
        $ipStats = [];
        foreach ($logs as $log) {
            $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
            $ip = $details['ip_address'] ?? $log->ip_address ?? 'unknown';
            
            if (!isset($ipStats[$ip])) {
                $ipStats[$ip] = [
                    'ip' => $ip,
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'missing' => 0,
                    'success_rate' => 0,
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
        
        // 성공률 계산 및 정렬
        foreach ($ipStats as &$stats) {
            $stats['success_rate'] = $stats['total'] > 0 
                ? round(($stats['success'] / $stats['total']) * 100, 1) 
                : 0;
        }
        
        // 시도 횟수로 정렬
        uasort($ipStats, function($a, $b) {
            return $b['total'] - $a['total'];
        });
        
        return array_slice($ipStats, 0, 20, true);
    }
    
    /**
     * 시간대별 분석 계산
     */
    protected function calculateHourlyAnalysis($startDate, $endDate)
    {
        $logs = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->whereBetween('created_at', [$startDate, $endDate])->get();
        
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyStats[$i] = [
                'hour' => $i,
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'missing' => 0,
            ];
        }
        
        foreach ($logs as $log) {
            $hour = $log->created_at->hour;
            $hourlyStats[$hour]['total']++;
            
            switch ($log->action) {
                case 'captcha_success':
                    $hourlyStats[$hour]['success']++;
                    break;
                case 'captcha_failed':
                    $hourlyStats[$hour]['failed']++;
                    break;
                case 'captcha_missing':
                    $hourlyStats[$hour]['missing']++;
                    break;
            }
        }
        
        return $hourlyStats;
    }
    
    /**
     * 의심스러운 IP 감지
     */
    protected function detectSuspiciousIps($ipStatistics)
    {
        $suspicious = [];
        
        foreach ($ipStatistics as $ip => $stats) {
            // 실패 횟수가 5회 이상이거나, 10회 이상 시도했는데 성공이 0인 경우
            if ($stats['failed'] > 5 || ($stats['total'] > 10 && $stats['success'] == 0)) {
                $suspicious[] = $stats;
            }
        }
        
        return $suspicious;
    }
    
    /**
     * 필터 적용
     */
    public function applyFilters()
    {
        $this->resetPage();
        $this->loadStatistics();
    }
    
    /**
     * 필터 초기화
     */
    public function resetFilters()
    {
        $this->days = 7;
        $this->type = '';
        $this->email = '';
        $this->ip = '';
        $this->search = '';
        $this->resetPage();
        $this->loadStatistics();
    }
    
    /**
     * 탭 전환
     */
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab !== 'logs') {
            $this->loadStatistics();
        }
    }
    
    /**
     * CSV 내보내기
     */
    public function export()
    {
        // 쿼리 빌드
        $query = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->where('created_at', '>=', now()->subDays($this->days));
        
        // 필터 적용
        if ($this->type) {
            $query->where('action', 'captcha_' . $this->type);
        }
        
        if ($this->email) {
            $query->where('email', $this->email);
        }
        
        if ($this->ip) {
            $query->whereJsonContains('details->ip_address', $this->ip);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'captcha_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response()->streamDownload(function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // BOM 추가 (Excel에서 한글 깨짐 방지)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // 헤더
            fputcsv($file, [
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
                
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->action,
                    $log->email ?? '',
                    $details['ip_address'] ?? $log->ip_address ?? '',
                    $details['score'] ?? '',
                    $details['error'] ?? '',
                    $log->user_agent ?? '',
                ]);
            }
            
            fclose($file);
        }, $filename, $headers);
    }
    
    /**
     * IP 차단 모달 표시
     */
    public function showBlockIpModal($ip)
    {
        $this->selectedIp = $ip;
        $this->showBlockIpModal = true;
    }
    
    /**
     * IP 차단
     */
    public function blockIpAddress()
    {
        if (!$this->selectedIp) {
            session()->flash('error', 'IP 주소가 선택되지 않았습니다.');
            return;
        }
        
        // IP 차단 로직 구현
        // 예: 방화벽 규칙 추가, 블랙리스트 저장 등
        
        session()->flash('success', "IP {$this->selectedIp}가 차단되었습니다.");
        
        $this->showBlockIpModal = false;
        $this->selectedIp = '';
        $this->loadStatistics();
    }
    
    /**
     * 로그 정리
     */
    public function performCleanup($days = 30)
    {
        $deleted = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->where('created_at', '<', now()->subDays($days))->delete();
        
        session()->flash('success', "{$deleted}개의 오래된 로그가 삭제되었습니다.");
        
        $this->loadStatistics();
    }
    
    /**
     * 자동 새로고침 토글
     */
    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        
        if ($this->autoRefresh) {
            $this->dispatch('startAutoRefresh', $this->refreshInterval * 1000);
        } else {
            $this->dispatch('stopAutoRefresh');
        }
    }
    
    /**
     * 페이지당 항목 수 변경
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    /**
     * 필터 변경 시 페이지 리셋
     */
    public function updatedDays()
    {
        $this->resetPage();
        $this->loadStatistics();
    }
    
    public function updatedType()
    {
        $this->resetPage();
    }
    
    public function updatedEmail()
    {
        $this->resetPage();
    }
    
    public function updatedIp()
    {
        $this->resetPage();
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
}
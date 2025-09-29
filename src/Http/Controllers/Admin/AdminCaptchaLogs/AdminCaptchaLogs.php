<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\AdminUserLog;

/**
 * CAPTCHA 로그 관리 메인 컨트롤러
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminCaptchaLogs
 */
class AdminCaptchaLogs extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // JSON 설정 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * CAPTCHA 로그 목록 페이지 표시
     */
    public function __invoke(Request $request)
    {
        return $this->index($request);
    }
    
    /**
     * CAPTCHA 로그 목록 페이지 표시 (index 메서드)
     */
    public function index(Request $request)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (!isset($this->jsonData['template']['index'])) {
            return response('Error: template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminCaptchaLogs.json';
        $settingsPath = $jsonPath;

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
        ]);
    }

    /**
     * Hook: 데이터 조회 전 실행
     */
    public function hookIndexing($wire)
    {
        // JSON 설정에서 이미 where 조건이 설정되어 있으므로
        // 추가 설정이 필요없음
        // table.where.default 설정이 자동으로 적용됨
        
        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     */
    public function hookIndexed($wire, $rows)
    {
        // 각 로그에 대한 추가 정보 처리
        if ($rows && count($rows) > 0) {
            foreach ($rows as $row) {
                // details JSON 파싱
                if ($row->details) {
                    $details = is_string($row->details) ? json_decode($row->details, true) : $row->details;
                    $row->score = $details['score'] ?? null;
                    $row->error = $details['error'] ?? null;
                }
                
                // 상태 뱃지 정보 추가
                $row->status_color = match($row->action) {
                    'captcha_success' => 'green',
                    'captcha_failed' => 'red',
                    'captcha_missing' => 'yellow',
                    default => 'gray'
                };
                
                $row->status_text = match($row->action) {
                    'captcha_success' => '성공',
                    'captcha_failed' => '실패',
                    'captcha_missing' => '미입력',
                    default => $row->action
                };
            }
        }

        return $rows;
    }

    /**
     * Hook: 통계 데이터 생성
     */
    public function hookStatistics($wire)
    {
        $days = request()->get('days', 7);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();
        
        $logs = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->whereBetween('logged_at', [$startDate, $endDate])->get();
        
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
        ];
    }

    /**
     * Hook: IP별 통계
     */
    public function hookIpStatistics($wire)
    {
        $days = request()->get('days', 7);
        $logs = AdminUserLog::whereIn('action', [
            'captcha_success',
            'captcha_failed', 
            'captcha_missing'
        ])->where('logged_at', '>=', now()->subDays($days))->get();
        
        $ipStats = [];
        foreach ($logs as $log) {
            $ip = $log->ip_address ?? 'unknown';
            
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
        
        // 의심스러운 IP 감지
        $suspicious = [];
        foreach ($ipStats as $ip => $stats) {
            if ($stats['failed'] > 5 || ($stats['total'] > 10 && $stats['success'] == 0)) {
                $suspicious[] = $ip;
            }
        }
        
        return [
            'stats' => array_slice($ipStats, 0, 10, true),
            'suspicious' => $suspicious
        ];
    }
}
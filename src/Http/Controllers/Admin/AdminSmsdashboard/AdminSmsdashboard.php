<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsdashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;
use Carbon\Carbon;

/**
 * Smsdashboard 관리 메인 컨트롤러 (목록/인덱스 페이지)
 *
 * Smsdashboard 목록을 표시하고 관리하는 기능을 제공합니다.
 * Livewire 컴포넌트(AdminTable)와 Hook 패턴을 통해 동작합니다.
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsdashboard
 * @since   1.0.0
 */
class AdminSmsdashboard extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     *
     * AdminSmsdashboard.json 설정 파일을 로드하여 컨트롤러를 초기화합니다.
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Smsdashboard 목록 페이지 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (! isset($this->jsonData['template']['index'])) {
            return response('Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminSmsdashboard.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);
        
        // SMS 대시보드 통계 데이터 수집
        $stats = $this->collectStats();
        $recentSms = $this->getRecentSms();
        $dailyStats = $this->getDailyStats();
        $providerStats = $this->getProviderStats();
        $queueStats = $this->getQueueStats();

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'stats' => $stats,
            'recentSms' => $recentSms,
            'dailyStats' => $dailyStats,
            'providerStats' => $providerStats,
            'queueStats' => $queueStats,
        ]);
    }

    /**
     * Hook: Livewire 컴포넌트의 데이터 조회 전 실행
     * 데이터베이스 쿼리 조건을 수정하거나 추가 로직을 실행할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 해당 값이 출력됨
     */
    public function hookIndexing($wire)
    {
        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     * 조회된 데이터를 가공하거나 추가 처리를 할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param mixed $rows 조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 커스터마이징된 헤더 설정
     */
    public function hookTableHeader($wire)
    {
        return $this->jsonData['index']['table']['columns'] ?? [];
    }

    /**
     * Hook: 페이지네이션 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 10,
            'perPageOptions' => [10, 25, 50, 100]
        ];
    }

    /**
     * Hook: 정렬 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 정렬 설정
     */
    public function hookSorting($wire)
    {
        return $this->jsonData['index']['sorting'] ?? [
            'default' => 'created_at',
            'direction' => 'desc'
        ];
    }

    /**
     * Hook: 검색 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => 'Search smsdashboards...',
            'debounce' => 300
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        return $this->jsonData['index']['filters'] ?? [];
    }
    
    /**
     * SMS 통계 데이터 수집
     */
    private function collectStats()
    {
        $totalSent = DB::table('admin_sms_sends')->count();
        $successCount = DB::table('admin_sms_sends')->where('status', 'success')->count();
        
        return [
            'total_sent' => $totalSent,
            'sent_today' => DB::table('admin_sms_sends')
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'sent_this_week' => DB::table('admin_sms_sends')
                ->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->count(),
            'sent_this_month' => DB::table('admin_sms_sends')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'success_rate' => $totalSent > 0 ? round(($successCount / $totalSent) * 100, 2) : 0,
            'pending_queue' => DB::table('admin_sms_queues')
                ->where('status', 'pending')
                ->count(),
            'failed_queue' => DB::table('admin_sms_queues')
                ->where('status', 'failed')
                ->count(),
            'processing_queue' => DB::table('admin_sms_queues')
                ->where('status', 'processing')
                ->count(),
            'providers_count' => DB::table('admin_sms_providers')
                ->where('is_active', true)
                ->count(),
            'total_cost' => DB::table('admin_sms_sends')
                ->sum('cost') ?? 0,
        ];
    }
    
    /**
     * 최근 SMS 발송 기록 조회
     */
    private function getRecentSms()
    {
        return DB::table('admin_sms_sends')
            ->leftJoin('admin_sms_providers', 'admin_sms_sends.provider_id', '=', 'admin_sms_providers.id')
            ->select(
                'admin_sms_sends.*',
                'admin_sms_providers.name as provider_name'
            )
            ->orderBy('admin_sms_sends.created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * 일별 발송 통계 (최근 7일)
     */
    private function getDailyStats()
    {
        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $success = DB::table('admin_sms_sends')
                ->whereDate('created_at', $date)
                ->where('status', 'success')
                ->count();
            $failed = DB::table('admin_sms_sends')
                ->whereDate('created_at', $date)
                ->where('status', 'failed')
                ->count();
            
            $stats[] = [
                'date' => $date->format('m/d'),
                'success' => $success,
                'failed' => $failed,
                'total' => $success + $failed
            ];
        }
        return $stats;
    }
    
    /**
     * 프로바이더별 통계
     */
    private function getProviderStats()
    {
        return DB::table('admin_sms_sends')
            ->leftJoin('admin_sms_providers', 'admin_sms_sends.provider_id', '=', 'admin_sms_providers.id')
            ->select(
                'admin_sms_providers.name as provider',
                DB::raw('count(*) as count'),
                DB::raw('sum(case when admin_sms_sends.status = "success" then 1 else 0 end) as success_count'),
                DB::raw('sum(case when admin_sms_sends.status = "failed" then 1 else 0 end) as failed_count'),
                DB::raw('avg(admin_sms_sends.cost) as avg_cost'),
                DB::raw('sum(admin_sms_sends.cost) as total_cost')
            )
            ->groupBy('admin_sms_providers.name', 'admin_sms_providers.id')
            ->orderBy('count', 'desc')
            ->get();
    }
    
    /**
     * 큐 상태 통계
     */
    private function getQueueStats()
    {
        return DB::table('admin_sms_queues')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();
    }
}
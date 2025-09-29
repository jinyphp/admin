<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * IP 화이트리스트 상세 보기 컨트롤러
 */
class AdminIpWhitelistShow extends Controller
{
    /**
     * JSON 설정 데이터
     * @var array|null
     */
    private $jsonData;
    
    /**
     * 생성자 - JSON 설정 로드
     */
    public function __construct()
    {
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }
    
    /**
     * IP 화이트리스트 상세 페이지 표시
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request, $id)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 설정을 로드할 수 없습니다.', 500);
        }
        
        // 템플릿 경로 확인
        if (!isset($this->jsonData['template']['show'])) {
            return response('Error: template.show 설정이 필요합니다.', 500);
        }
        
        // 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_ip_whitelist';
        $data = DB::table($tableName)->where('id', $id)->first();
        
        if (!$data) {
            return response('Error: 데이터를 찾을 수 없습니다.', 404);
        }
        
        // 뷰 데이터 준비
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminIpWhitelist.json';
        $this->jsonData['controllerClass'] = get_class($this);
        
        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $jsonPath,
            'controllerClass' => static::class,
            'id' => $id,
            'data' => $data,
            'form' => (array) $data // 데이터를 폼 배열로 변환
        ]);
    }
    
    /**
     * Hook: 상세 데이터 표시 전 처리
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param object $data 표시할 데이터
     * @return object
     */
    public function hookShowing($wire, $data)
    {
        // IP 표시 형식 생성
        switch ($data->type) {
            case 'range':
                $data->ip_display = "{$data->ip_range_start} ~ {$data->ip_range_end}";
                $data->ip_count = $this->calculateIpCount($data->ip_range_start, $data->ip_range_end);
                break;
            case 'cidr':
                $data->ip_display = "{$data->ip_address}/{$data->cidr_prefix}";
                $data->ip_count = pow(2, 32 - $data->cidr_prefix);
                break;
            default:
                $data->ip_display = $data->ip_address;
                $data->ip_count = 1;
        }
        
        // 상태 정보
        if ($data->expires_at && now()->gt($data->expires_at)) {
            $data->status = '만료됨';
            $data->status_class = 'danger';
        } elseif (!$data->is_active) {
            $data->status = '비활성';
            $data->status_class = 'warning';
        } else {
            $data->status = '활성';
            $data->status_class = 'success';
        }
        
        // 최근 접근 로그 조회
        $recentLogs = DB::table('admin_ip_access_logs')
            ->where('ip_address', 'LIKE', $data->ip_address . '%')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $wire->recentAccessLogs = $recentLogs;
        
        // 통계 정보
        $stats = DB::table('admin_ip_access_logs')
            ->where('ip_address', 'LIKE', $data->ip_address . '%')
            ->selectRaw('
                COUNT(*) as total_access,
                SUM(CASE WHEN is_allowed = 1 THEN 1 ELSE 0 END) as allowed_count,
                SUM(CASE WHEN is_allowed = 0 THEN 1 ELSE 0 END) as blocked_count,
                MAX(created_at) as last_access
            ')
            ->first();
            
        $wire->accessStats = $stats;
        
        return $data;
    }
    
    /**
     * IP 범위의 IP 개수 계산
     * 
     * @param string $start
     * @param string $end
     * @return int
     */
    private function calculateIpCount($start, $end)
    {
        $startLong = ip2long($start);
        $endLong = ip2long($end);
        
        if ($startLong === false || $endLong === false) {
            return 0;
        }
        
        return $endLong - $startLong + 1;
    }
}
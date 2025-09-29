<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\AdminIpWhitelist as IpWhitelistModel;

/**
 * IP 화이트리스트 수정 컨트롤러
 */
class AdminIpWhitelistEdit extends Controller
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
     * IP 화이트리스트 수정 페이지 표시
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
        if (!isset($this->jsonData['template']['edit'])) {
            return response('Error: template.edit 설정이 필요합니다.', 500);
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
        
        return view($this->jsonData['template']['edit'], [
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
     * Hook: 수정 폼 초기화
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $form 기존 데이터
     * @return array
     */
    public function hookEditing($wire, $form)
    {
        // 만료 상태 체크
        if (!empty($form['expires_at'])) {
            $expiresAt = \Carbon\Carbon::parse($form['expires_at']);
            if ($expiresAt->isPast()) {
                $wire->showWarning = true;
                $wire->warningMessage = '이 IP는 이미 만료되었습니다.';
            }
        }
        
        // 최근 접근 정보 표시
        if (!empty($form['last_accessed_at'])) {
            $lastAccess = \Carbon\Carbon::parse($form['last_accessed_at']);
            $wire->lastAccessInfo = $lastAccess->diffForHumans() . ' (' . $form['access_count'] . '회 접근)';
        }
        
        return $form;
    }
    
    /**
     * Hook: 업데이트 전 처리
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $form 수정된 데이터
     * @return array|string
     */
    public function hookUpdating($wire, $form)
    {
        // 수정 중인 레코드의 ID 가져오기
        $recordId = $wire->modelId ?? $wire->id ?? null;
        
        // IP 타입별 검증 (타입은 변경 불가)
        switch ($form['type']) {
            case 'single':
                if (!filter_var($form['ip_address'], FILTER_VALIDATE_IP)) {
                    return '유효한 IP 주소를 입력해주세요.';
                }
                
                // 다른 IP와 중복 체크 (자기 자신 제외)
                $exists = IpWhitelistModel::where('ip_address', $form['ip_address'])
                    ->where('type', 'single')
                    ->where('id', '!=', $recordId)
                    ->exists();
                    
                if ($exists) {
                    return '이미 등록된 IP 주소입니다.';
                }
                break;
                
            case 'range':
                if (empty($form['ip_range_start']) || empty($form['ip_range_end'])) {
                    return 'IP 범위를 입력해주세요.';
                }
                
                if (!filter_var($form['ip_range_start'], FILTER_VALIDATE_IP) || 
                    !filter_var($form['ip_range_end'], FILTER_VALIDATE_IP)) {
                    return '유효한 IP 범위를 입력해주세요.';
                }
                
                if (ip2long($form['ip_range_start']) > ip2long($form['ip_range_end'])) {
                    return '시작 IP는 종료 IP보다 작아야 합니다.';
                }
                break;
                
            case 'cidr':
                if (!filter_var($form['ip_address'], FILTER_VALIDATE_IP)) {
                    return '유효한 기본 IP 주소를 입력해주세요.';
                }
                
                if (empty($form['cidr_prefix']) || $form['cidr_prefix'] < 1 || $form['cidr_prefix'] > 32) {
                    return 'CIDR 프리픽스는 1-32 사이의 값이어야 합니다.';
                }
                break;
        }
        
        // 만료일 검증 (수정 시에는 과거 날짜도 허용)
        if (!empty($form['expires_at'])) {
            $form['expires_at'] = \Carbon\Carbon::parse($form['expires_at'])->format('Y-m-d H:i:s');
        }
        
        // 타임스탬프
        $form['updated_at'] = now();
        
        return $form;
    }
    
    /**
     * Hook: 업데이트 후 처리
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $form 업데이트된 데이터
     * @return void
     */
    public function hookUpdated($wire, $form)
    {
        // 캐시 초기화
        IpWhitelistModel::clearCache();
        
        // 수정된 레코드의 ID 가져오기
        $recordId = $wire->modelId ?? $wire->id ?? null;
        
        // 로그 기록
        DB::table('admin_user_logs')->insert([
            'action' => 'ip_whitelist_updated',
            'target_type' => 'ip_whitelist',
            'target_id' => $recordId,
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => json_encode([
                'ip_address' => $form['ip_address'],
                'type' => $form['type'],
                'is_active' => $form['is_active']
            ]),
            'logged_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        session()->flash('notification', [
            'type' => 'success',
            'title' => '수정 완료',
            'message' => 'IP 화이트리스트가 수정되었습니다.'
        ]);
    }
}
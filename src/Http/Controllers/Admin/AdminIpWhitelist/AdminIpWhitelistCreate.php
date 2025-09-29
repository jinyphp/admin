<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\AdminIpWhitelist as IpWhitelistModel;

/**
 * IP 화이트리스트 생성 컨트롤러
 * 
 * 새로운 IP를 화이트리스트에 추가하는 컨트롤러입니다.
 */
class AdminIpWhitelistCreate extends Controller
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
     * IP 화이트리스트 생성 페이지 표시
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 설정을 로드할 수 없습니다.', 500);
        }
        
        // 템플릿 경로 확인
        if (!isset($this->jsonData['template']['create'])) {
            return response('Error: template.create 설정이 필요합니다.', 500);
        }
        
        // 뷰 데이터 준비
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminIpWhitelist.json';
        $this->jsonData['controllerClass'] = get_class($this);
        
        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $jsonPath,
            'controllerClass' => static::class,
            'form' => [] // 빈 폼 데이터 전달
        ]);
    }
    
    /**
     * Hook: 폼 초기화
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $form 폼 데이터
     * @return array
     */
    public function hookCreating($wire, $form)
    {
        // 기본값 설정
        $form['type'] = $form['type'] ?? 'single';
        $form['is_active'] = $form['is_active'] ?? true;
        
        // 현재 사용자의 IP를 힌트로 표시
        $wire->userIpHint = request()->ip();
        
        return $form;
    }
    
    /**
     * Hook: 저장 전 처리
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $form 폼 데이터
     * @return array|string 성공시 배열, 실패시 에러 메시지
     */
    public function hookStoring($wire, $form)
    {
        // IP 타입별 검증
        switch ($form['type']) {
            case 'single':
                // 단일 IP 검증
                if (!filter_var($form['ip_address'], FILTER_VALIDATE_IP)) {
                    return '유효한 IP 주소를 입력해주세요.';
                }
                
                // 중복 체크
                $exists = IpWhitelistModel::where('ip_address', $form['ip_address'])
                    ->where('type', 'single')
                    ->exists();
                    
                if ($exists) {
                    return '이미 등록된 IP 주소입니다.';
                }
                break;
                
            case 'range':
                // IP 범위 검증
                if (empty($form['ip_range_start']) || empty($form['ip_range_end'])) {
                    return 'IP 범위를 입력해주세요.';
                }
                
                if (!filter_var($form['ip_range_start'], FILTER_VALIDATE_IP) || 
                    !filter_var($form['ip_range_end'], FILTER_VALIDATE_IP)) {
                    return '유효한 IP 범위를 입력해주세요.';
                }
                
                // 시작 IP가 종료 IP보다 작아야 함
                if (ip2long($form['ip_range_start']) > ip2long($form['ip_range_end'])) {
                    return '시작 IP는 종료 IP보다 작아야 합니다.';
                }
                
                // 범위 설정 시 ip_address는 시작 IP로 설정
                $form['ip_address'] = $form['ip_range_start'];
                break;
                
            case 'cidr':
                // CIDR 표기법 검증
                if (!filter_var($form['ip_address'], FILTER_VALIDATE_IP)) {
                    return '유효한 기본 IP 주소를 입력해주세요.';
                }
                
                if (empty($form['cidr_prefix']) || $form['cidr_prefix'] < 1 || $form['cidr_prefix'] > 32) {
                    return 'CIDR 프리픽스는 1-32 사이의 값이어야 합니다.';
                }
                break;
                
            default:
                return '유효하지 않은 IP 타입입니다.';
        }
        
        // 만료일 검증
        if (!empty($form['expires_at'])) {
            $expiresAt = \Carbon\Carbon::parse($form['expires_at']);
            if ($expiresAt->isPast()) {
                return '만료일은 현재 시간 이후여야 합니다.';
            }
        }
        
        // 추가 정보 설정
        $form['added_by'] = Auth::user()->email;
        $form['access_count'] = 0;
        
        // 타임스탬프
        $form['created_at'] = now();
        $form['updated_at'] = now();
        
        return $form;
    }
    
    /**
     * Hook: 저장 후 처리
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $form 저장된 데이터
     * @return void
     */
    public function hookStored($wire, $form)
    {
        // 캐시 초기화
        IpWhitelistModel::clearCache();
        
        // 로그 기록
        DB::table('admin_user_logs')->insert([
            'action' => 'ip_whitelist_created',
            'target_type' => 'ip_whitelist',
            'target_id' => $form['id'] ?? null,
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => json_encode([
                'ip_address' => $form['ip_address'],
                'type' => $form['type'],
                'description' => $form['description']
            ]),
            'logged_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // 알림 메시지
        $ipDisplay = $this->getIpDisplay($form);
        
        session()->flash('notification', [
            'type' => 'success',
            'title' => 'IP 추가 완료',
            'message' => "{$ipDisplay}가 화이트리스트에 추가되었습니다."
        ]);
    }
    
    /**
     * IP 표시 형식 생성
     * 
     * @param array $form
     * @return string
     */
    private function getIpDisplay($form)
    {
        switch ($form['type']) {
            case 'range':
                return "{$form['ip_range_start']} ~ {$form['ip_range_end']}";
            case 'cidr':
                return "{$form['ip_address']}/{$form['cidr_prefix']}";
            default:
                return $form['ip_address'];
        }
    }
}
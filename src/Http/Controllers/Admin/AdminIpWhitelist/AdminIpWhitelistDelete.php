<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\AdminIpWhitelist as IpWhitelistModel;

/**
 * IP 화이트리스트 삭제 컨트롤러
 */
class AdminIpWhitelistDelete extends Controller
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
     * IP 화이트리스트 삭제 처리
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_ip_whitelist';
        $data = DB::table($tableName)->where('id', $id)->first();
        
        if (!$data) {
            return back()->withErrors(['error' => '데이터를 찾을 수 없습니다.']);
        }
        
        // 트랜잭션 시작
        DB::beginTransaction();
        
        try {
            // 삭제 전 로그 기록
            DB::table('admin_user_logs')->insert([
                'action' => 'ip_whitelist_deleted',
                'target_type' => 'ip_whitelist',
                'target_id' => $id,
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => json_encode([
                    'deleted_ip' => $data->ip_address,
                    'type' => $data->type,
                    'description' => $data->description
                ]),
                'logged_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 데이터 삭제
            DB::table($tableName)->where('id', $id)->delete();
            
            // 캐시 초기화
            IpWhitelistModel::clearCache();
            
            DB::commit();
            
            // 성공 메시지와 함께 리다이렉트
            $redirectRoute = $this->jsonData['route']['name'] ?? 'admin.security.ip-whitelist';
            
            return redirect()->route($redirectRoute)->with('notification', [
                'type' => 'success',
                'title' => '삭제 완료',
                'message' => 'IP가 화이트리스트에서 제거되었습니다.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Hook: 삭제 전 처리
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $ids 삭제할 ID 목록
     * @param string $deleteType 'single' 또는 'multiple'
     * @return bool|string true면 진행, false면 취소, 문자열이면 에러 메시지
     */
    public function hookDeleting($wire, $ids, $deleteType = 'single')
    {
        // 배열이 아닌 경우 처리 (기존 코드와 호환성)
        if (!is_array($ids)) {
            if (is_object($ids)) {
                // 객체인 경우 ID 추출
                $ids = [$ids->id ?? $ids];
            } else {
                $ids = [$ids];
            }
        }
        
        // 현재 사용자의 IP가 삭제 대상에 포함되어 있는지 확인
        $currentIp = request()->ip();
        
        $ipsToDelete = DB::table('admin_ip_whitelist')
            ->whereIn('id', $ids)
            ->get();
            
        foreach ($ipsToDelete as $ipEntry) {
            $model = new IpWhitelistModel((array) $ipEntry);
            
            if ($model->matchesIp($currentIp)) {
                return '현재 접속 중인 IP는 삭제할 수 없습니다.';
            }
        }
        
        // 활성 IP가 1개만 남은 경우 경고
        $activeCount = DB::table('admin_ip_whitelist')
            ->where('is_active', true)
            ->whereNotIn('id', $ids)
            ->count();
            
        if ($activeCount === 0) {
            return '최소 1개 이상의 활성 IP가 필요합니다.';
        }
        
        return true;
    }
    
    /**
     * Hook: 삭제 후 처리
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $ids 삭제된 ID 목록
     * @return void
     */
    public function hookDeleted($wire, $ids)
    {
        // 캐시 초기화
        IpWhitelistModel::clearCache();
        
        // 대량 삭제 로그
        if (count($ids) > 1) {
            DB::table('admin_user_logs')->insert([
                'action' => 'ip_whitelist_bulk_deleted',
                'target_type' => 'ip_whitelist',
                'target_id' => null,
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => json_encode([
                    'deleted_count' => count($ids),
                    'deleted_ids' => $ids
                ]),
                'logged_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $count = count($ids);
        session()->flash('notification', [
            'type' => 'success',
            'title' => '삭제 완료',
            'message' => "{$count}개의 IP가 화이트리스트에서 제거되었습니다."
        ]);
    }
}
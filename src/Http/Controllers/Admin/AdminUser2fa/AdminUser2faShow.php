<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUser2fa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\admin\App\Services\JsonConfigService;
use Jiny\Admin\Services\TwoFactorAuthService;
use Jiny\Admin\Models\User;

/**
 * AdminUser2faShow Controller
 * 
 * 2FA 상세 정보 표시 및 관리 기능을 제공합니다.
 * TwoFactorAuthService를 사용하여 2FA 관련 작업을 처리합니다.
 */
class AdminUser2faShow extends Controller
{
    private $jsonData;
    private $twoFactorService;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
        
        // 2FA 서비스 초기화
        $this->twoFactorService = new TwoFactorAuthService();
    }

    /**
     * Single Action __invoke method
     * 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_user2fas';
        $query = DB::table($tableName);

        // 기본 where 조건 적용
        if (isset($this->jsonData['table']['where']['default'])) {
            foreach ($this->jsonData['table']['where']['default'] as $condition) {
                if (count($condition) === 3) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                } elseif (count($condition) === 2) {
                    $query->where($condition[0], $condition[1]);
                }
            }
        }

        $item = $query->where('user_id', $id)->first();
        
        // User 정보도 함께 조회
        $user = User::find($id);
        
        if (!$user) {
            $redirectUrl = isset($this->jsonData['route']['name'])
                ? route($this->jsonData['route']['name'].'.index')
                : route('admin.user.2fa');

            return redirect($redirectUrl)
                ->with('error', '사용자를 찾을 수 없습니다.');
        }

        // 2FA 데이터가 없으면 기본 데이터 생성
        if (!$item) {
            $data = [
                'user_id' => $id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'enabled' => false,
                'method' => null,
                'last_used_at' => null,
                'backup_codes_used' => 0,
                'created_at' => null,
                'updated_at' => null,
            ];
        } else {
            // 객체를 배열로 변환
            $data = (array) $item;
            $data['user_name'] = $user->name;
            $data['user_email'] = $user->email;
        }

        // 2FA 상태 정보 추가
        $twoFactorStatus = $this->twoFactorService->getStatus($user);
        $data['two_factor_status'] = $twoFactorStatus;

        // Apply hookShowing if exists
        if (method_exists($this, 'hookShowing')) {
            $data = $this->hookShowing(null, $data);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUser2fa.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // Set title from data or use default
        $title = $user->name . '님의 2FA 설정';

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['show'], [
            'controllerClass' => static::class,
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $data,
            'id' => $id,
            'title' => $title,
            'subtitle' => '2FA 상세 정보 및 설정',
        ]);
    }

    /**
     * 상세보기 표시 전에 호출됩니다.
     */
    public function hookShowing($wire, $data)
    {
        // 날짜 형식 지정
        $dateFormat = $this->jsonData['show']['display']['datetimeFormat'] ?? 'Y-m-d H:i:s';

        if (isset($data['created_at']) && $data['created_at']) {
            $data['created_at_formatted'] = date($dateFormat, strtotime($data['created_at']));
        }

        if (isset($data['updated_at']) && $data['updated_at']) {
            $data['updated_at_formatted'] = date($dateFormat, strtotime($data['updated_at']));
        }
        
        if (isset($data['last_used_at']) && $data['last_used_at']) {
            $data['last_used_at_formatted'] = date($dateFormat, strtotime($data['last_used_at']));
        }

        // Boolean 라벨 처리
        $booleanLabels = $this->jsonData['show']['display']['booleanLabels'] ?? [
            'true' => '활성화',
            'false' => '비활성화',
        ];

        if (isset($data['enabled'])) {
            $data['enabled_label'] = $data['enabled'] ? $booleanLabels['true'] : $booleanLabels['false'];
        }
        
        // 2FA 상태 라벨
        if (isset($data['two_factor_status'])) {
            $status = $data['two_factor_status'];
            $data['status_text'] = $status['enabled'] ? '활성화됨' : '비활성화됨';
            $data['status_color'] = $status['enabled'] ? 'green' : 'gray';
        }

        return $data;
    }

    /**
     * Hook: 조회 후 데이터 가공
     */
    public function hookShowed($wire, $data)
    {
        return $data;
    }
    
    /**
     * Hook: 2FA 설정 페이지로 이동
     */
    public function hookCustomEnableTwoFactor($wire, $params)
    {
        $userId = $params['id'] ?? null;
        
        if (!$userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');
            return;
        }
        
        // Edit 페이지로 리다이렉트
        return redirect()->route('admin.user.2fa.edit', $userId);
    }
    
    /**
     * Hook: 2FA 비활성화
     */
    public function hookCustomDisableTwoFactor($wire, $params)
    {
        $userId = $params['id'] ?? null;
        
        if (!$userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');
            return;
        }
        
        try {
            $user = User::findOrFail($userId);
            
            // 2FA 비활성화
            $this->twoFactorService->disableTwoFactor($user, true);
            
            session()->flash('success', '2FA가 비활성화되었습니다.');
            
            // Livewire 컴포넌트 새로고침
            if ($wire) {
                $wire->refreshData();
            }
            
        } catch (\Exception $e) {
            session()->flash('error', '2FA 비활성화 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * Hook: 백업 코드 재생성
     */
    public function hookCustomRegenerateBackupCodes($wire, $params)
    {
        $userId = $params['id'] ?? null;
        
        if (!$userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');
            return;
        }
        
        try {
            $user = User::findOrFail($userId);
            
            if (!$user->two_factor_enabled) {
                session()->flash('error', '2FA가 활성화되어 있지 않습니다.');
                return;
            }
            
            // 백업 코드 재생성
            $backupCodes = $this->twoFactorService->regenerateBackupCodes($user);
            
            // 세션에 저장하여 표시
            session(['regenerated_backup_codes_' . $userId => $backupCodes]);
            
            session()->flash('success', '백업 코드가 재생성되었습니다.');
            
            // Livewire 컴포넌트 새로고침
            if ($wire) {
                $wire->refreshData();
            }
            
        } catch (\Exception $e) {
            session()->flash('error', '백업 코드 재생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * Hook: 2FA 사용 로그 보기
     */
    public function hookCustomViewLogs($wire, $params)
    {
        $userId = $params['id'] ?? null;
        
        if (!$userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');
            return;
        }
        
        // 2FA 관련 로그 조회
        $logs = DB::table('admin_user_logs')
            ->where('user_id', $userId)
            ->where('action', 'like', '2fa_%')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 로그를 세션에 저장하여 표시
        session(['2fa_logs_' . $userId => $logs]);
        
        session()->flash('info', '최근 2FA 활동 로그를 불러왔습니다.');
        
        // Livewire 컴포넌트 새로고침
        if ($wire) {
            $wire->refreshData();
        }
    }
}

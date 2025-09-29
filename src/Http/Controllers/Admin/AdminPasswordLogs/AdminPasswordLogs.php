<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminPasswordLogs Main Controller
 *
 * 비밀번호 시도 로그 및 차단 관리
 */
class AdminPasswordLogs extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    // private function loadJsonFromCurrentPath()
    // {
    //     try {
    //         $jsonFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminPasswordLogs.json';

    //         if (!file_exists($jsonFilePath)) {
    //             return $this->getDefaultJsonData();
    //         }

    //         $jsonContent = file_get_contents($jsonFilePath);
    //         $jsonData = json_decode($jsonContent, true);

    //         if (json_last_error() !== JSON_ERROR_NONE) {
    //             return $this->getDefaultJsonData();
    //         }

    //         return $jsonData;

    //     } catch (\Exception $e) {
    //         return $this->getDefaultJsonData();
    //     }
    // }

    // private function getDefaultJsonData()
    // {
    //     return [
    //         'title' => 'Password Security Logs',
    //         'subtitle' => 'Monitor failed password attempts and blocked IPs',
    //         'route' => [
    //             'name' => 'admin.user.password.logs'
    //         ],
    //         'table' => [
    //             'name' => 'admin_password_logs',
    //             'model' => '\\Jiny\\Admin\\App\\Models\\AdminPasswordLog'
    //         ],
    //         'template' => [
    //             'layout' => 'jiny-admin::layouts.admin',
    //             'index' => 'jiny-admin::template.index'
    //         ],
    //         'index' => [
    //             'features' => [
    //                 'enableCreate' => false,
    //                 'enableDelete' => true,
    //                 'enableEdit' => false,
    //                 'enableSearch' => true,
    //                 'enableSort' => true,
    //                 'enablePagination' => true,
    //                 'enableUnblock' => true
    //             ]
    //         ]
    //     ];
    // }

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminPasswordLogs.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정
        $this->jsonData['currentRoute'] = 'admin.user.password.logs';

        // 쿼리 스트링 파라미터를 jsonData에 동적으로 추가
        $queryParams = $request->query();
        if (! empty($queryParams)) {
            // 동적 쿼리 조건을 위한 키 추가
            $this->jsonData['queryConditions'] = [];

            // status 파라미터 처리
            if (isset($queryParams['status'])) {
                $this->jsonData['queryConditions']['status'] = $queryParams['status'];
                $this->jsonData['index']['filters']['status']['value'] = $queryParams['status'];
                $this->jsonData['index']['defaultFilters'] = ['status' => $queryParams['status']];
            }

            // 다른 쿼리 파라미터들도 처리 가능
            foreach (['email', 'ip_address', 'date_from', 'date_to'] as $param) {
                if (isset($queryParams[$param])) {
                    $this->jsonData['queryConditions'][$param] = $queryParams[$param];
                }
            }
        }

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'title' => $this->jsonData['title'] ?? 'Password Security Logs',
            'subtitle' => $this->jsonData['subtitle'] ?? 'Monitor failed password attempts and blocked IPs',
        ]);
    }

    /**
     * Hook for unblocking an IP address
     */
    public function hookUnblock($wire, $id)
    {
        try {
            $log = DB::table('admin_password_logs')->where('id', $id)->first();

            if (! $log) {
                return '로그를 찾을 수 없습니다.';
            }

            if ($log->status !== 'blocked') {
                return '이미 차단 해제되었거나 차단되지 않은 IP입니다.';
            }

            // Update status to resolved
            DB::table('admin_password_logs')
                ->where('id', $id)
                ->update([
                    'status' => 'resolved',
                    'unblocked_at' => now(),
                    'is_blocked' => false,
                    'updated_at' => now(),
                ]);

            // Clear any related blocked entries for this IP
            DB::table('admin_password_logs')
                ->where('ip_address', $log->ip_address)
                ->where('status', 'blocked')
                ->update([
                    'status' => 'resolved',
                    'unblocked_at' => now(),
                    'is_blocked' => false,
                    'updated_at' => now(),
                ]);

            return ['success' => true, 'message' => 'IP 차단이 해제되었습니다.'];

        } catch (\Exception $e) {
            return '차단 해제 중 오류가 발생했습니다: '.$e->getMessage();
        }
    }

    /**
     * Hook for bulk unblocking
     */
    public function hookBulkUnblock($wire, $ids)
    {
        try {
            $count = DB::table('admin_password_logs')
                ->whereIn('id', $ids)
                ->where('status', 'blocked')
                ->update([
                    'status' => 'resolved',
                    'unblocked_at' => now(),
                    'is_blocked' => false,
                    'updated_at' => now(),
                ]);

            return ['success' => true, 'message' => $count.'개의 IP 차단이 해제되었습니다.'];

        } catch (\Exception $e) {
            return '차단 해제 중 오류가 발생했습니다: '.$e->getMessage();
        }
    }

    /**
     * Hook: IP 차단 해제 (커스텀 Hook)
     */
    public function hookCustomUnblockIP($wire, $params)
    {
        $id = $params['id'] ?? null;

        if (! $id) {
            session()->flash('error', 'ID가 필요합니다.');

            return false;
        }

        try {
            $log = DB::table('admin_password_logs')->where('id', $id)->first();

            if (! $log) {
                session()->flash('error', '로그를 찾을 수 없습니다.');

                return false;
            }

            if ($log->status !== 'blocked') {
                session()->flash('warning', '이미 차단 해제되었거나 차단되지 않은 IP입니다.');

                return false;
            }

            // 해당 이메일의 사용자 찾기
            $user = DB::table('users')->where('email', $log->email)->first();

            if ($user) {
                // 사용자의 로그인 실패 횟수 초기화
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'failed_login_attempts' => 0,
                        'account_locked_until' => null,
                        'updated_at' => now(),
                    ]);
            }

            // 로그 상태 업데이트
            DB::table('admin_password_logs')
                ->where('id', $id)
                ->update([
                    'status' => 'resolved',
                    'unblocked_at' => now(),
                    'is_blocked' => false,
                    'updated_at' => now(),
                ]);

            // 동일 IP의 다른 차단 로그도 해제
            DB::table('admin_password_logs')
                ->where('ip_address', $log->ip_address)
                ->where('status', 'blocked')
                ->update([
                    'status' => 'resolved',
                    'unblocked_at' => now(),
                    'is_blocked' => false,
                    'updated_at' => now(),
                ]);

            // 활동 로그 기록 (admin_user_logs 테이블)
            DB::table('admin_user_logs')->insert([
                'user_id' => $user->id ?? null,
                'email' => $log->email,
                'name' => $user->name ?? null,
                'action' => 'password_unblock',
                'details' => "IP {$log->ip_address} 차단 해제 by Admin",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            // Livewire 컴포넌트에 데이터 새로고침 요청
            if ($wire) {
                // getRowsProperty를 호출하여 데이터 새로고침
                $wire->dispatch('refresh-table');

                // 성공 메시지 표시를 위한 이벤트
                $wire->dispatch('show-message', [
                    'type' => 'success',
                    'message' => "IP {$log->ip_address} 차단이 해제되었습니다.",
                ]);
            }

            session()->flash('success', "IP {$log->ip_address} 차단이 해제되었습니다.");

            return true;

        } catch (\Exception $e) {
            \Log::error('hookCustomUnblockIP Error: '.$e->getMessage());
            session()->flash('error', '차단 해제 중 오류가 발생했습니다: '.$e->getMessage());

            return false;
        }
    }
}

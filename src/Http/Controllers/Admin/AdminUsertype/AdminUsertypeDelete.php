<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUsertype;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jiny\admin\App\Services\JsonConfigService;

/**
 * AdminUsertypeDelete Controller
 */
class AdminUsertypeDelete extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 삭제 확인 및 처리
     */
    public function __invoke(Request $request, $id)
    {
        if ($request->isMethod('delete') || $request->isMethod('post')) {
            return $this->destroy($request, $id);
        }

        return $this->confirm($request, $id);
    }

    /**
     * 삭제 확인 화면 표시
     */
    public function confirm(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_usertypes';
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

        $data = $query->where('id', $id)->first();

        if (! $data) {
            return redirect($this->getRedirectUrl())
                ->with('error', 'Usertype을(를) 찾을 수 없습니다.');
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.delete view 경로 확인 (delete 템플릿이 없을 수도 있음)
        $viewPath = $this->jsonData['template']['delete'] ??
                    'jiny-admin::admin.admin_usertype.delete';

        return view($viewPath, [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id,
            'confirmMessage' => $this->jsonData['delete']['confirmation']['message'] ??
                              'Are you sure you want to delete this item?',
            'requireConfirmation' => $this->jsonData['delete']['requireConfirmation'] ?? true,
        ]);
    }

    /**
     * 데이터 삭제
     */
    public function destroy(Request $request, $id)
    {
        // 삭제 확인 검증
        $requireConfirmation = $this->jsonData['delete']['requireConfirmation'] ?? true;

        if ($requireConfirmation && ! $request->has('confirm_delete')) {
            return redirect()->back()
                ->with('error', $this->jsonData['destroy']['messages']['confirmRequired'] ??
                              'Delete confirmation required.');
        }

        // 삭제할 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_usertypes';
        $data = DB::table($tableName)
            ->where('id', $id)
            ->first();

        if (! $data) {
            return redirect($this->getRedirectUrl())
                ->with('error', 'Usertype을(를) 찾을 수 없습니다.');
        }

        // 삭제 전 훅 실행
        $canDelete = $this->hookDeleting(null, $data);

        if ($canDelete === false) {
            return redirect($this->getRedirectUrl())
                ->with('error', '이 항목은 삭제할 수 없습니다.');
        }

        // 트랜잭션 처리
        $enableTransaction = $this->jsonData['delete']['enableTransaction'] ?? true;

        if ($enableTransaction) {
            DB::beginTransaction();
        }

        try {
            // 데이터베이스에서 삭제
            DB::table($tableName)
                ->where('id', $id)
                ->delete();

            // 로깅 처리
            $this->logDeletion($data);

            // 삭제 후 훅 실행
            $this->hookDeleted(null, $data);

            if ($enableTransaction) {
                DB::commit();
            }

            // 성공 메시지와 함께 목록으로 리다이렉트
            $message = $this->jsonData['destroy']['messages']['success'] ??
                      'Usertype이(가) 성공적으로 삭제되었습니다.';

            return redirect($this->getRedirectUrl())
                ->with('success', $message);

        } catch (\Exception $e) {
            if ($enableTransaction) {
                DB::rollBack();
            }

            $errorMessage = sprintf(
                $this->jsonData['destroy']['messages']['error'] ??
                'Error deleting usertype: %s',
                $e->getMessage()
            );

            return redirect($this->getRedirectUrl())
                ->with('error', $errorMessage);
        }
    }

    /**
     * 데이터 삭제 전에 호출됩니다.
     * false를 반환하면 삭제가 취소됩니다.
     */
    public function hookDeleting($wire, $data)
    {
        // 삭제 가능 여부 체크
        return true;
    }

    /**
     * 데이터 삭제 후에 호출됩니다.
     */
    public function hookDeleted($wire, $data)
    {
        // 필요시 추가 작업 수행
        return $data;
    }

    /**
     * 삭제 로그 기록
     */
    private function logDeletion($data)
    {
        $loggingConfig = $this->jsonData['delete']['logging'] ?? [];

        if (! ($loggingConfig['enabled'] ?? false)) {
            return;
        }

        $channel = $loggingConfig['channel'] ?? 'admin';
        $level = $loggingConfig['level'] ?? 'info';

        $logData = [
            'action' => 'delete_usertype',
            'item_id' => $data->id,
            'item_title' => $data->title ?? $data->name ?? 'Unknown',
        ];

        if ($loggingConfig['includeUser'] ?? true) {
            $logData['user_id'] = Auth::id();
            $logData['user_email'] = Auth::user()->email ?? null;
        }

        if ($loggingConfig['includeIp'] ?? true) {
            $logData['ip_address'] = request()->ip();
        }

        Log::channel($channel)->$level('Usertype deleted', $logData);
    }

    /**
     * 리다이렉트 URL 가져오기
     */
    private function getRedirectUrl()
    {
        if (isset($this->jsonData['route']['name'])) {
            return route($this->jsonData['route']['name'].'.index');
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            return route($this->jsonData['route'].'.index');
        }

        return '/admin/usertype';
    }
}

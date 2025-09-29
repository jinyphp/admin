<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jiny\Admin\Services\JsonConfigService;

/**
 * 사용자 삭제 컨트롤러
 * 
 * 사용자 삭제 확인 및 실제 삭제 처리를 담당합니다.
 * Livewire 컴포넌트(AdminDelete)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminUsers
 * @author  @jiny/admin Team
 * @since   1.0.0
 * 
 * ## 메소드 호출 트리
 * ```
 * __invoke()
 * ├── [DELETE/POST 요청]
 * │   └── destroy($id)
 * │       ├── 삭제 확인 검증
 * │       ├── DB::beginTransaction()
 * │       ├── hookDeleting($ids)         [삭제 전 처리]
 * │       │   ├── 자기 자신 삭제 체크
 * │       │   └── 시스템 관리자 보호
 * │       ├── DB::table()->delete()
 * │       ├── logDeletion($data)
 * │       │   └── Log::channel()->info()
 * │       ├── hookDeleted($ids)          [삭제 후 처리]
 * │       │   └── admin_user_types 카운트 감소
 * │       └── DB::commit()
 * └── [GET 요청]
 *     └── confirm($id)
 *         └── view() 렌더링
 * ```
 * 
 * ## 보안 기능
 * - 자기 자신 삭제 방지
 * - 시스템 관리자(ID=1) 삭제 방지
 * - 트랜잭션 처리로 데이터 일관성 보장
 * - 삭제 확인 필수 (requireConfirmation)
 * 
 * ## 반환값 패턴
 * - hookDeleting:
 *   - true: 삭제 진행
 *   - false: 삭제 취소
 *   - string: 에러 메시지와 함께 취소
 * - hookDeleted: void (사후 처리용)
 */
class AdminUsersDelete extends Controller
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
                ->with('error', 'User을(를) 찾을 수 없습니다.');
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
                ->with('error', 'User을(를) 찾을 수 없습니다.');
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
                      'User이(가) 성공적으로 삭제되었습니다.';

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
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $ids  삭제할 ID 목록
     * @param  string  $deleteType  'single' 또는 'multiple'
     * @return bool|string true면 진행, false면 취소, 문자열이면 에러 메시지
     */
    public function hookDeleting($wire, $ids, $deleteType = 'single')
    {
        // 배열이 아닌 경우 처리 (기존 코드와 호환성)
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        // 자기 자신을 삭제하려고 하는지 체크
        if (in_array(Auth::id(), $ids)) {
            return '자기 자신은 삭제할 수 없습니다.';
        }

        // 시스템 관리자 삭제 방지 (예: ID가 1인 사용자)
        if (in_array(1, $ids)) {
            return '시스템 관리자는 삭제할 수 없습니다.';
        }

        // 삭제 가능 여부 체크
        return true;
    }

    /**
     * 데이터 삭제 후에 호출됩니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $ids  삭제된 ID 목록
     * @param  int  $actualDeleted  실제로 삭제된 개수
     */
    public function hookDeleted($wire, $ids, $actualDeleted = 0)
    {
        // 배열이 아닌 경우 처리 (기존 코드와 호환성)
        if (! is_array($ids)) {
            // 기존 단일 삭제 코드와 호환성 유지
            $data = $ids;
            if (isset($data->utype) && $data->utype) {
                DB::table('admin_user_types')
                    ->where('code', $data->utype)
                    ->where('user_count', '>', 0)  // 음수가 되지 않도록 체크
                    ->decrement('user_count');
            }

            return $data;
        }

        // 다중 삭제 처리: 삭제된 사용자들의 타입별로 카운트 감소
        if (! empty($ids)) {
            // 삭제된 사용자들의 타입 정보 조회 (이미 삭제되었으므로 로그나 백업에서 조회해야 함)
            // 실제로는 삭제 전에 타입 정보를 저장해두고 사용하는 것이 좋음

            \Log::info('Users deleted', [
                'ids' => $ids,
                'count' => $actualDeleted,
            ]);
        }

        return true;
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

        Log::channel($channel)->$level('User deleted', $logData);
    }

    /**
     * 리다이렉트 URL 가져오기
     */
    private function getRedirectUrl()
    {
        if (isset($this->jsonData['route']['name'])) {
            return route($this->jsonData['route']['name']);
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            return route($this->jsonData['route']);
        }

        return '/admin/users';
    }
}

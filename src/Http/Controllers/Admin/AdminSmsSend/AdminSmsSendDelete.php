<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsSend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jiny\Admin\Services\JsonConfigService;

/**
 * admin 삭제 컨트롤러
 * 
 * admin 삭제 확인 및 실제 삭제 처리를 담당합니다.
 * Livewire 컴포넌트(AdminDelete)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsSend
 * @since   1.0.0
 */
class AdminSmsSendDelete extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 삭제 확인 및 처리
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  mixed    $id       삭제할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        if ($request->isMethod('delete') || $request->isMethod('post')) {
            return $this->destroy($request, $id);
        }

        return $this->confirm($request, $id);
    }

    /**
     * 삭제 확인 페이지 표시
     *
     * @param  Request  $request
     * @param  mixed    $id
     * @return \Illuminate\View\View
     */
    protected function confirm(Request $request, $id)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? null;
        if (!$tableName) {
            return response('Error: 테이블 설정이 필요합니다.', 500);
        }

        $data = DB::table($tableName)->where('id', $id)->first();
        
        if (!$data) {
            return response('Error: 데이터를 찾을 수 없습니다.', 404);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsSend.json';
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['delete'] ?? 'jiny-admin::template.delete', [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'data' => $data,
            'id' => $id,
        ]);
    }

    /**
     * 실제 삭제 처리
     *
     * @param  Request  $request
     * @param  mixed    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function destroy(Request $request, $id)
    {
        // 삭제 확인 검증
        if (isset($this->jsonData['destroy']['requireConfirmation']) && 
            $this->jsonData['destroy']['requireConfirmation'] === true) {
            if (!$request->has('confirm') || $request->input('confirm') !== 'true') {
                return back()->withErrors(['confirm' => '삭제 확인이 필요합니다.']);
            }
        }

        $tableName = $this->jsonData['table']['name'] ?? null;
        if (!$tableName) {
            return back()->withErrors(['error' => '테이블 설정이 없습니다.']);
        }

        // 트랜잭션 시작
        DB::beginTransaction();
        try {
            // 삭제 전 Hook
            $result = $this->hookDeleting(null, [$id]);
            if ($result === false || is_string($result)) {
                DB::rollBack();
                $errorMessage = is_string($result) ? $result : '삭제가 취소되었습니다.';
                return back()->withErrors(['error' => $errorMessage]);
            }

            // 데이터 조회 (로깅용)
            $data = DB::table($tableName)->where('id', $id)->first();
            
            // 삭제 실행
            DB::table($tableName)->where('id', $id)->delete();

            // 삭제 로그
            $this->logDeletion($tableName, $id, $data);

            // 삭제 후 Hook
            $this->hookDeleted(null, [$id]);

            DB::commit();

            // 성공 메시지
            $successMessage = $this->jsonData['destroy']['messages']['success'] ?? '삭제되었습니다.';
            $redirectRoute = $this->jsonData['route']['name'] ?? 'admin.dashboard';
            
            return redirect()->route($redirectRoute)->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = $this->jsonData['destroy']['messages']['error'] ?? '삭제 중 오류가 발생했습니다: %s';
            $errorMessage = sprintf($errorMessage, $e->getMessage());
            
            return back()->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Hook: 삭제 전 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $ids   삭제할 ID 목록
     * @return bool|string true면 진행, false나 문자열이면 중단
     */
    public function hookDeleting($wire, $ids)
    {
        // 삭제 가능 여부 검증
        return true;
    }

    /**
     * Hook: 삭제 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $ids   삭제된 ID 목록
     * @return void
     */
    public function hookDeleted($wire, $ids)
    {
        // 관련 데이터 정리 등
    }

    /**
     * 삭제 로그 기록
     *
     * @param  string  $table
     * @param  mixed   $id
     * @param  mixed   $data
     * @return void
     */
    protected function logDeletion($table, $id, $data)
    {
        Log::channel('admin')->info('Data deleted', [
            'table' => $table,
            'id' => $id,
            'data' => $data,
            'deleted_by' => Auth::id(),
            'ip' => request()->ip(),
        ]);
    }
}
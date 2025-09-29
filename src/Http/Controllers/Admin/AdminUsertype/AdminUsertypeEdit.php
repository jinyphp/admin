<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUsertype;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\admin\App\Services\JsonConfigService;

/**
 * AdminUsertypeEdit Controller
 */
class AdminUsertypeEdit extends Controller
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
     * 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_usertypes';
        $data = DB::table($tableName)
            ->where('id', $id)
            ->first();

        if (! $data) {
            if (isset($this->jsonData['route']['name'])) {
                $redirectUrl = route($this->jsonData['route']['name'].'.index');
            } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
                $redirectUrl = route($this->jsonData['route'].'.index');
            } else {
                $redirectUrl = '/admin/usertype';
            }

            return redirect($redirectUrl)
                ->with('error', 'Usertype을(를) 찾을 수 없습니다.');
        }

        // 객체를 배열로 변환
        $form = (array) $data;

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.edit view 경로 확인
        if (! isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUsertype.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        return view($this->jsonData['template']['edit'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'form' => $form,
            'id' => $id,
            'title' => 'Edit Usertype',
            'subtitle' => 'Usertype을(를) 수정합니다.',
        ]);
    }

    /**
     * 수정폼이 실행될때 호출됩니다.
     */
    public function hookEditing($wire, $form)
    {
        // enable 필드를 boolean으로 변환
        $form['enable'] = (bool) ($form['enable'] ?? false);

        return $form;
    }

    /**
     * 데이터 업데이트 전에 호출됩니다.
     */
    public function hookUpdating($wire, $form)
    {
        // enable 필드 처리 (체크박스)
        $form['enable'] = isset($form['enable']) ? 1 : 0;

        // ID 제거 (업데이트 시 필요 없음)
        unset($form['id']);
        unset($form['_token']);
        unset($form['_method']);

        // updated_at 타임스탬프 업데이트
        $form['updated_at'] = now();

        return $form;
    }

    /**
     * 데이터 업데이트 후에 호출됩니다.
     */
    public function hookUpdated($wire, $form)
    {
        // 필요시 추가 작업 수행
        return $form;
    }
}

<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminWebhooklogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * Webhooklogs 수정 컨트롤러
 * 
 * 기존 Webhooklogs 정보를 수정하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminEdit)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminWebhooklogs
 * @since   1.0.0
 */
class AdminWebhooklogsEdit extends Controller
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
     * 수정 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  수정할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_webhooklogs';
        $data = DB::table($tableName)
            ->where('id', $id)
            ->first();

        if (! $data) {
            if (isset($this->jsonData['route']['name'])) {
                $redirectUrl = route($this->jsonData['route']['name']);
            } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
                $redirectUrl = route($this->jsonData['route']);
            } else {
                $redirectUrl = '/admin/webhooklogs';
            }

            return redirect($redirectUrl)
                ->with('error', 'Webhooklogs을(를) 찾을 수 없습니다.');
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
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminWebhooklogs.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['edit'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'form' => $form,
            'id' => $id,
        ]);
    }

    /**
     * Hook: 수정폼이 실행될 때 호출
     *
     * 폼 데이터를 초기화하거나 필요한 데이터를 준비합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  현재 폼 데이터
     * @return array 초기화된 폼 데이터
     */
    public function hookEditing($wire, $form)
    {
        // 필요시 데이터 가공
        return $form;
    }

    /**
     * Hook: 데이터 업데이트 전 호출
     *
     * 데이터 검증 및 가공을 수행합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  폼 데이터
     * @return array|string 성공시 수정된 form 배열, 실패시 에러 메시지 문자열
     */
    public function hookUpdating($wire, $form)
    {
        // 불필요한 필드 제거
        unset($form['_token']);
        unset($form['_method']);

        // updated_at 타임스탬프 갱신
        $form['updated_at'] = now();

        // 성공: 배열 반환
        return $form;
    }

    /**
     * Hook: 데이터 업데이트 후 호출
     *
     * 추가 처리가 필요한 경우 구현합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  업데이트된 데이터
     * @return array 처리된 데이터
     */
    public function hookUpdated($wire, $form)
    {
        return $form;
    }

    /**
     * Hook: 폼 필드 변경시 실시간 검증
     * 
     * hookForm{FieldName} 형태로 각 필드별 검증 메소드를 추가할 수 있습니다.
     * 예: hookFormEmail, hookFormName 등
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  mixed  $value  입력된 값
     * @param  string  $fieldName  필드명
     * @return void
     */
    // public function hookFormFieldName($wire, $value, $fieldName)
    // {
    //     // 필드별 실시간 검증 로직
    // }
}
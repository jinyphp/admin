<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsSend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\admin\App\Services\JsonConfigService;

/**
 * admin 수정 컨트롤러
 * 
 * 기존 admin 정보를 수정하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminEdit)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsSend
 * @since   1.0.0
 */
class AdminSmsSendEdit extends Controller
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
     * 수정 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  mixed    $id       수정할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? null;
        if (!$tableName) {
            return response('Error: 테이블 설정이 필요합니다.', 500);
        }

        $data = DB::table($tableName)->where('id', $id)->first();
        
        if (!$data) {
            return response('Error: 데이터를 찾을 수 없습니다.', 404);
        }

        // 객체를 배열로 변환하여 $form 변수 생성
        $form = (array) $data;

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // template.edit view 경로 확인
        if (!isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsSend.json';
        $settingsPath = $jsonPath;

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        // SMS 제공업체 목록 조회
        $providers = DB::table('admin_sms_providers')
            ->where('is_active', 1)
            ->orderBy('priority', 'asc')
            ->get();

        return view($this->jsonData['template']['edit'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'data' => $data,
            'form' => $form,
            'id' => $id,
            'providers' => $providers,
            'is_scheduled' => isset($form['scheduled_at']) && $form['scheduled_at'],
        ]);
    }

    /**
     * Hook: 수정 폼 초기화
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  기존 데이터
     * @return array
     */
    public function hookEditing($wire, $form)
    {
        // 데이터 가공
        return $form;
    }

    /**
     * Hook: 업데이트 전 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  수정된 데이터
     * @return array|string 성공시 배열, 실패시 에러 메시지
     */
    public function hookUpdating($wire, $form)
    {
        // 데이터 가공 및 검증
        return $form;
    }

    /**
     * Hook: 업데이트 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  업데이트된 데이터
     * @return void
     */
    public function hookUpdated($wire, $form)
    {
        // 후처리 로직
    }
}
<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsSend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * admin 상세 정보 표시 컨트롤러
 * 
 * admin 상세 정보 표시 및 다양한 관리 작업을 처리합니다.
 * Livewire 컴포넌트(AdminShow)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsSend
 * @since   1.0.0
 */
class AdminSmsSendShow extends Controller
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
     * 상세 정보 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  mixed    $id       조회할 레코드 ID
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

        $query = DB::table($tableName);

        // 기본 where 조건 적용
        if (isset($this->jsonData['table']['where']['default'])) {
            foreach ($this->jsonData['table']['where']['default'] as $condition) {
                $query->where($condition[0], $condition[1], $condition[2] ?? '=');
            }
        }

        $data = $query->where('id', $id)->first();
        
        if (!$data) {
            return response('Error: 데이터를 찾을 수 없습니다.', 404);
        }

        // 관련 데이터 조회 (provider 정보)
        if (isset($data->provider_id) && $data->provider_id) {
            $provider = DB::table('admin_sms_providers')
                ->where('id', $data->provider_id)
                ->first();
            if ($provider) {
                $data->provider_name = $provider->provider_name;
            }
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // Livewire 컴포넌트 사용 확인
        $showLayoutPath = $this->jsonData['show']['showLayoutPath'] ?? null;
        
        if ($showLayoutPath) {
            // Livewire 컴포넌트 사용
            // JSON 파일 경로 추가
            $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsSend.json';
            $settingsPath = $jsonPath;

            // 현재 컨트롤러 클래스를 JSON 데이터에 추가
            $this->jsonData['controllerClass'] = get_class($this);

            return view('jiny-admin::template.show', [
                'jsonData' => $this->jsonData,
                'jsonPath' => $jsonPath,
                'settingsPath' => $settingsPath,
                'controllerClass' => static::class,
                'data' => $data,
                'itemId' => $id,
            ]);
        } else {
            // 기존 template.show 사용
            if (!isset($this->jsonData['template']['show'])) {
                return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
            }

            // JSON 파일 경로 추가
            $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsSend.json';
            $settingsPath = $jsonPath;

            // 현재 컨트롤러 클래스를 JSON 데이터에 추가
            $this->jsonData['controllerClass'] = get_class($this);

            return view($this->jsonData['template']['show'], [
                'jsonData' => $this->jsonData,
                'jsonPath' => $jsonPath,
                'settingsPath' => $settingsPath,
                'controllerClass' => static::class,
                'data' => $data,
                'id' => $id,
            ]);
        }
    }

    /**
     * Hook: 표시 전 데이터 가공
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  mixed  $data  표시할 데이터
     * @return mixed
     */
    public function hookShowing($wire, $data)
    {
        // 날짜 형식 변환, Boolean 값 라벨 변환 등
        return $data;
    }

    /**
     * Hook: 표시 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  mixed  $data  표시된 데이터
     * @return void
     */
    public function hookShowed($wire, $data)
    {
        // 조회 로그 기록 등
    }
}
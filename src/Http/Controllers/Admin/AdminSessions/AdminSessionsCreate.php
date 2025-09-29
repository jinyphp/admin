<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSessions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminSessionsCreate Controller
 */
class AdminSessionsCreate extends Controller
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
     * 생성 폼 표시
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 기본값 설정
        $form = [
            'enable' => false,
        ];

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.create view 경로 확인
        if (! isset($this->jsonData['template']['create'])) {
            $debugInfo = 'JSON template section: '.json_encode($this->jsonData['template'] ?? 'not found');

            return response('Error: 화면을 출력하기 위한 template.create 설정이 필요합니다. '.$debugInfo, 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminSessions.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'form' => $form,
            'title' => 'Create New Sessions',
            'subtitle' => '새로운 Sessions을(를) 생성합니다.',
        ]);
    }

    /**
     * 생성폼이 실행될때 호출됩니다.
     */
    public function hookCreating($wire, $value)
    {
        // 기본값 설정
        $defaults = $this->jsonData['create']['defaults'] ??
                   $this->jsonData['store']['defaults'] ?? [];

        $form = array_merge([
            'enable' => true,
        ], $defaults);

        return $form;
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire, $form)
    {
        // enable 필드 처리 (체크박스)
        $form['enable'] = isset($form['enable']) ? 1 : 0;

        // 불필요한 필드 제거
        unset($form['_token']);
        unset($form['continue_creating']);

        // timestamps 추가
        $form['created_at'] = now();
        $form['updated_at'] = now();

        return $form;
    }

    /**
     * 신규 데이터 DB 삽입후에 호출됩니다.
     */
    public function hookStored($wire, $form)
    {
        // 필요시 추가 작업 수행
        return $form;
    }
}

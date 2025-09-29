<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUserLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminUserLogs Main Controller
 *
 * 사용자 로그인/로그아웃 활동 로그 조회
 */
class AdminUserLogs extends Controller
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
    //         $jsonFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminUserLogs.json';

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
    //         'title' => 'User Activity Logs',
    //         'subtitle' => 'Monitor user authentication activities',
    //         'route' => [
    //             'name' => 'admin.user.logs'
    //         ],
    //         'table' => [
    //             'name' => 'admin_user_logs',
    //             'model' => '\\Jiny\\Admin\\App\\Models\\AdminUserLog'
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
    //                 'enablePagination' => true
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
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUserLogs.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정
        $this->jsonData['currentRoute'] = 'admin.user.logs';

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        // 쿼리 스트링 파라미터를 jsonData에 동적으로 추가
        $queryParams = $request->query();
        if (! empty($queryParams)) {
            // 동적 쿼리 조건을 위한 키 추가
            $this->jsonData['queryConditions'] = [];

            // user_id 파라미터 처리
            if (isset($queryParams['user_id'])) {
                $this->jsonData['queryConditions']['user_id'] = $queryParams['user_id'];
                // 필터에도 추가 (UI 표시용)
                $this->jsonData['index']['filters']['user_id']['value'] = $queryParams['user_id'];
                $this->jsonData['index']['defaultFilters'] = ['user_id' => $queryParams['user_id']];
            }

            // 다른 쿼리 파라미터들도 처리 가능
            foreach (['action', 'email', 'ip_address', 'date_from', 'date_to'] as $param) {
                if (isset($queryParams[$param])) {
                    $this->jsonData['queryConditions'][$param] = $queryParams[$param];
                }
            }
        }

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'title' => $this->jsonData['title'] ?? 'User Activity Logs',
            'subtitle' => $this->jsonData['subtitle'] ?? 'Monitor user authentication activities',
        ]);
    }
}

<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUserLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminUserLogs Show Controller
 *
 * 개별 로그 상세 조회
 */
class AdminUserLogsShow extends Controller
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
    //             return null;
    //         }

    //         $jsonContent = file_get_contents($jsonFilePath);
    //         return json_decode($jsonContent, true);

    //     } catch (\Exception $e) {
    //         return null;
    //     }
    // }

    /**
     * Display the specified resource.
     */
    public function __invoke(Request $request, $id)
    {
        $log = AdminUserLog::with('user')->findOrFail($id);

        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUserLogs.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정
        $this->jsonData['currentRoute'] = 'admin.user.logs';

        $template = $this->jsonData['template']['show'] ?? 'jiny-admin::template.show';

        return view($template, [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $log,
            'id' => $id,
            'title' => 'Log Details #'.$log->id,
            'subtitle' => 'View detailed log information',
        ]);
    }
}

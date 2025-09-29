<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\admin\App\Services\JsonConfigService;

/**
 * AdminPasswordLogs Show Controller
 *
 * 개별 비밀번호 로그 상세 보기
 */
class AdminPasswordLogsShow extends Controller
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
    //         $jsonFilePath = dirname(__DIR__) . '/AdminPasswordLogs/AdminPasswordLogs.json';

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
    //         'title' => 'Password Log Details',
    //         'subtitle' => 'View detailed password attempt information',
    //         'route' => [
    //             'name' => 'admin.user.password.logs.show'
    //         ],
    //         'template' => [
    //             'layout' => 'jiny-admin::layouts.admin',
    //             'show' => 'jiny-admin::template.show'
    //         ]
    //     ];
    // }

    /**
     * Display the specified resource.
     */
    public function __invoke(Request $request, $id)
    {
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 데이터베이스에서 로그 정보 조회
        $log = DB::table('admin_password_logs')->where('id', $id)->first();

        if (! $log) {
            return redirect()->route('admin.user.password.logs')
                ->with('error', '비밀번호 로그를 찾을 수 없습니다.');
        }

        // 데이터를 배열로 변환
        $data = (array) $log;

        // JSON 파일 경로 추가
        $jsonPath = dirname(__DIR__).'/AdminPasswordLogs/AdminPasswordLogs.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정
        $this->jsonData['currentRoute'] = 'admin.user.password.logs.show';

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $data,
            'id' => $id,
            'title' => 'Password Log #'.$id,
            'subtitle' => $this->jsonData['subtitle'] ?? 'View detailed password attempt information',
        ]);
    }
}

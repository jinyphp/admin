<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUserPassword;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminUserPassword Show Controller
 *
 * 개별 패스워드 히스토리 상세 보기
 */
class AdminUserPasswordShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);

        // JSON 파일이 없거나 로드 실패 시 기본값 설정
        // if (!$this->jsonData) {
        //     $this->jsonData = [
        //         'title' => 'Password History Details',
        //         'subtitle' => 'View detailed password attempt information',
        //         'route' => [
        //             'name' => 'admin.user.password'
        //         ],
        //         'template' => [
        //             'layout' => 'jiny-admin::layouts.admin',
        //             'show' => 'jiny-admin::admin.admin_user_password.show'
        //         ]
        //     ];
        // }
    }

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
            return redirect()->route('admin.user.password')
                ->with('error', '비밀번호 로그를 찾을 수 없습니다.');
        }

        // 데이터를 배열로 변환
        $data = (array) $log;

        // 사용자 정보 추가
        if (isset($log->user_id) && $log->user_id) {
            $user = DB::table('users')->where('id', $log->user_id)->first();
            if ($user) {
                $data['user'] = (array) $user;
            }
        } elseif (isset($log->email)) {
            $user = DB::table('users')->where('email', $log->email)->first();
            if ($user) {
                $data['user'] = (array) $user;
            }
        }

        // JSON 파일 경로 추가
        $jsonPath = dirname(__DIR__).'/AdminUserPassword/AdminUserPassword.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정 (목록 페이지로 설정)
        $this->jsonData['currentRoute'] = 'admin.user.password';

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['show'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
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

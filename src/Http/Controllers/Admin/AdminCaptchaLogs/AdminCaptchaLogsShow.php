<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * CAPTCHA 로그 상세 정보 표시 컨트롤러
 *
 * CAPTCHA 인증 시도 로그의 상세 정보를 표시합니다.
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminCaptchaLogs
 */
class AdminCaptchaLogsShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * CAPTCHA 로그 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        // admin_user_logs 테이블에서 CAPTCHA 로그 조회
        $query = DB::table('admin_user_logs')
            ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
            ->where('id', $id);

        $item = $query->first();

        if (!$item) {
            return redirect()
                ->route('admin.captcha.logs')
                ->with('error', 'CAPTCHA 로그를 찾을 수 없습니다.');
        }

        // 객체를 배열로 변환
        $data = (array) $item;

        // Apply hookShowing if exists
        if (method_exists($this, 'hookShowing')) {
            $data = $this->hookShowing(null, $data);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminCaptchaLogs.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // CAPTCHA 로그 제목 설정
        $title = 'CAPTCHA 로그 #' . $id;

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['show'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $data,
            'id' => $id,
            'title' => $title,
            'subtitle' => 'CAPTCHA 인증 시도 상세 정보',
        ]);
    }

    /**
     * 상세보기 표시 전에 호출됩니다.
     */
    public function hookShowing($wire, $data)
    {
        // 날짜 형식 지정
        $dateFormat = $this->jsonData['show']['display']['datetimeFormat'] ?? 'Y-m-d H:i:s';

        if (isset($data['logged_at'])) {
            $data['logged_at_formatted'] = date($dateFormat, strtotime($data['logged_at']));
        }

        if (isset($data['created_at'])) {
            $data['created_at_formatted'] = date($dateFormat, strtotime($data['created_at']));
        }

        // details JSON 파싱
        if (isset($data['details']) && $data['details']) {
            $details = is_string($data['details']) ? json_decode($data['details'], true) : $data['details'];
            $data['score'] = $details['score'] ?? null;
            $data['error'] = $details['error'] ?? null;
            $data['challenge_ts'] = $details['challenge_ts'] ?? null;
            $data['hostname'] = $details['hostname'] ?? null;
        }

        // 상태 라벨 설정
        $data['status_label'] = match($data['action'] ?? '') {
            'captcha_success' => '성공',
            'captcha_failed' => '실패',
            'captcha_missing' => '미입력',
            default => $data['action'] ?? '알 수 없음'
        };

        $data['status_color'] = match($data['action'] ?? '') {
            'captcha_success' => 'green',
            'captcha_failed' => 'red',
            'captcha_missing' => 'yellow',
            default => 'gray'
        };

        return $data;
    }

    /**
     * Hook: 조회 후 데이터 가공
     */
    public function hookShowed($wire, $data)
    {
        return $data;
    }
}
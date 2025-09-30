<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * CAPTCHA 로그 삭제 컨트롤러
 * 오래된 CAPTCHA 로그 정리 기능 제공
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminCaptchaLogs
 */
class AdminCaptchaLogsDelete extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * 삭제 처리 (관리자만 가능)
     */
    public function __invoke(Request $request, $id = null)
    {
        // 단일 로그 삭제는 허용하지 않음
        if ($id) {
            return redirect()
                ->route('admin.system.captcha.logs')
                ->with('error', '개별 CAPTCHA 로그는 삭제할 수 없습니다. 일괄 정리 기능을 사용하세요.');
        }

        // 오래된 로그 일괄 정리 (30일 이상)
        if ($request->has('cleanup')) {
            return $this->cleanup($request);
        }

        return redirect()->route('admin.system.captcha.logs');
    }

    /**
     * 오래된 로그 정리
     */
    private function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        
        try {
            $deleted = DB::table('admin_user_logs')
                ->whereIn('action', ['captcha_success', 'captcha_failed', 'captcha_missing'])
                ->where('logged_at', '<', now()->subDays($days))
                ->delete();

            return redirect()
                ->route('admin.system.captcha.logs')
                ->with('success', "{$deleted}개의 오래된 CAPTCHA 로그가 정리되었습니다.");
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.system.captcha.logs')
                ->with('error', '로그 정리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * Hook: 대량 삭제 전 실행
     */
    public function hookDeleting($wire, $ids)
    {
        // CAPTCHA 로그는 시스템 로그이므로 개별 삭제 방지
        if (is_array($ids) && count($ids) > 0) {
            return '개별 CAPTCHA 로그는 삭제할 수 없습니다. 일괄 정리 기능을 사용하세요.';
        }
        
        return false;
    }
}

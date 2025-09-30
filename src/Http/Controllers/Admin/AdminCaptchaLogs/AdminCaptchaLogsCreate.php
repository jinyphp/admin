<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * CAPTCHA 로그 생성 컨트롤러
 * CAPTCHA 로그는 시스템에서 자동 생성되므로 수동 생성 불가
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminCaptchaLogs
 */
class AdminCaptchaLogsCreate extends Controller
{
    /**
     * CAPTCHA 로그는 수동으로 생성할 수 없음
     */
    public function __invoke(Request $request)
    {
        // CAPTCHA 로그는 로그인 시도 시 자동으로 생성됨
        return redirect()
            ->route('admin.system.captcha.logs')
            ->with('error', 'CAPTCHA 로그는 수동으로 생성할 수 없습니다. 로그인 시도 시 자동으로 기록됩니다.');
    }
}

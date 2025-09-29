<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminCaptchaLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * CAPTCHA 로그 수정 컨트롤러
 * CAPTCHA 로그는 시스템 로그이므로 수정 불가
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminCaptchaLogs
 */
class AdminCaptchaLogsEdit extends Controller
{
    /**
     * CAPTCHA 로그는 수정할 수 없음
     */
    public function __invoke(Request $request, $id)
    {
        // CAPTCHA 로그는 시스템 로그이므로 수정 불가
        return redirect()
            ->route('admin.captcha.logs')
            ->with('error', 'CAPTCHA 로그는 시스템 로그이므로 수정할 수 없습니다.');
    }
}

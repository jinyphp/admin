<?php

namespace Jiny\Admin\Http\Controllers\Web\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUsertype;
use Jiny\Admin\Models\User;

/**
 * 관리자 로그인 컨트롤러
 *
 * 관리자 로그인 페이지를 표시하고 인증 처리를 담당합니다.
 *
 * ## 메소드 호출 흐름:
 *
 * 1. __construct()
 *    └─> loadConfiguration()
 *        └─> getDefaultConfiguration() [설정 파일이 없는 경우]
 *
 * 2. showLoginForm() [메인 엔트리 포인트]
 *    ├─> Step 1: isUserLoggedIn()
 *    │   └─> [false] displayLoginForm() → 종료
 *    │
 *    ├─> Step 2: getCurrentUser()
 *    │
 *    ├─> Step 3: hasAdminPrivileges()
 *    │   └─> [false] handleNonAdminUser()
 *    │       ├─> shouldLogAccess()
 *    │       ├─> logUnauthorizedAccess()
 *    │       ├─> setNotification()
 *    │       └─> displayLoginForm() → 종료
 *    │
 *    ├─> Step 4: shouldCheckAdminType()
 *    │   └─> [true] isValidAdminType()
 *    │       └─> [false] handleInvalidAdminType()
 *    │           ├─> shouldLogAccess()
 *    │           ├─> logUnauthorizedAccess()
 *    │           ├─> setNotification()
 *    │           └─> displayLoginForm() → 종료
 *    │
 *    └─> Step 5: redirectToDashboard()
 *        ├─> shouldLogAccess()
 *        ├─> logSuccessfulAccess()
 *        ├─> isAutoRedirectEnabled()
 *        │   └─> [false] displayLoginFormWithMessage()
 *        │       ├─> setNotification()
 *        │       └─> view() → 종료
 *        └─> [true] redirect() → 종료
 *
 * 3. trackLoginPageAccess() [선택적 호출]
 *    └─> shouldLogAccess()
 *        └─> AdminUserLog::log()
 */
class AdminLogin extends Controller
{
    /**
     * 설정 데이터
     */
    private $config;

    /**
     * 컨트롤러 생성자
     */
    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * 로그인 폼 표시 [메인 엔트리 포인트]
     *
     * 이미 로그인된 사용자는 관리자 권한에 따라 리다이렉트하고,
     * 로그인되지 않은 사용자에게는 로그인 폼을 표시합니다.
     *
     * 호출 순서:
     * 1. isUserLoggedIn() - 로그인 상태 확인
     * 2. getCurrentUser() - 사용자 정보 획득
     * 3. hasAdminPrivileges() - 관리자 권한 확인
     * 4. shouldCheckAdminType() & isValidAdminType() - 관리자 타입 검증
     * 5. redirectToDashboard() - 최종 리다이렉트
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        // Setup 완료 확인 - 관리자가 없으면 setup 페이지로 리다이렉트
        if (!$this->isSetupComplete()) {
            return redirect('/admin/setup');
        }

        // Step 1: 로그인 상태 확인
        if (! $this->isUserLoggedIn()) {
            return $this->displayLoginForm();
        }

        // Step 2: 현재 사용자 정보 가져오기
        $user = $this->getCurrentUser();

        // Step 3: 관리자 권한 검증
        if (! $this->hasAdminPrivileges($user)) {
            return $this->handleNonAdminUser();
        }

        // Step 4: 관리자 타입 검증 (설정에 따라)
        if ($this->shouldCheckAdminType()) {
            if (! $this->isValidAdminType($user)) {
                return $this->handleInvalidAdminType($user);
            }
        }

        // Step 5: 관리자 대시보드로 리다이렉트
        return $this->redirectToDashboard($user);
    }

    /**
     * 설정 파일 로드
     */
    private function loadConfiguration()
    {
        $configPath = __DIR__.'/AdminLogin.json';

        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
        } else {
            $this->config = $this->getDefaultConfiguration();
        }
    }

    /**
     * 기본 설정 반환
     *
     * @return array
     */
    private function getDefaultConfiguration()
    {
        return [
            'viewPath' => 'jiny-admin::Site.Login.login',
            'routes' => [
                'dashboard' => 'admin.dashboard',
                'login' => 'admin.login',
            ],
            'messages' => [
                'admin_required' => '관리자 계정으로 로그인해주세요.',
                'already_logged_in' => '이미 로그인되어 있습니다.',
                'invalid_admin_type' => '유효하지 않은 관리자 타입입니다.',
                'welcome_back' => '환영합니다.',
            ],
            'settings' => [
                'auto_redirect' => true,
                'check_admin_type' => true,
                'log_access' => true,
            ],
        ];
    }

    /**
     * 사용자 로그인 상태 확인
     *
     * @return bool
     */
    private function isUserLoggedIn()
    {
        return auth()->check();
    }

    /**
     * 현재 로그인한 사용자 반환
     *
     * @return mixed
     */
    private function getCurrentUser()
    {
        return auth()->user();
    }

    /**
     * 관리자 권한 보유 여부 확인
     *
     * @param  mixed  $user
     * @return bool
     */
    private function hasAdminPrivileges($user)
    {
        return isset($user->isAdmin) && $user->isAdmin;
    }

    /**
     * 관리자 타입 검증 필요 여부 확인
     *
     * @return bool
     */
    private function shouldCheckAdminType()
    {
        return $this->config['settings']['check_admin_type'] ?? true;
    }

    /**
     * 유효한 관리자 타입인지 확인
     *
     * @param  mixed  $user
     * @return bool
     */
    private function isValidAdminType($user)
    {
        // 사용자 타입이 설정되어 있는지 확인
        if (! isset($user->utype) || ! $user->utype) {
            return false;
        }

        // admin_user_types 테이블에서 유효성 검증
        $adminType = AdminUsertype::where('code', $user->utype)
            ->where('enable', true)
            ->first();

        return $adminType !== null;
    }

    /**
     * 관리자가 아닌 사용자 처리
     *
     * 호출 순서:
     * 1. shouldLogAccess() - 로그 기록 여부 확인
     * 2. logUnauthorizedAccess() - 권한 없는 접근 로그 기록
     * 3. auth()->logout() - 로그아웃 처리
     * 4. setNotification() - 알림 메시지 설정
     * 5. displayLoginForm() - 로그인 폼 표시
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleNonAdminUser()
    {
        // 로그 기록 (설정에 따라)
        if ($this->shouldLogAccess()) {
            $this->logUnauthorizedAccess('not_admin');
        }

        // 로그아웃 처리
        auth()->logout();

        // 알림 메시지 설정
        $this->setNotification('info', '관리자 로그인', $this->config['messages']['admin_required']);

        // 로그인 페이지로 리다이렉트
        return $this->displayLoginForm();
    }

    /**
     * 유효하지 않은 관리자 타입 처리
     *
     * 호출 순서:
     * 1. shouldLogAccess() - 로그 기록 여부 확인
     * 2. logUnauthorizedAccess() - 권한 없는 접근 로그 기록
     * 3. auth()->logout() - 로그아웃 처리
     * 4. setNotification() - 경고 메시지 설정
     * 5. displayLoginForm() - 로그인 폼 표시
     *
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleInvalidAdminType($user)
    {
        // 로그 기록 (설정에 따라)
        if ($this->shouldLogAccess()) {
            $this->logUnauthorizedAccess('invalid_admin_type', $user);
        }

        // 로그아웃 처리
        auth()->logout();

        // 알림 메시지 설정
        $this->setNotification('warning', '관리자 타입 오류', $this->config['messages']['invalid_admin_type']);

        // 로그인 페이지로 리다이렉트
        return $this->displayLoginForm();
    }

    /**
     * 관리자 대시보드로 리다이렉트
     *
     * 호출 순서:
     * 1. shouldLogAccess() - 로그 기록 여부 확인
     * 2. logSuccessfulAccess() - 성공 접근 로그 기록
     * 3. isAutoRedirectEnabled() - 자동 리다이렉트 설정 확인
     * 4-a. [자동 리다이렉트 비활성] displayLoginFormWithMessage()
     * 4-b. [자동 리다이렉트 활성] redirect()->route()
     *
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToDashboard($user)
    {
        // 로그 기록 (설정에 따라)
        if ($this->shouldLogAccess()) {
            $this->logSuccessfulAccess($user);
        }

        // 자동 리다이렉트 설정 확인
        if (! $this->isAutoRedirectEnabled()) {
            return $this->displayLoginFormWithMessage();
        }

        return redirect()->route($this->config['routes']['dashboard']);
    }

    /**
     * 로그인 폼 표시
     *
     * @return \Illuminate\View\View
     */
    private function displayLoginForm()
    {
        return view($this->config['viewPath']);
    }

    /**
     * 메시지와 함께 로그인 폼 표시
     *
     * @return \Illuminate\View\View
     */
    private function displayLoginFormWithMessage()
    {
        $this->setNotification('success', '이미 로그인됨', $this->config['messages']['already_logged_in']);

        return view($this->config['viewPath']);
    }

    /**
     * 알림 메시지 설정
     *
     * @param  string  $type
     * @param  string  $title
     * @param  string  $message
     */
    private function setNotification($type, $title, $message)
    {
        session()->flash('notification', [
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    /**
     * 자동 리다이렉트 활성화 여부 확인
     *
     * @return bool
     */
    private function isAutoRedirectEnabled()
    {
        return $this->config['settings']['auto_redirect'] ?? true;
    }

    /**
     * 접근 로그 기록 여부 확인
     *
     * @return bool
     */
    private function shouldLogAccess()
    {
        return $this->config['settings']['log_access'] ?? true;
    }

    /**
     * 권한 없는 접근 로그 기록
     *
     * @param  string  $reason
     * @param  mixed  $user
     */
    private function logUnauthorizedAccess($reason, $user = null)
    {
        $logData = [
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'attempted_at' => now()->toDateTimeString(),
        ];

        if ($user) {
            $logData['user_id'] = $user->id;
            $logData['email'] = $user->email;
            $logData['utype'] = $user->utype ?? null;
        }

        AdminUserLog::log('unauthorized_login_page_access', $user, $logData);
    }

    /**
     * 성공적인 접근 로그 기록
     *
     * @param  mixed  $user
     */
    private function logSuccessfulAccess($user)
    {
        AdminUserLog::log('login_page_redirect', $user, [
            'action' => 'auto_redirect_to_dashboard',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'redirected_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * 로그인 페이지 접근 추적
     *
     * 로그인 페이지 방문을 추적하고 분석합니다.
     *
     * @return void
     */
    public function trackLoginPageAccess(Request $request)
    {
        if ($this->shouldLogAccess()) {
            AdminUserLog::log('login_page_view', null, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('Referer'),
                'viewed_at' => now()->toDateTimeString(),
            ]);
        }
    }

    /**
     * Setup 완료 여부 확인
     *
     * @return bool
     */
    private function isSetupComplete()
    {
        // users 테이블에 사용자가 한 명이라도 있으면 setup 완료로 간주
        return User::exists();
    }
}

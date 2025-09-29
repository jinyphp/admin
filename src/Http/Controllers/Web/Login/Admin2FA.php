<?php

namespace Jiny\Admin\Http\Controllers\Web\Login;

use App\Http\Controllers\Controller;
use Jiny\Admin\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUserSession;
use PragmaRX\Google2FA\Google2FA;

/**
 * 2차 인증(2FA) 컨트롤러
 *
 * Google Authenticator를 사용한 2차 인증 기능을 제공합니다.
 * TOTP 코드 검증, 백업 코드 처리, 시도 횟수 제한 등을 관리합니다.
 *
 * ## 메소드 호출 흐름:
 *
 * 1. __construct()
 *    └─> loadConfiguration()
 *        └─> getDefaultConfiguration() [설정 파일이 없는 경우]
 *
 * 2. showChallenge() [2FA 인증 페이지 표시]
 *    ├─> Step 1: validateSession()
 *    │   └─> [false] redirectToLogin() → 종료
 *    └─> Step 2: displayChallengeView() → 종료
 *
 * 3. verify() [2FA 코드 검증]
 *    ├─> Step 1: validateRequest()
 *    ├─> Step 2: validateSession()
 *    │   └─> [false] redirectToLogin() → 종료
 *    ├─> Step 3: getUserFromSession()
 *    │   └─> [null] redirectToLogin() → 종료
 *    ├─> Step 4: trackAttempt()
 *    ├─> Step 5: verifyCode()
 *    │   ├─> verifyBackupCode() [백업 코드 사용시]
 *    │   └─> verifyTotpCode() [일반 코드 사용시]
 *    ├─> Step 6a: [성공] handleSuccessfulVerification()
 *    │   ├─> recordSuccessfulVerification()
 *    │   ├─> completeLogin()
 *    │   ├─> updateUserLoginInfo()
 *    │   ├─> logSuccessfulLogin()
 *    │   ├─> trackSession()
 *    │   ├─> cleanupSession()
 *    │   └─> redirectToDashboard() → 종료
 *    └─> Step 6b: [실패] handleFailedVerification()
 *        ├─> checkMaxAttempts()
 *        │   └─> [초과] terminateSession() → 종료
 *        └─> displayErrorMessage() → 종료
 *
 * 4. check2FARequired() [정적 메소드 - 2FA 필요 여부 확인]
 *    ├─> is2FAEnabled()
 *    │   └─> [false] return false → 종료
 *    ├─> storeUserInSession()
 *    ├─> storeRememberOption()
 *    └─> logoutUser() → return true
 */
class Admin2FA extends Controller
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
     * 2FA 인증 페이지 표시 [메인 엔트리 포인트]
     *
     * 로그인 후 2FA가 필요한 사용자에게 인증 코드 입력 화면을 표시합니다.
     *
     * 호출 순서:
     * 1. validateSession() - 세션 유효성 검증
     * 2. displayChallengeView() - 인증 페이지 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showChallenge(Request $request)
    {
        // Step 1: 세션 유효성 검증
        if (! $this->validateSession($request)) {
            return $this->redirectToLogin($this->config['messages']['session_expired']);
        }

        // Step 2: 2FA 인증 페이지 표시
        return $this->displayChallengeView();
    }

    /**
     * 2FA 코드 검증 [메인 검증 포인트]
     *
     * 사용자가 입력한 2FA 코드를 검증합니다.
     *
     * 호출 순서:
     * 1. validateRequest() - 요청 데이터 검증
     * 2. validateSession() - 세션 유효성 검증
     * 3. getUserFromSession() - 세션에서 사용자 정보 조회
     * 4. trackAttempt() - 시도 횟수 추적
     * 5. verifyCode() - 코드 검증 (백업/TOTP)
     * 6a. handleSuccessfulVerification() - 성공 처리
     * 6b. handleFailedVerification() - 실패 처리
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        // Step 1: 요청 데이터 검증
        $this->validateRequest($request);

        // Step 2: 세션 유효성 검증
        if (! $this->validateSession($request)) {
            return $this->redirectToLogin($this->config['messages']['session_expired']);
        }

        // Step 3: 세션에서 사용자 정보 가져오기
        $user = $this->getUserFromSession($request);
        if (! $user) {
            return $this->redirectToLogin($this->config['messages']['user_not_found']);
        }

        // Step 4: 시도 횟수 추적
        $attempts = $this->trackAttempt($request);

        // Step 5: 코드 검증
        $verificationResult = $this->verifyCode($user, $request);

        // Step 6: 검증 결과 처리
        if ($verificationResult['verified']) {
            return $this->handleSuccessfulVerification($user, $request, $verificationResult, $attempts);
        } else {
            return $this->handleFailedVerification($request, $attempts);
        }
    }

    /**
     * 설정 파일 로드
     */
    private function loadConfiguration()
    {
        $configPath = __DIR__.'/Admin2FA.json';

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
            'viewPaths' => [
                'challenge' => 'jiny-admin::web.login.2fa_challenge',
                'setup' => 'jiny-admin::web.login.2fa_setup',
                'recovery' => 'jiny-admin::web.login.2fa_recovery',
            ],
            'routes' => [
                'challenge' => 'admin.2fa.challenge',
                'verify' => 'admin.2fa.verify',
                'login' => 'admin.login',
                'dashboard' => 'admin.dashboard',
            ],
            'messages' => [
                'session_expired' => '세션이 만료되었습니다. 다시 로그인해주세요.',
                'user_not_found' => '사용자를 찾을 수 없습니다.',
                'invalid_code' => '인증 코드가 올바르지 않습니다.',
                'max_attempts_exceeded' => '너무 많은 시도로 인해 세션이 종료되었습니다. 다시 로그인해주세요.',
                'verification_success' => '2차 인증이 완료되었습니다.',
                'backup_code_used' => '백업 코드가 사용되었습니다.',
            ],
            'settings' => [
                'max_attempts' => 5,
                'code_length' => 6,
                'backup_codes_count' => 8,
                'window_size' => 2,
                'enable_backup_codes' => true,
                'log_attempts' => true,
                'log_success' => true,
            ],
            'session' => [
                'user_id_key' => '2fa_user_id',
                'user_email_key' => '2fa_user_email',
                'attempts_key' => '2fa_attempts',
                'remember_key' => '2fa_remember',
                'completed_key' => '2fa_completed',
            ],
        ];
    }

    /**
     * 요청 데이터 검증
     */
    private function validateRequest(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
    }

    /**
     * 세션 유효성 검증
     *
     * @return bool
     */
    private function validateSession(Request $request)
    {
        return $request->session()->has($this->config['session']['user_id_key']);
    }

    /**
     * 세션에서 사용자 정보 조회
     *
     * @return User|null
     */
    private function getUserFromSession(Request $request)
    {
        $userId = $request->session()->get($this->config['session']['user_id_key']);

        return $userId ? User::find($userId) : null;
    }

    /**
     * 시도 횟수 추적
     *
     * @return int
     */
    private function trackAttempt(Request $request)
    {
        $attempts = $request->session()->get($this->config['session']['attempts_key'], 0) + 1;
        $request->session()->put($this->config['session']['attempts_key'], $attempts);

        return $attempts;
    }

    /**
     * 코드 검증 (백업 또는 TOTP)
     *
     * 호출 순서:
     * 1. 백업 코드 사용시: verifyBackupCode()
     * 2. 일반 코드 사용시: verifyTotpCode()
     *
     * @return array
     */
    private function verifyCode(User $user, Request $request)
    {
        $code = $request->input('code');
        $useBackup = $request->input('use_backup', false);

        if ($useBackup && $this->config['settings']['enable_backup_codes']) {
            return $this->verifyBackupCode($user, $code);
        } else {
            return $this->verifyTotpCode($user, $code);
        }
    }

    /**
     * 백업 코드 검증
     *
     * @param  string  $code
     * @return array
     */
    private function verifyBackupCode(User $user, $code)
    {
        $backupCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (in_array($code, $backupCodes)) {
            // 사용된 백업 코드 제거
            $backupCodes = array_diff($backupCodes, [$code]);
            $user->two_factor_recovery_codes = encrypt(json_encode(array_values($backupCodes)));
            $user->save();

            return [
                'verified' => true,
                'method' => 'backup',
            ];
        }

        return [
            'verified' => false,
            'method' => 'backup',
        ];
    }

    /**
     * TOTP 코드 검증
     *
     * @param  string  $code
     * @return array
     */
    private function verifyTotpCode(User $user, $code)
    {
        $google2fa = new Google2FA;
        $secret = decrypt($user->two_factor_secret);

        $verified = $google2fa->verifyKey($secret, $code);

        return [
            'verified' => $verified,
            'method' => 'app',
        ];
    }

    /**
     * 성공적인 검증 처리
     *
     * 호출 순서:
     * 1. recordSuccessfulVerification() - 성공 정보 세션 저장
     * 2. completeLogin() - 로그인 완료
     * 3. updateUserLoginInfo() - 사용자 정보 업데이트
     * 4. logSuccessfulLogin() - 로그 기록
     * 5. trackSession() - 세션 추적
     * 6. cleanupSession() - 세션 정리
     * 7. handleRememberMe() - Remember Me 처리
     * 8. redirectToDashboard() - 대시보드로 리다이렉트
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleSuccessfulVerification(User $user, Request $request, array $verificationResult, int $attempts)
    {
        // Step 1: 성공 정보를 세션에 저장
        $this->recordSuccessfulVerification($request, $user, $verificationResult['method'], $attempts);

        // Step 2: 로그인 완료
        $this->completeLogin($user, $request);

        // Step 3: 사용자 정보 업데이트
        $this->updateUserLoginInfo($user);

        // Step 4: 로그 기록
        if ($this->config['settings']['log_success']) {
            $this->logSuccessfulLogin($user, $request, $verificationResult['method'], $attempts);
        }

        // Step 5: 세션 추적
        $this->trackSession($user, $request);

        // Step 6: 세션 정리
        $this->cleanupSession($request);

        // Step 7: Remember Me 처리
        $this->handleRememberMe($user, $request);

        // Step 8: 대시보드로 리다이렉트
        return $this->redirectToDashboard();
    }

    /**
     * 실패한 검증 처리
     *
     * 호출 순서:
     * 1. checkMaxAttempts() - 최대 시도 횟수 확인
     * 2a. [초과시] terminateSession() - 세션 종료
     * 2b. [미초과] displayErrorMessage() - 에러 메시지 표시
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleFailedVerification(Request $request, int $attempts)
    {
        // 최대 시도 횟수 확인
        if ($this->checkMaxAttempts($attempts)) {
            return $this->terminateSession($request);
        }

        // 에러 메시지 표시
        return $this->displayErrorMessage($attempts);
    }

    /**
     * 성공 정보 세션 저장
     */
    private function recordSuccessfulVerification(Request $request, User $user, string $method, int $attempts)
    {
        $request->session()->put($this->config['session']['completed_key'], [
            'user_id' => $user->id,
            'method' => $method,
            'verified_at' => now(),
            'attempts' => $attempts,
        ]);
    }

    /**
     * 로그인 완료 처리
     */
    private function completeLogin(User $user, Request $request)
    {
        Auth::login($user);
    }

    /**
     * 사용자 로그인 정보 업데이트
     */
    private function updateUserLoginInfo(User $user)
    {
        $user->last_2fa_used_at = now();
        $user->last_login_at = now();
        $user->login_count = ($user->login_count ?? 0) + 1;
        $user->save();
    }

    /**
     * 성공적인 로그인 로그 기록
     */
    private function logSuccessfulLogin(User $user, Request $request, string $method, int $attempts)
    {
        AdminUserLog::log('login', $user, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'two_factor_used' => true,
            'two_factor_method' => $method,
            'two_factor_required' => true,
            'two_factor_verified_at' => now(),
            'two_factor_attempts' => $attempts,
        ]);
    }

    /**
     * 세션 추적
     */
    private function trackSession(User $user, Request $request)
    {
        $session = AdminUserSession::track($user, $request, true);
        if (! $session) {
            \Log::warning('Failed to track session for user with 2FA: '.$user->email);
        }
    }

    /**
     * 세션 정리
     */
    private function cleanupSession(Request $request)
    {
        $keysToForget = [
            $this->config['session']['user_id_key'],
            $this->config['session']['user_email_key'],
            $this->config['session']['attempts_key'],
        ];

        $request->session()->forget($keysToForget);
    }

    /**
     * Remember Me 처리
     */
    private function handleRememberMe(User $user, Request $request)
    {
        if ($request->session()->has($this->config['session']['remember_key'])) {
            Auth::login($user, true);
            $request->session()->forget($this->config['session']['remember_key']);
        }
    }

    /**
     * 최대 시도 횟수 확인
     */
    private function checkMaxAttempts(int $attempts)
    {
        return $attempts >= $this->config['settings']['max_attempts'];
    }

    /**
     * 세션 종료
     */
    private function terminateSession(Request $request)
    {
        $keysToForget = [
            $this->config['session']['user_id_key'],
            $this->config['session']['user_email_key'],
            $this->config['session']['attempts_key'],
            $this->config['session']['remember_key'],
        ];

        $request->session()->forget($keysToForget);

        return redirect()->route($this->config['routes']['login'])
            ->with('error', $this->config['messages']['max_attempts_exceeded']);
    }

    /**
     * 에러 메시지 표시
     */
    private function displayErrorMessage(int $attempts)
    {
        $maxAttempts = $this->config['settings']['max_attempts'];
        $message = $this->config['messages']['invalid_code'].
                   " ({$attempts}/{$maxAttempts} 시도)";

        return back()->with('error', $message);
    }

    /**
     * 2FA 인증 페이지 표시
     */
    private function displayChallengeView()
    {
        return view($this->config['viewPaths']['challenge']);
    }

    /**
     * 로그인 페이지로 리다이렉트
     */
    private function redirectToLogin($message = null)
    {
        $redirect = redirect()->route($this->config['routes']['login']);

        if ($message) {
            $redirect->with('error', $message);
        }

        return $redirect;
    }

    /**
     * 대시보드로 리다이렉트
     */
    private function redirectToDashboard()
    {
        return redirect()->intended(route($this->config['routes']['dashboard']));
    }

    /**
     * 2FA 필요 여부 확인 [정적 메소드]
     *
     * 로그인 성공 후 사용자에게 2FA가 필요한지 확인합니다.
     *
     * 호출 순서:
     * 1. is2FAEnabled() - 2FA 활성화 여부 확인
     * 2. storeUserInSession() - 사용자 정보 세션 저장
     * 3. storeRememberOption() - Remember 옵션 저장
     * 4. logoutUser() - 임시 로그아웃
     *
     * @param  User  $user  인증할 사용자 객체
     * @param  Request  $request  HTTP 요청 객체
     * @return bool 2FA 필요 여부
     */
    public static function check2FARequired($user, Request $request)
    {
        // Step 1: 2FA 활성화 여부 확인
        if (! self::is2FAEnabled($user)) {
            return false;
        }

        // Step 2: 사용자 정보를 세션에 저장
        self::storeUserInSession($user, $request);

        // Step 3: Remember Me 옵션 저장
        self::storeRememberOption($request);

        // Step 4: 임시 로그아웃 (2FA 검증 전까지)
        self::logoutUser();

        return true;
    }

    /**
     * 2FA 활성화 여부 확인
     */
    private static function is2FAEnabled($user)
    {
        return $user->two_factor_enabled && $user->two_factor_secret;
    }

    /**
     * 사용자 정보를 세션에 저장
     */
    private static function storeUserInSession($user, Request $request)
    {
        // 설정 파일 직접 로드 (정적 메소드이므로)
        $configPath = __DIR__.'/Admin2FA.json';
        $config = file_exists($configPath)
            ? json_decode(file_get_contents($configPath), true)
            : ['session' => ['user_id_key' => '2fa_user_id', 'user_email_key' => '2fa_user_email']];

        $request->session()->put($config['session']['user_id_key'], $user->id);
        $request->session()->put($config['session']['user_email_key'], $user->email);
    }

    /**
     * Remember 옵션 저장
     */
    private static function storeRememberOption(Request $request)
    {
        if ($request->has('remember')) {
            // 설정 파일 직접 로드 (정적 메소드이므로)
            $configPath = __DIR__.'/Admin2FA.json';
            $config = file_exists($configPath)
                ? json_decode(file_get_contents($configPath), true)
                : ['session' => ['remember_key' => '2fa_remember']];

            $request->session()->put($config['session']['remember_key'], true);
        }
    }

    /**
     * 임시 로그아웃
     */
    private static function logoutUser()
    {
        Auth::logout();
    }
}

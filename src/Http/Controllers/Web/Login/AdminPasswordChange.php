<?php

namespace Jiny\Admin\Http\Controllers\Web\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Jiny\Admin\Models\AdminPasswordLog;
use Jiny\Admin\Models\AdminUserLog;

/**
 * 관리자 비밀번호 변경 컨트롤러
 *
 * 관리자 사용자의 비밀번호 변경 기능을 담당합니다.
 * 비밀번호 만료, 관리자의 강제 변경 요청, 사용자의 자발적 변경 등
 * 다양한 시나리오에서 비밀번호 변경을 처리합니다.
 *
 * ## 메소드 호출 흐름:
 *
 * 1. __construct()
 *    └─> loadConfiguration()
 *        └─> getDefaultConfiguration() [설정 파일이 없는 경우]
 *
 * 2. showChangeForm() [비밀번호 변경 폼 표시]
 *    ├─> Step 1: checkAuthentication()
 *    │   └─> [false] redirectToLogin() → 종료
 *    ├─> Step 2: checkPasswordChangeRequired()
 *    └─> Step 3: displayChangeForm() → 종료
 *
 * 3. changePassword() [비밀번호 변경 처리]
 *    ├─> Step 1: checkAuthentication()
 *    │   └─> [false] redirectToLogin() → 종료
 *    ├─> Step 2: validatePasswordInput()
 *    │   └─> [실패] 에러 반환 → 종료
 *    ├─> Step 3: getCurrentUser()
 *    ├─> Step 4: checkSamePassword()
 *    │   └─> [동일] displayError() → 종료
 *    ├─> Step 5: checkPasswordHistory()
 *    │   └─> [재사용] displayError() → 종료
 *    ├─> Step 6: logPasswordChange()
 *    ├─> Step 7: updateUserPassword()
 *    │   ├─> hashPassword()
 *    │   ├─> setPasswordChangedAt()
 *    │   ├─> setPasswordExpiryDate()
 *    │   └─> resetForceChangeFlags()
 *    ├─> Step 8: saveUser()
 *    ├─> Step 9: logUserActivity()
 *    ├─> Step 10: clearSessionFlags()
 *    ├─> Step 11: setSuccessNotification()
 *    └─> Step 12: redirectToDashboard() → 종료
 *
 * @author  JinyPHP Team
 *
 * @since   2025.09.04
 */
class AdminPasswordChange extends Controller
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
     * 비밀번호 변경 폼 표시 [메인 엔트리 포인트]
     *
     * 사용자에게 비밀번호 변경 화면을 보여줍니다.
     *
     * 호출 순서:
     * 1. checkAuthentication() - 인증 상태 확인
     * 2. checkPasswordChangeRequired() - 강제 변경 여부 확인
     * 3. displayChangeForm() - 변경 폼 표시
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showChangeForm()
    {
        // Step 1: 인증 상태 확인
        if (! $this->checkAuthentication()) {
            return $this->redirectToLogin();
        }

        // Step 2: 비밀번호 강제 변경 여부 확인
        $passwordChangeRequired = $this->checkPasswordChangeRequired();

        // Step 3: 비밀번호 변경 폼 표시
        return $this->displayChangeForm($passwordChangeRequired);
    }

    /**
     * 비밀번호 변경 처리 [메인 처리 포인트]
     *
     * 사용자가 제출한 새 비밀번호를 검증하고 변경합니다.
     *
     * 호출 순서:
     * 1. checkAuthentication() - 인증 상태 확인
     * 2. validatePasswordInput() - 입력 데이터 검증
     * 3. getCurrentUser() - 현재 사용자 정보 조회
     * 4. checkSamePassword() - 동일 비밀번호 체크
     * 5. checkPasswordHistory() - 비밀번호 재사용 체크
     * 6. logPasswordChange() - 변경 로그 기록
     * 7. updateUserPassword() - 사용자 비밀번호 업데이트
     * 8. saveUser() - 사용자 정보 저장
     * 9. logUserActivity() - 활동 로그 기록
     * 10. clearSessionFlags() - 세션 플래그 정리
     * 11. setSuccessNotification() - 성공 메시지 설정
     * 12. redirectToDashboard() - 대시보드로 리다이렉트
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        // Step 1: 인증 상태 확인
        if (! $this->checkAuthentication()) {
            return $this->redirectToLogin();
        }

        // Step 2: 입력 데이터 검증
        $validated = $this->validatePasswordInput($request);
        if (! $validated) {
            return back()->withErrors($this->getValidationErrors())->withInput();
        }

        // Step 3: 현재 사용자 정보 가져오기
        $user = $this->getCurrentUser();

        // Step 4: 동일 비밀번호 체크
        if ($this->checkSamePassword($request->password, $user->password)) {
            return $this->displayError('password', $this->config['messages']['password_same']);
        }

        // Step 5: 비밀번호 히스토리 체크
        if ($this->checkPasswordHistory($user->id, $request->password)) {
            return $this->displayError('password', $this->config['messages']['password_reused']);
        }

        // Step 6: 비밀번호 변경 로그 기록
        $this->logPasswordChange($user, $request);

        // Step 7: 사용자 비밀번호 업데이트
        $this->updateUserPassword($user, $request->password);

        // Step 8: 사용자 정보 저장
        $this->saveUser($user);

        // Step 9: 활동 로그 기록
        $this->logUserActivity($user, $request);

        // Step 10: 세션 플래그 정리
        $this->clearSessionFlags();

        // Step 11: 성공 메시지 설정
        $this->setSuccessNotification();

        // Step 12: 대시보드로 리다이렉트
        return $this->redirectToDashboard();
    }

    /**
     * 설정 파일 로드
     */
    private function loadConfiguration()
    {
        $configPath = __DIR__.'/AdminPasswordChange.json';

        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
        } else {
            $this->config = $this->getDefaultConfiguration();
        }
    }

    /**
     * 기본 설정 반환
     */
    private function getDefaultConfiguration()
    {
        return [
            'viewPaths' => [
                'change_form' => 'jiny-admin::web.login.password_change',
            ],
            'routes' => [
                'login' => 'admin.login',
                'dashboard' => 'admin.dashboard',
            ],
            'messages' => [
                'login_required' => '비밀번호를 변경하려면 먼저 로그인해주세요.',
                'login_required_title' => '로그인 필요',
                'password_same' => '새 비밀번호는 현재 비밀번호와 다르게 설정해주세요.',
                'password_reused' => '최근에 사용한 비밀번호는 재사용할 수 없습니다.',
                'change_success' => '비밀번호가 성공적으로 변경되었습니다.',
                'change_success_title' => '비밀번호 변경 완료',
            ],
            'password_policy' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_numbers' => true,
                'require_symbols' => true,
                'check_compromised' => true,
                'password_history' => 3,
                'expiry_days' => 90,
            ],
            'session' => [
                'required_flag' => 'password_change_required',
                'user_id_key' => 'password_change_user_id',
            ],
        ];
    }

    /**
     * 인증 상태 확인
     *
     * @return bool
     */
    private function checkAuthentication()
    {
        return Auth::check();
    }

    /**
     * 현재 사용자 반환
     *
     * @return \App\Models\User
     */
    private function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * 비밀번호 강제 변경 여부 확인
     *
     * @return bool
     */
    private function checkPasswordChangeRequired()
    {
        return session($this->config['session']['required_flag'], false);
    }

    /**
     * 입력 데이터 검증
     *
     * @return bool
     */
    private function validatePasswordInput(Request $request)
    {
        try {
            $rules = [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed'],
            ];

            // 비밀번호 정책 적용
            $passwordRule = Password::min($this->config['password_policy']['min_length']);

            if ($this->config['password_policy']['require_uppercase']) {
                $passwordRule->mixedCase();
            }
            if ($this->config['password_policy']['require_numbers']) {
                $passwordRule->numbers();
            }
            if ($this->config['password_policy']['require_symbols']) {
                $passwordRule->symbols();
            }
            if ($this->config['password_policy']['check_compromised']) {
                $passwordRule->uncompromised();
            }

            $rules['password'][] = $passwordRule;

            $request->validate($rules, $this->config['messages']['validation'] ?? []);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 검증 에러 메시지 반환
     */
    private function getValidationErrors()
    {
        return $this->config['messages']['validation'] ?? [];
    }

    /**
     * 동일 비밀번호 체크
     *
     * @param  string  $newPassword
     * @param  string  $currentPasswordHash
     * @return bool
     */
    private function checkSamePassword($newPassword, $currentPasswordHash)
    {
        return Hash::check($newPassword, $currentPasswordHash);
    }

    /**
     * 비밀번호 히스토리 체크
     *
     * @param  int  $userId
     * @param  string  $newPassword
     * @return bool
     */
    private function checkPasswordHistory($userId, $newPassword)
    {
        $historyLimit = $this->config['password_policy']['password_history'];

        $recentPasswords = AdminPasswordLog::where('user_id', $userId)
            ->where('action', 'password_changed')
            ->orderBy('created_at', 'desc')
            ->limit($historyLimit)
            ->pluck('old_password_hash')
            ->toArray();

        foreach ($recentPasswords as $oldPasswordHash) {
            if (Hash::check($newPassword, $oldPasswordHash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 비밀번호 변경 로그 기록
     *
     * @param  \App\Models\User  $user
     */
    private function logPasswordChange($user, Request $request)
    {
        if (! $this->config['logging']['log_password_changes']) {
            return;
        }

        AdminPasswordLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'action' => 'password_changed',
            'old_password_hash' => $user->password,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'forced_change' => $this->checkPasswordChangeRequired(),
                'change_reason' => $this->getChangeReason(),
            ],
        ]);
    }

    /**
     * 변경 사유 반환
     *
     * @return string
     */
    private function getChangeReason()
    {
        if ($this->checkPasswordChangeRequired()) {
            return 'password_expired';
        }

        return 'user_initiated';
    }

    /**
     * 사용자 비밀번호 업데이트
     *
     * 호출 순서:
     * 1. hashPassword() - 비밀번호 해시화
     * 2. setPasswordChangedAt() - 변경 시간 기록
     * 3. setPasswordExpiryDate() - 만료일 설정
     * 4. resetForceChangeFlags() - 강제 변경 플래그 초기화
     *
     * @param  \App\Models\User  $user
     * @param  string  $newPassword
     */
    private function updateUserPassword($user, $newPassword)
    {
        // 비밀번호 해시화
        $user->password = $this->hashPassword($newPassword);

        // 변경 시간 기록
        $this->setPasswordChangedAt($user);

        // 만료일 설정
        $this->setPasswordExpiryDate($user);

        // 강제 변경 플래그 초기화
        $this->resetForceChangeFlags($user);
    }

    /**
     * 비밀번호 해시화
     *
     * @param  string  $password
     * @return string
     */
    private function hashPassword($password)
    {
        return Hash::make($password);
    }

    /**
     * 비밀번호 변경 시간 기록
     *
     * @param  \App\Models\User  $user
     */
    private function setPasswordChangedAt($user)
    {
        $user->password_changed_at = now();
    }

    /**
     * 비밀번호 만료일 설정
     *
     * @param  \App\Models\User  $user
     */
    private function setPasswordExpiryDate($user)
    {
        $expiryDays = config('admin.setting.password.expiry_days',
            $this->config['password_policy']['expiry_days']);

        if ($expiryDays > 0) {
            $user->password_expires_at = now()->addDays($expiryDays);
        } else {
            $user->password_expires_at = null;
        }
    }

    /**
     * 강제 변경 플래그 초기화
     *
     * @param  \App\Models\User  $user
     */
    private function resetForceChangeFlags($user)
    {
        if (isset($user->force_password_change)) {
            $user->force_password_change = false;
        }
        if (isset($user->password_must_change)) {
            $user->password_must_change = false;
        }
    }

    /**
     * 사용자 정보 저장
     *
     * @param  \App\Models\User  $user
     */
    private function saveUser($user)
    {
        $user->save();
    }

    /**
     * 활동 로그 기록
     *
     * @param  \App\Models\User  $user
     */
    private function logUserActivity($user, Request $request)
    {
        if (! $this->config['logging']['log_password_changes']) {
            return;
        }

        AdminUserLog::log('password_changed', $user, [
            'ip_address' => $request->ip(),
            'forced_change' => $this->checkPasswordChangeRequired(),
        ]);
    }

    /**
     * 세션 플래그 정리
     */
    private function clearSessionFlags()
    {
        session()->forget([
            $this->config['session']['required_flag'],
            $this->config['session']['user_id_key'],
        ]);
    }

    /**
     * 성공 알림 설정
     */
    private function setSuccessNotification()
    {
        session()->flash('notification', [
            'type' => 'success',
            'title' => $this->config['messages']['change_success_title'],
            'message' => $this->config['messages']['change_success'],
        ]);
    }

    /**
     * 에러 표시
     *
     * @param  string  $field
     * @param  string  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    private function displayError($field, $message)
    {
        return back()->withErrors([$field => $message])->withInput();
    }

    /**
     * 비밀번호 변경 폼 표시
     *
     * @param  bool  $required
     * @return \Illuminate\View\View
     */
    private function displayChangeForm($required)
    {
        return view($this->config['viewPaths']['change_form'], [
            'required' => $required,
            'user' => $this->getCurrentUser(),
        ]);
    }

    /**
     * 로그인 페이지로 리다이렉트
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToLogin()
    {
        session()->flash('notification', [
            'type' => 'error',
            'title' => $this->config['messages']['login_required_title'],
            'message' => $this->config['messages']['login_required'],
        ]);

        return redirect()->route($this->config['routes']['login']);
    }

    /**
     * 대시보드로 리다이렉트
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToDashboard()
    {
        return redirect()->route($this->config['routes']['dashboard']);
    }
}

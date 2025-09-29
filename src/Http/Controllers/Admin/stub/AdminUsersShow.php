<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\admin\App\Services\JsonConfigService;

/**
 * 사용자 상세 정보 표시 컨트롤러
 * 
 * 사용자 상세 정보 표시 및 다양한 관리 작업을 처리합니다.
 * Livewire 컴포넌트(AdminShow)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminUsers
 * @author  @jiny/admin Team
 * @since   1.0.0
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminShow Component
 * ├── hookShowing($data)                  [표시 전 데이터 가공]
 * │   ├── 날짜 형식 변환
 * │   └── Boolean 값 라벨 변환
 * ├── hookShowed($data)                   [표시 후 처리]
 * └── [커스텀 액션 훅]
 *     ├── hookCustomPasswordResetForce()  [비밀번호 변경 강제]
 *     │   ├── users 테이블 업데이트
 *     │   ├── admin_user_passwords 기록
 *     │   └── admin_user_logs 로깅
 *     ├── hookCustomPasswordResetCancel() [비밀번호 강제 해제]
 *     ├── hookCustomPasswordExpiryExtend()[만료 기간 연장]
 *     ├── hookCustomEmailVerify()         [이메일 강제 인증]
 *     ├── hookCustomEmailUnverify()       [이메일 인증 취소]
 *     ├── hookCustomAccountActivate()     [계정 활성화]
 *     ├── hookCustomAccountDeactivate()   [계정 비활성화]
 *     └── hookCustomPasswordReset()       [비밀번호/계정 초기화]
 *         ├── reset_attempts              [실패 횟수 초기화]
 *         ├── unlock_account              [계정 잠금 해제]
 *         └── force_password_change       [비밀번호 변경 강제]
 * ```
 * 
 * ## 주요 기능
 * - 사용자 상세 정보 표시
 * - 비밀번호 관리 (강제 변경, 만료 연장)
 * - 이메일 인증 관리
 * - 계정 활성화/비활성화
 * - 로그인 실패 초기화
 * - 모든 작업에 대한 감사 로깅
 * 
 * ## 보안 및 로깅
 * - 모든 관리 작업은 admin_user_logs에 기록
 * - 비밀번호 관련 작업은 admin_user_passwords에 추가 기록
 * - IP 주소와 User Agent 정보 저장
 * - 관리자 정보와 함께 감사 추적
 */
class AdminUsersShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_usertypes';
        $query = DB::table($tableName);

        // 기본 where 조건 적용
        if (isset($this->jsonData['table']['where']['default'])) {
            foreach ($this->jsonData['table']['where']['default'] as $condition) {
                if (count($condition) === 3) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                } elseif (count($condition) === 2) {
                    $query->where($condition[0], $condition[1]);
                }
            }
        }

        $item = $query->where('id', $id)->first();

        if (! $item) {
            $redirectUrl = isset($this->jsonData['route']['name'])
                ? route($this->jsonData['route']['name'].'.index')
                : '/admin/usertype';

            return redirect($redirectUrl)
                ->with('error', 'User을(를) 찾을 수 없습니다.');
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
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUsers.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // Set title from data or use default
        $title = $data['title'] ?? $data['name'] ?? 'User Details';

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
            'subtitle' => 'User 상세 정보',
        ]);
    }

    /**
     * 상세보기 표시 전에 호출됩니다.
     */
    public function hookShowing($wire, $data)
    {
        // 날짜 형식 지정
        $dateFormat = $this->jsonData['show']['display']['datetimeFormat'] ?? 'Y-m-d H:i:s';

        if (isset($data['created_at'])) {
            $data['created_at_formatted'] = date($dateFormat, strtotime($data['created_at']));
        }

        if (isset($data['updated_at'])) {
            $data['updated_at_formatted'] = date($dateFormat, strtotime($data['updated_at']));
        }

        // Boolean 라벨 처리
        $booleanLabels = $this->jsonData['show']['display']['booleanLabels'] ?? [
            'true' => 'Enabled',
            'false' => 'Disabled',
        ];

        if (isset($data['enable'])) {
            $data['enable_label'] = $data['enable'] ? $booleanLabels['true'] : $booleanLabels['false'];
        }

        return $data;
    }

    /**
     * Hook: 조회 후 데이터 가공
     */
    public function hookShowed($wire, $data)
    {
        return $data;
    }

    /**
     * Hook: 비밀번호 변경 강제
     */
    public function hookCustomPasswordResetForce($wire, $params)
    {
        \Log::info('hookCustomPasswordResetForce called', ['params' => $params]);

        $userId = $params['id'] ?? null;

        if (! $userId) {
            \Log::error('User ID not found in params');
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            // 사용자 정보 조회
            $user = DB::table('users')->where('id', $userId)->first();
            \Log::info('User found', ['user_id' => $userId, 'user_exists' => ! is_null($user)]);

            if (! $user) {
                session()->flash('error', '사용자를 찾을 수 없습니다.');

                return;
            }

            // 사용자 테이블 업데이트 - 비밀번호 변경 강제
            $updateResult = DB::table('users')
                ->where('id', $userId)
                ->update([
                    'password_must_change' => true,
                    'force_password_change' => true,
                    'updated_at' => now(),
                ]);
            \Log::info('User table updated', ['user_id' => $userId, 'rows_affected' => $updateResult]);

            // admin_user_passwords 테이블에 기록 (패스워드 이력)
            try {
                DB::table('admin_user_passwords')->insert([
                    'user_id' => $userId,
                    'password_hash' => $user->password,
                    'changed_at' => now(),
                    'expires_at' => now()->addDay(), // 1일 내 변경 필요
                    'changed_by_ip' => request()->ip(),
                    'changed_by_user_agent' => request()->userAgent(),
                    'change_reason' => '관리자가 비밀번호 변경 강제 설정',
                    'is_temporary' => false,
                    'is_expired' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                \Log::info('admin_user_passwords record inserted', ['user_id' => $userId]);
            } catch (\Exception $e) {
                \Log::error('Failed to insert admin_user_passwords', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }

            // admin_user_password_logs 테이블에 활동 로그 기록
            DB::table('admin_user_password_logs')->insert([
                'user_id' => $userId,
                'action' => 'force_change',
                'description' => '관리자가 비밀번호 변경을 강제 설정했습니다',
                'performed_by' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // admin_user_logs 테이블에도 활동 로그 기록
            DB::table('admin_user_logs')->insert([
                'user_id' => $userId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => 'force_password_change',
                'description' => '관리자가 비밀번호 변경을 강제 설정했습니다',
                'details' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_email' => auth()->user()->email ?? 'unknown',
                    'reason' => 'Admin forced password change',
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            session()->flash('success', '비밀번호 변경이 강제 설정되었습니다. 사용자는 다음 로그인 시 비밀번호를 변경해야 합니다.');

            // Livewire 컴포넌트 새로고침
            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            session()->flash('error', '작업 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * Hook: 비밀번호 변경 강제 해제
     */
    public function hookCustomPasswordResetCancel($wire, $params)
    {
        $userId = $params['id'] ?? null;

        if (! $userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            // 사용자 정보 조회
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                session()->flash('error', '사용자를 찾을 수 없습니다.');

                return;
            }

            // 사용자 테이블 업데이트 - 비밀번호 변경 강제 해제
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'password_must_change' => false,
                    'force_password_change' => false,
                    'updated_at' => now(),
                ]);

            // admin_user_passwords 테이블에 기록
            DB::table('admin_user_passwords')->insert([
                'user_id' => $userId,
                'password_hash' => $user->password,
                'changed_at' => now(),
                'expires_at' => null, // 만료 없음
                'changed_by_ip' => request()->ip(),
                'changed_by_user_agent' => request()->userAgent(),
                'change_reason' => '관리자가 비밀번호 변경 강제 해제',
                'is_temporary' => false,
                'is_expired' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // admin_user_password_logs 테이블에 활동 로그 기록
            DB::table('admin_user_password_logs')->insert([
                'user_id' => $userId,
                'action' => 'force_cancel',
                'description' => '관리자가 비밀번호 변경 강제를 해제했습니다',
                'performed_by' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // admin_user_logs 테이블에도 활동 로그 기록
            DB::table('admin_user_logs')->insert([
                'user_id' => $userId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => 'cancel_force_password_change',
                'description' => '관리자가 비밀번호 변경 강제를 해제했습니다',
                'details' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_email' => auth()->user()->email ?? 'unknown',
                    'reason' => 'Admin cancelled forced password change',
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            session()->flash('success', '비밀번호 변경 강제 설정이 해제되었습니다.');

            // Livewire 컴포넌트 새로고침
            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            session()->flash('error', '작업 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * Hook: 비밀번호 만료 기간 연장
     */
    public function hookCustomPasswordExpiryExtend($wire, $params)
    {
        $userId = $params['id'] ?? null;

        if (! $userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            // 사용자 정보 조회
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                session()->flash('error', '사용자를 찾을 수 없습니다.');

                return;
            }

            // 설정에서 연장 기간 가져오기 (기본 90일)
            $extensionDays = 90; // 설정 파일에서 읽어올 수도 있음

            // 현재 만료일 확인
            $currentExpiryDate = $user->password_expires_at ?
                \Carbon\Carbon::parse($user->password_expires_at) :
                now();

            // 만료일이 이미 지났으면 현재 시점부터, 아니면 현재 만료일부터 연장
            if ($currentExpiryDate->isPast()) {
                $newExpiryDate = now()->addDays($extensionDays);
                $extensionNote = "만료된 비밀번호를 현재로부터 {$extensionDays}일 연장";
            } else {
                $newExpiryDate = $currentExpiryDate->addDays($extensionDays);
                $extensionNote = "기존 만료일로부터 {$extensionDays}일 연장";
            }

            // 사용자 테이블 업데이트
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'password_expires_at' => $newExpiryDate,
                    'updated_at' => now(),
                ]);

            // admin_user_password_logs 테이블에 활동 로그 기록
            DB::table('admin_user_password_logs')->insert([
                'user_id' => $userId,
                'action' => 'password_expiry_extended',
                'description' => "관리자가 비밀번호 만료 기간을 {$extensionDays}일 연장했습니다",
                'metadata' => json_encode([
                    'old_expiry_date' => $user->password_expires_at,
                    'new_expiry_date' => $newExpiryDate->toDateTimeString(),
                    'extension_days' => $extensionDays,
                    'extension_note' => $extensionNote,
                ]),
                'performed_by' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // admin_user_logs 테이블에도 활동 로그 기록
            DB::table('admin_user_logs')->insert([
                'user_id' => $userId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => 'password_expiry_extended',
                'description' => "비밀번호 만료 기간이 {$extensionDays}일 연장되었습니다",
                'details' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_email' => auth()->user()->email ?? 'unknown',
                    'old_expiry_date' => $user->password_expires_at,
                    'new_expiry_date' => $newExpiryDate->toDateTimeString(),
                    'extension_days' => $extensionDays,
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            session()->flash('success', "비밀번호 만료 기간이 {$extensionDays}일 연장되었습니다. 새 만료일: ".$newExpiryDate->format('Y-m-d'));

            // Livewire 컴포넌트 새로고침
            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            \Log::error('Password expiry extension failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', '비밀번호 만료 연장 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * Hook: 이메일 강제 인증
     */
    public function hookCustomEmailVerify($wire, $params)
    {
        $userId = $params['id'] ?? null;

        if (! $userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                session()->flash('error', '사용자를 찾을 수 없습니다.');

                return;
            }

            // 이메일 인증 처리
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);

            // 로그 기록
            DB::table('admin_user_logs')->insert([
                'user_id' => $userId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => 'email_verified',
                'description' => '관리자가 이메일을 강제 인증했습니다',
                'details' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_email' => auth()->user()->email ?? 'unknown',
                    'verified_at' => now()->toDateTimeString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            session()->flash('success', '이메일이 성공적으로 인증되었습니다.');

            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            \Log::error('Email verification failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', '이메일 인증 처리 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * Hook: 이메일 인증 취소
     */
    public function hookCustomEmailUnverify($wire, $params)
    {
        $userId = $params['id'] ?? null;

        if (! $userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                session()->flash('error', '사용자를 찾을 수 없습니다.');

                return;
            }

            // 이메일 인증 취소
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'email_verified_at' => null,
                    'updated_at' => now(),
                ]);

            // 로그 기록
            DB::table('admin_user_logs')->insert([
                'user_id' => $userId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => 'email_unverified',
                'description' => '관리자가 이메일 인증을 취소했습니다',
                'details' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_email' => auth()->user()->email ?? 'unknown',
                    'unverified_at' => now()->toDateTimeString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            session()->flash('success', '이메일 인증이 취소되었습니다.');

            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            \Log::error('Email unverification failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', '이메일 인증 취소 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * Hook: 계정 활성화
     */
    public function hookCustomAccountActivate($wire, $params)
    {
        $userId = $params['id'] ?? null;

        if (! $userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                session()->flash('error', '사용자를 찾을 수 없습니다.');

                return;
            }

            // 계정 활성화
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'is_active' => true,
                    'enable' => true,
                    'account_locked_until' => null,
                    'failed_login_attempts' => 0,
                    'updated_at' => now(),
                ]);

            // 로그 기록
            DB::table('admin_user_logs')->insert([
                'user_id' => $userId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => 'account_activated',
                'description' => '관리자가 계정을 활성화했습니다',
                'details' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_email' => auth()->user()->email ?? 'unknown',
                    'activated_at' => now()->toDateTimeString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            session()->flash('success', '계정이 활성화되었습니다.');

            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            \Log::error('Account activation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', '계정 활성화 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * Hook: 계정 비활성화
     */
    public function hookCustomAccountDeactivate($wire, $params)
    {
        $userId = $params['id'] ?? null;

        if (! $userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                session()->flash('error', '사용자를 찾을 수 없습니다.');

                return;
            }

            // 계정 비활성화
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'is_active' => false,
                    'enable' => false,
                    'updated_at' => now(),
                ]);

            // 로그 기록
            DB::table('admin_user_logs')->insert([
                'user_id' => $userId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => 'account_deactivated',
                'description' => '관리자가 계정을 비활성화했습니다',
                'details' => json_encode([
                    'admin_id' => auth()->id(),
                    'admin_email' => auth()->user()->email ?? 'unknown',
                    'deactivated_at' => now()->toDateTimeString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
                'created_at' => now(),
            ]);

            session()->flash('success', '계정이 비활성화되었습니다.');

            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            \Log::error('Account deactivation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', '계정 비활성화 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * Hook: 비밀번호 재설정 및 계정 잠금 해제
     */
    public function hookCustomPasswordReset($wire, $params)
    {
        $userId = $params['id'] ?? null;
        $action = $params['action'] ?? 'reset';

        if (! $userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');

            return;
        }

        try {
            switch ($action) {
                case 'reset_attempts':
                    // 비밀번호 실패 횟수 초기화
                    DB::table('users')
                        ->where('id', $userId)
                        ->update([
                            'failed_login_attempts' => 0,
                            'account_locked_until' => null,
                            'updated_at' => now(),
                        ]);

                    // 관련 로그 기록
                    DB::table('admin_user_logs')->insert([
                        'user_id' => $userId,
                        'action' => 'password_reset',
                        'description' => 'Admin reset failed login attempts',
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'logged_at' => now(),
                        'created_at' => now(),
                    ]);

                    session()->flash('success', '로그인 실패 횟수가 초기화되었습니다.');
                    break;

                case 'unlock_account':
                    // 계정 잠금 해제
                    DB::table('users')
                        ->where('id', $userId)
                        ->update([
                            'account_locked_until' => null,
                            'failed_login_attempts' => 0,
                            'updated_at' => now(),
                        ]);

                    // 관련 로그 기록
                    DB::table('admin_user_logs')->insert([
                        'user_id' => $userId,
                        'action' => 'account_unlock',
                        'description' => 'Admin unlocked user account',
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'logged_at' => now(),
                        'created_at' => now(),
                    ]);

                    session()->flash('success', '계정 잠금이 해제되었습니다.');
                    break;

                case 'force_password_change':
                    // 다음 로그인 시 비밀번호 변경 강제
                    $user = DB::table('users')->where('id', $userId)->first();

                    if (! $user) {
                        session()->flash('error', '사용자를 찾을 수 없습니다.');
                        break;
                    }

                    // 사용자 테이블 업데이트
                    DB::table('users')
                        ->where('id', $userId)
                        ->update([
                            'password_must_change' => true,
                            'force_password_change' => true,
                            'updated_at' => now(),
                        ]);

                    // admin_user_passwords 테이블에 기록
                    DB::table('admin_user_passwords')->insert([
                        'user_id' => $userId,
                        'password_hash' => $user->password,
                        'changed_at' => now(),
                        'expires_at' => now()->addDay(), // 1일 내 변경 필요
                        'changed_by_ip' => request()->ip(),
                        'changed_by_user_agent' => request()->userAgent(),
                        'change_reason' => '관리자가 비밀번호 변경 강제 설정',
                        'is_temporary' => false,
                        'is_expired' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // admin_user_logs 테이블에 활동 로그 기록
                    DB::table('admin_user_logs')->insert([
                        'user_id' => $userId,
                        'email' => $user->email,
                        'name' => $user->name,
                        'action' => 'force_password_change',
                        'description' => '관리자가 비밀번호 변경을 강제 설정했습니다',
                        'details' => json_encode([
                            'admin_id' => auth()->id(),
                            'admin_email' => auth()->user()->email ?? 'unknown',
                            'reason' => 'Admin forced password change',
                        ]),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'logged_at' => now(),
                        'created_at' => now(),
                    ]);

                    session()->flash('success', '다음 로그인 시 비밀번호 변경이 요구됩니다.');
                    break;

                default:
                    session()->flash('error', '알 수 없는 작업입니다.');
            }

            // Livewire 컴포넌트 새로고침
            if ($wire) {
                $wire->refreshData();
            }

        } catch (\Exception $e) {
            session()->flash('error', '작업 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }
}

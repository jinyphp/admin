<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Services\PasswordValidator;

/**
 * 사용자 수정 컨트롤러
 * 
 * 기존 사용자 정보를 수정하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminEdit)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminUsers
 * @author  @jiny/admin Team
 * @since   1.0.0
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminEdit Component
 * ├── hookEditing($form)                  [수정 폼 초기화]
 * │   ├── admin_user_types 테이블 조회
 * │   ├── isAdmin 필드 boolean 변환
 * │   └── 패스워드 필드 제거 (보안)
 * ├── hookFormEmail($value)               [이메일 실시간 검증]
 * │   ├── 이메일 형식 검증
 * │   └── 중복 체크 (자기 자신 제외)
 * ├── hookFormPassword($value)            [패스워드 실시간 검증]
 * │   └── PasswordValidator::validate() (비어있으면 스킵)
 * ├── hookFormPasswordConfirmation($value)[패스워드 확인 검증]
 * │   └── 일치 여부 체크
 * ├── hookUpdating($form)                 [업데이트 전 처리]
 * │   ├── 기존 데이터 조회 (utype 변경 감지용)
 * │   ├── 패스워드 확인 검증
 * │   ├── PasswordValidator::validate() (입력시만)
 * │   ├── Hash::make() (입력시만)
 * │   └── updated_at 타임스탬프 갱신
 * └── hookUpdated($form)                  [업데이트 후 처리]
 *     └── admin_user_types 카운트 조정
 *         ├── 이전 타입 카운트 감소
 *         └── 새 타입 카운트 증가
 * ```
 * 
 * ## 특이사항
 * - 패스워드는 선택적 수정 (비어있으면 기존 값 유지)
 * - 사용자 타입 변경시 자동으로 카운트 조정
 * - 이메일 중복 체크시 자기 자신은 제외
 * 
 * ## 반환값 패턴
 * - hookUpdating:
 *   - 성공: array (처리된 폼 데이터)
 *   - 실패: string (에러 메시지)
 * - hookEditing: array (초기화된 폼 데이터)
 */
class AdminUsersEdit extends Controller
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
     * 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_usertypes';
        $data = DB::table($tableName)
            ->where('id', $id)
            ->first();

        if (! $data) {
            if (isset($this->jsonData['route']['name'])) {
                $redirectUrl = route($this->jsonData['route']['name']);
            } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
                $redirectUrl = route($this->jsonData['route']);
            } else {
                $redirectUrl = '/admin/users';
            }

            return redirect($redirectUrl)
                ->with('error', 'User을(를) 찾을 수 없습니다.');
        }

        // 객체를 배열로 변환
        $form = (array) $data;

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.edit view 경로 확인
        if (! isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUsers.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        return view($this->jsonData['template']['edit'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'form' => $form,
            'id' => $id,
            'title' => 'Edit User',
            'subtitle' => 'User을(를) 수정합니다.',
        ]);
    }

    /**
     * 수정폼이 실행될때 호출됩니다.
     */
    public function hookEditing($wire, $form)
    {
        // 사용자 타입 목록 가져오기
        $userTypes = DB::table('admin_user_types')
            ->where('enable', 1)
            ->orderBy('level', 'desc')
            ->get();

        // View에 전달할 데이터 설정
        if ($wire) {
            $wire->userTypes = $userTypes;
        }

        // isAdmin 필드를 boolean으로 변환
        $form['isAdmin'] = (bool) ($form['isAdmin'] ?? false);

        // 패스워드 필드는 비워둠 (보안상 기존 패스워드를 표시하지 않음)
        unset($form['password']);

        return $form;
    }

    /**
     * 데이터 업데이트 전에 호출됩니다.
     *
     * @return array|string 성공시 수정된 form 배열, 실패시 에러 메시지 문자열
     */
    public function hookUpdating($wire, $form)
    {
        // 기존 데이터 가져오기 (user type 변경 감지를 위해)
        $tableName = $this->jsonData['table']['name'] ?? 'users';
        $oldData = DB::table($tableName)
            ->where('id', $wire->id ?? $form['id'] ?? 0)
            ->first();

        // 이전 utype 값을 wire에 저장 (hookUpdated에서 사용)
        if ($wire && $oldData) {
            $wire->oldUtype = $oldData->utype;
        }

        // 패스워드 확인 필드 검증
        if (isset($form['password']) && isset($form['password_confirmation'])) {
            if ($form['password'] !== $form['password_confirmation']) {
                $errorMessage = '패스워드와 패스워드 확인이 일치하지 않습니다.';

                // Livewire 컴포넌트에 에러 전달
                if ($wire && method_exists($wire, 'addError')) {
                    $wire->addError('form.password_confirmation', $errorMessage);
                }

                return $errorMessage;
            }
        }

        // 디버깅: 패스워드 필드 확인
        \Log::info('hookUpdating - form keys: '.implode(', ', array_keys($form)));
        if (isset($form['password'])) {
            \Log::info('hookUpdating - password value: '.$form['password']);
        }

        // 패스워드가 입력된 경우에만 검증 및 해싱
        if (isset($form['password']) && ! empty($form['password'])) {
            \Log::info('Password validation starting for: '.$form['password']);
            $passwordValidator = new PasswordValidator;

            // 사용자 정보 준비 (유사성 체크용)
            $userData = [
                'name' => $form['name'] ?? '',
                'email' => $form['email'] ?? '',
            ];

            // 패스워드 유효성 검증
            if (! $passwordValidator->validate($form['password'], $userData)) {
                // 검증 실패 시 에러 메시지 문자열 반환
                $errors = $passwordValidator->getErrors();
                $errorMessage = '패스워드 검증 실패: '.implode(' ', $errors);

                \Log::error('Password validation failed: '.$errorMessage);

                // Livewire 컴포넌트에 에러 전달
                if ($wire && method_exists($wire, 'addError')) {
                    foreach ($errors as $error) {
                        $wire->addError('form.password', $error);
                    }
                }

                // 에러 메시지 문자열 반환 (배열이 아님)
                // dd($errorMessage);
                return $errorMessage;
            }

            // 검증 통과 시 패스워드 해싱
            $form['password'] = Hash::make($form['password']);
        } else {
            // 패스워드가 비어있으면 업데이트하지 않음
            unset($form['password']);
        }

        // isAdmin 필드 처리 (체크박스)
        $form['isAdmin'] = isset($form['isAdmin']) ? 1 : 0;

        // ID 제거 (업데이트 시 필요 없음)
        unset($form['id']);
        unset($form['_token']);
        unset($form['_method']);
        unset($form['password_confirmation']);

        // updated_at 타임스탬프 업데이트
        $form['updated_at'] = now();

        // 성공: 배열 반환
        return $form;
    }

    /**
     * 데이터 업데이트 후에 호출됩니다.
     */
    public function hookUpdated($wire, $form)
    {
        // 사용자 타입이 변경된 경우 카운트 업데이트
        $oldUtype = $wire->oldUtype ?? null;
        $newUtype = $form['utype'] ?? null;

        if ($oldUtype !== $newUtype) {
            // 이전 타입의 카운트 감소
            if ($oldUtype) {
                DB::table('admin_user_types')
                    ->where('code', $oldUtype)
                    ->where('cnt', '>', 0)
                    ->decrement('cnt');
            }

            // 새 타입의 카운트 증가
            if ($newUtype) {
                DB::table('admin_user_types')
                    ->where('code', $newUtype)
                    ->increment('cnt');
            }
        }

        return $form;
    }

    /**
     * Email 필드가 변경될 때 호출되는 hook
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  string  $value  입력된 이메일 값
     * @param  string  $fieldName  필드명 (email)
     * @return void
     */
    public function hookFormEmail($wire, $value, $fieldName = 'email')
    {
        // 이메일 형식 검증
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $wire->addError('form.email', '올바른 이메일 형식이 아닙니다.');

            return;
        }

        // 현재 수정 중인 사용자의 ID 가져오기
        $currentId = $wire->id ?? $wire->form['id'] ?? 0;

        // 이메일 중복 체크 (자기 자신은 제외)
        $exists = DB::table('users')
            ->where('email', $value)
            ->where('id', '!=', $currentId)
            ->exists();

        if ($exists) {
            $wire->addError('form.email', '이미 등록된 이메일입니다.');
        } else {
            // 에러 초기화
            $wire->resetErrorBag('form.email');
        }
    }

    /**
     * Password 필드가 변경될 때 호출되는 hook
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  string  $value  입력된 패스워드 값
     * @param  string  $fieldName  필드명 (password)
     * @return void
     */
    public function hookFormPassword($wire, $value, $fieldName = 'password')
    {
        // 패스워드가 비어있으면 검증 스킵 (수정 시에는 선택 사항)
        if (empty($value)) {
            $wire->resetErrorBag('form.password');

            return;
        }

        // PasswordValidator를 사용하여 동일한 규칙 적용
        $passwordValidator = new PasswordValidator;

        // 사용자 정보 준비 (유사성 체크용)
        $userData = [
            'name' => $wire->form['name'] ?? '',
            'email' => $wire->form['email'] ?? '',
        ];

        // 패스워드 유효성 검증
        if (! $passwordValidator->validate($value, $userData)) {
            // 검증 실패 시 첫 번째 에러만 표시 (실시간 검증에서는 한 번에 하나씩)
            $errors = $passwordValidator->getErrors();
            if (! empty($errors)) {
                $wire->addError('form.password', $errors[0]);
            }
        } else {
            // 검증 통과 시 에러 초기화
            $wire->resetErrorBag('form.password');
        }
    }

    /**
     * Password Confirmation 필드가 변경될 때 호출되는 hook
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  string  $value  입력된 패스워드 확인 값
     * @param  string  $fieldName  필드명 (password_confirmation)
     * @return void
     */
    public function hookFormPasswordConfirmation($wire, $value, $fieldName = 'password_confirmation')
    {
        // 원본 패스워드 가져오기
        $password = $wire->form['password'] ?? '';

        // 패스워드가 입력되지 않았으면 확인도 필요 없음
        if (empty($password)) {
            $wire->resetErrorBag('form.password_confirmation');

            return;
        }

        // 패스워드 일치 검증
        if ($password !== $value) {
            $wire->addError('form.password_confirmation', '패스워드가 일치하지 않습니다.');
        } else {
            $wire->resetErrorBag('form.password_confirmation');
        }
    }
}

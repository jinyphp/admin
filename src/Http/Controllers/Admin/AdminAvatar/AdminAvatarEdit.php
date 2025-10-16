<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminAvatar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Auth\Services\AvatarUploadService;

/**
 * 아바타 수정 컨트롤러
 * 
 * 기존 사용자의 아바타 정보를 수정하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminEdit)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminAvatar
 * @since   1.0.0
 */
class AdminAvatarEdit extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 수정 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  mixed    $id       수정할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'users';
        $data = DB::table($tableName)->where('id', $id)->first();
        
        if (!$data) {
            return response('Error: 데이터를 찾을 수 없습니다.', 404);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // template.edit view 경로 확인
        if (!isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
        }

        // 객체를 배열로 변환 (form 변수 생성)
        $form = (array) $data;

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminAvatar.json';
        $settingsPath = $jsonPath;

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['edit'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'data' => $data,
            'form' => $form,
            'id' => $id,
        ]);
    }

    /**
     * Hook: 수정 폼 초기화
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  기존 데이터
     * @return array
     */
    public function hookEditing($wire, $form)
    {
        // 사용자 타입 옵션 로드
        $userTypes = DB::table('admin_user_types')
            ->select('code', 'name')
            ->where('enable', 1)
            ->orderBy('pos')
            ->get();

        // Livewire 컴포넌트에 사용자 타입 옵션 전달
        if ($wire) {
            $wire->userTypeOptions = $userTypes->pluck('name', 'code')->toArray();
            
            // 현재 아바타 이미지 URL 저장
            $wire->currentAvatar = $form['avatar'] ?? '/images/default-avatar.png';
        }

        // isAdmin 필드 boolean 변환
        if (isset($form['isAdmin'])) {
            $form['isAdmin'] = (bool) $form['isAdmin'];
        }

        // 패스워드 필드 제거 (보안)
        unset($form['password']);

        return $form;
    }

    /**
     * Hook: 업데이트 전 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  수정된 데이터
     * @return array|string 성공시 배열, 실패시 에러 메시지
     */
    public function hookUpdating($wire, $form)
    {
        // ID 가져오기 (wire의 id 속성 또는 form 배열에서)
        $id = $wire->id ?? $form['id'] ?? null;
        
        if (!$id) {
            return '수정할 데이터의 ID를 찾을 수 없습니다.';
        }
        
        // 기존 데이터 조회 (utype 변경 감지용)
        $oldData = DB::table('users')
            ->where('id', $id)
            ->first();

        // 이전 utype 저장
        if ($wire) {
            $wire->oldUtype = $oldData->utype ?? null;
        }

        // 패스워드 처리 (입력된 경우만)
        if (!empty($form['password'])) {
            $form['password'] = Hash::make($form['password']);
        } else {
            unset($form['password']);
        }

        // 아바타 이미지 업로드 처리
        if ($wire && $wire->photo) {
            // Livewire의 TemporaryUploadedFile 객체 처리
            $file = $wire->photo;

            // 파일 유효성 검증
            if (!$file->isValid()) {
                return '아바타 이미지 업로드에 실패했습니다.';
            }

            // AvatarUploadService를 사용하여 파일 업로드
            try {
                $avatarService = new AvatarUploadService();
                $userUuid = $oldData->uuid ?? null;

                \Log::info('AdminAvatarEdit: Starting avatar upload', [
                    'user_id' => $id,
                    'user_uuid' => $userUuid,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ]);

                $uploadResult = $avatarService->upload($file, $userUuid);

                \Log::info('AdminAvatarEdit: Avatar upload successful', [
                    'path' => $uploadResult['path'],
                    'hash' => substr($uploadResult['hash'], 0, 16) . '...',
                ]);

                // 업로드된 파일 경로를 form에 설정
                $form['avatar'] = $uploadResult['path'];

                // 기존 아바타 파일이 있으면 삭제 (기본 이미지가 아닌 경우)
                if ($oldData->avatar &&
                    $oldData->avatar !== '/images/default-avatar.png' &&
                    !str_contains($oldData->avatar, '/default')) {
                    $avatarService->delete($oldData->avatar);
                }

                // photo 속성을 null로 설정하여 AdminEdit에서 중복 처리되지 않도록 함
                $wire->photo = null;

            } catch (\Exception $e) {
                \Log::error('AdminAvatarEdit: Avatar upload failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return '아바타 업로드 중 오류가 발생했습니다: ' . $e->getMessage();
            }
        }

        // 타임스탬프 갱신
        $form['updated_at'] = now();

        // password_confirmation 제거
        unset($form['password_confirmation']);

        return $form;
    }

    /**
     * Hook: 업데이트 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  업데이트된 데이터
     * @return void
     */
    public function hookUpdated($wire, $form)
    {
        // 사용자 타입이 변경된 경우 카운트 조정
        if ($wire && isset($wire->oldUtype) && $wire->oldUtype !== $form['utype']) {
            // 이전 타입 카운트 감소
            if ($wire->oldUtype) {
                DB::table('admin_user_types')
                    ->where('code', $wire->oldUtype)
                    ->decrement('cnt');
            }

            // 새 타입 카운트 증가
            if (!empty($form['utype'])) {
                DB::table('admin_user_types')
                    ->where('code', $form['utype'])
                    ->increment('cnt');
            }
        }
    }

    /**
     * Hook: 이메일 실시간 검증
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  string $value 입력값
     * @return void
     */
    public function hookFormEmail($wire, $value)
    {
        if (empty($value)) {
            return;
        }

        // 이메일 형식 검증
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $wire->addError('form.email', '올바른 이메일 형식이 아닙니다.');
            return;
        }

        // ID 가져오기
        $id = $wire->id ?? $wire->get('form.id') ?? null;
        
        // ID가 있는 경우에만 중복 체크 (자기 자신 제외)
        if ($id) {
            $exists = DB::table('users')
                ->where('email', $value)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                $wire->addError('form.email', '이미 사용중인 이메일입니다.');
            }
        }
    }
}
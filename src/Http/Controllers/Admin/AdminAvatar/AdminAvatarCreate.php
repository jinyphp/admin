<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminAvatar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Jiny\Admin\Services\JsonConfigService;

/**
 * 아바타 생성 컨트롤러
 * 
 * 새로운 사용자와 아바타를 생성하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminCreate)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminAvatar
 * @since   1.0.0
 */
class AdminAvatarCreate extends Controller
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
     * 생성 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 기본값 설정
        $form = [];

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // template.create view 경로 확인
        if (!isset($this->jsonData['template']['create'])) {
            return response('Error: 화면을 출력하기 위한 template.create 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminAvatar.json';
        $settingsPath = $jsonPath;

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'form' => $form,
        ]);
    }

    /**
     * Hook: 폼 초기화
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  폼 데이터
     * @return array
     */
    public function hookCreating($wire, $form)
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
        }

        // 기본값 설정
        $form['isAdmin'] = false;
        
        return $form;
    }

    /**
     * Hook: 저장 전 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  폼 데이터
     * @return array|string 성공시 배열, 실패시 에러 메시지
     */
    public function hookStoring($wire, $form)
    {
        // 패스워드 해시 처리
        if (!empty($form['password'])) {
            $form['password'] = Hash::make($form['password']);
        }

        // 아바타 이미지 처리
        if ($wire && $wire->hasFile('avatar')) {
            $file = $wire->file('avatar');
            
            // 파일 유효성 검증
            if (!$file->isValid()) {
                return '아바타 이미지 업로드에 실패했습니다.';
            }

            // 파일 저장
            $path = $file->store('avatars', 'public');
            if ($path) {
                $form['avatar'] = '/storage/' . $path;
            } else {
                return '아바타 이미지 저장에 실패했습니다.';
            }
        }

        // 타임스탬프 추가
        $form['created_at'] = now();
        $form['updated_at'] = now();

        // password_confirmation 제거
        unset($form['password_confirmation']);

        return $form;
    }

    /**
     * Hook: 저장 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  저장된 데이터
     * @return void
     */
    public function hookStored($wire, $form)
    {
        // 사용자 타입 카운트 증가
        if (!empty($form['utype'])) {
            DB::table('admin_user_types')
                ->where('code', $form['utype'])
                ->increment('cnt');
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

        // 중복 체크
        $exists = DB::table('users')
            ->where('email', $value)
            ->exists();

        if ($exists) {
            $wire->addError('form.email', '이미 사용중인 이메일입니다.');
        }
    }
}
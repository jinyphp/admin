<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminAvatar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * 아바타 상세 정보 표시 컨트롤러
 * 
 * 사용자의 아바타와 상세 정보를 표시합니다.
 * Livewire 컴포넌트(AdminShow)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminAvatar
 * @since   1.0.0
 */
class AdminAvatarShow extends Controller
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
     * 상세 정보 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  mixed    $id       조회할 레코드 ID
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
        $query = DB::table($tableName);

        // 기본 where 조건 적용
        if (isset($this->jsonData['table']['where']['default'])) {
            foreach ($this->jsonData['table']['where']['default'] as $condition) {
                $query->where($condition[0], $condition[1], $condition[2] ?? '=');
            }
        }

        $data = $query->where('id', $id)->first();
        
        if (!$data) {
            return response('Error: 데이터를 찾을 수 없습니다.', 404);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // template.show view 경로 확인
        if (!isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminAvatar.json';
        $settingsPath = $jsonPath;

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'data' => $data,
            'id' => $id,
        ]);
    }

    /**
     * Hook: 표시 전 데이터 가공
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  mixed  $data  표시할 데이터
     * @return mixed
     */
    public function hookShowing($wire, $data)
    {
        // 날짜 형식 변환
        if (isset($data->created_at)) {
            $data->created_at_formatted = date('Y-m-d H:i:s', strtotime($data->created_at));
        }
        if (isset($data->updated_at)) {
            $data->updated_at_formatted = date('Y-m-d H:i:s', strtotime($data->updated_at));
        }
        if (isset($data->email_verified_at)) {
            $data->email_verified_at_formatted = $data->email_verified_at ? 
                date('Y-m-d H:i:s', strtotime($data->email_verified_at)) : '미인증';
        }

        // Boolean 값 라벨 변환
        if (isset($data->isAdmin)) {
            $data->isAdmin_label = $data->isAdmin ? '관리자' : '일반 사용자';
        }

        // 사용자 타입 이름 가져오기
        if (!empty($data->utype)) {
            $userType = DB::table('admin_user_types')
                ->where('code', $data->utype)
                ->first();
            $data->utype_name = $userType ? $userType->name : $data->utype;
        }

        // 아바타 이미지 기본값 처리
        if (empty($data->avatar)) {
            $data->avatar = '/images/default-avatar.png';
        }

        // 아바타 이미지 전체 URL 생성
        if (!str_starts_with($data->avatar, 'http')) {
            $data->avatar_url = asset($data->avatar);
        } else {
            $data->avatar_url = $data->avatar;
        }

        return $data;
    }

    /**
     * Hook: 표시 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  mixed  $data  표시된 데이터
     * @return void
     */
    public function hookShowed($wire, $data)
    {
        // 조회 로그 기록 (필요시)
    }

    /**
     * Hook: 아바타 변경 처리
     * 
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  mixed  $data  사용자 데이터
     * @return array|string
     */
    public function hookCustomAvatarChange($wire, $data)
    {
        if (!$wire || !$wire->hasFile('newAvatar')) {
            return '새 아바타 이미지를 선택해주세요.';
        }

        $file = $wire->file('newAvatar');
        
        // 파일 유효성 검증
        if (!$file->isValid()) {
            return '아바타 이미지 업로드에 실패했습니다.';
        }

        // 파일 크기 검증 (2MB)
        if ($file->getSize() > 2048 * 1024) {
            return '아바타 이미지는 2MB를 초과할 수 없습니다.';
        }

        // 이미지 파일 검증
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
            return '아바타는 JPG, PNG, GIF 형식만 지원합니다.';
        }

        // 기존 이미지 삭제
        if ($data->avatar && $data->avatar !== '/images/default-avatar.png') {
            $oldPath = str_replace('/storage/', '', $data->avatar);
            Storage::disk('public')->delete($oldPath);
        }

        // 새 파일 저장
        $path = $file->store('avatars', 'public');
        if (!$path) {
            return '아바타 이미지 저장에 실패했습니다.';
        }

        // DB 업데이트
        DB::table('users')
            ->where('id', $data->id)
            ->update([
                'avatar' => '/storage/' . $path,
                'updated_at' => now()
            ]);

        return [
            'success' => true,
            'message' => '아바타가 성공적으로 변경되었습니다.',
            'new_avatar' => '/storage/' . $path
        ];
    }
}
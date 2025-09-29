<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminEmailTemplates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminEmailTemplates 삭제 컨트롤러
 * 
 * AdminEmailTemplates 삭제 처리를 담당합니다.
 * Livewire 컴포넌트(AdminDelete)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminEmailTemplates
 * @since   1.0.0
 */
class AdminEmailTemplatesDelete extends Controller
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
     * 삭제 확인 페이지 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  삭제할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_admin_email_templates';
        $data = DB::table($tableName)
            ->where('id', $id)
            ->first();

        if (! $data) {
            if (isset($this->jsonData['route']['name'])) {
                $redirectUrl = route($this->jsonData['route']['name']);
            } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
                $redirectUrl = route($this->jsonData['route']);
            } else {
                $redirectUrl = '/admin/admin_email_templates';
            }

            return redirect($redirectUrl)
                ->with('error', 'AdminEmailTemplates을(를) 찾을 수 없습니다.');
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.delete view 경로 확인 (없으면 기본 템플릿 사용)
        if (! isset($this->jsonData['template']['delete'])) {
            $this->jsonData['template']['delete'] = 'jiny-admin::template.delete';
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminEmailTemplates.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['delete'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $data,
            'id' => $id,
        ]);
    }

    /**
     * Hook: 삭제 전 처리
     *
     * 삭제 가능 여부를 검증하거나 관련 데이터를 처리합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  int  $id  삭제할 레코드 ID
     * @return bool|string true 반환시 삭제 진행, 문자열 반환시 에러 메시지
     */
    public function hookDeleting($wire, $id)
    {
        // 삭제 가능 여부 검증
        // 예: 관련 데이터 존재 여부 체크
        
        return true; // 삭제 진행
    }

    /**
     * Hook: 삭제 후 처리
     *
     * 삭제 후 추가 처리가 필요한 경우 구현합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  int  $id  삭제된 레코드 ID
     * @return void
     */
    public function hookDeleted($wire, $id)
    {
        // 관련 데이터 정리 등
    }
}
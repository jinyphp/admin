<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminIpblacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * Ipblacklist 상세 보기 컨트롤러
 * 
 * Ipblacklist 상세 정보 표시를 담당합니다.
 * Livewire 컴포넌트(AdminShow)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminIpblacklist
 * @since   1.0.0
 */
class AdminIpblacklistShow extends Controller
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
     * 상세 정보 페이지 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  조회할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_ipblacklists';
        $data = DB::table($tableName)
            ->where('id', $id)
            ->first();

        if (! $data) {
            if (isset($this->jsonData['route']['name'])) {
                $redirectUrl = route($this->jsonData['route']['name']);
            } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
                $redirectUrl = route($this->jsonData['route']);
            } else {
                $redirectUrl = '/admin/ipblacklists';
            }

            return redirect($redirectUrl)
                ->with('error', 'Ipblacklist을(를) 찾을 수 없습니다.');
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminIpblacklist.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['show'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $data,
            'id' => $id,
        ]);
    }

    /**
     * Hook: 상세 정보 표시 전 처리
     *
     * 데이터를 가공하거나 추가 정보를 로드합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  object  $data  조회된 데이터
     * @return object 가공된 데이터
     */
    public function hookShowing($wire, $data)
    {
        // 필요시 데이터 가공
        // 예: 관련 데이터 조인, 포맷팅 등
        
        return $data;
    }

    /**
     * Hook: 관련 데이터 로드
     *
     * 상세 페이지에 표시할 관련 데이터를 로드합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  int  $id  레코드 ID
     * @return array 추가 데이터
     */
    public function hookRelatedData($wire, $id)
    {
        // 관련 데이터 로드
        // 예: 연관 테이블 데이터, 통계 정보 등
        
        return [];
    }
}
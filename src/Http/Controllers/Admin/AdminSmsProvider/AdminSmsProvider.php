<?php
namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Services\JsonConfigService;

class AdminSmsProvider extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService();
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response("Error: JSON 데이터를 로드할 수 없습니다.", 500);
        }

        // template.index view 경로 확인
        if(!isset($this->jsonData['template']['index'])) {
            return response("Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.", 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsProvider.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // currentRoute 설정
        $this->jsonData['currentRoute'] = $this->jsonData['route']['name'] ?? 'admin.sms_provider';
        
        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'title' => $this->jsonData['title'] ?? 'SmsProvider Management',
            'subtitle' => $this->jsonData['subtitle'] ?? 'Manage sms_providers'
        ]);
    }

    /**
     * Hook: Livewire 컴포넌트의 데이터 조회 전 실행
     * 데이터베이스 쿼리 조건을 수정하거나 추가 로직을 실행할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 해당 값이 출력됨
     */
    public function hookIndexing($wire)
    {
        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     * 조회된 데이터를 가공하거나 추가 처리를 할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param mixed $rows 조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 커스터마이징된 헤더 설정
     */
    public function hookTableHeader($wire)
    {
        return $this->jsonData['index']['table']['columns'] ?? [];
    }

    /**
     * Hook: 페이지네이션 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 10,
            'perPageOptions' => [10, 25, 50, 100]
        ];
    }

    /**
     * Hook: 정렬 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 정렬 설정
     */
    public function hookSorting($wire)
    {
        return $this->jsonData['index']['sorting'] ?? [
            'default' => 'created_at',
            'direction' => 'desc'
        ];
    }

    /**
     * Hook: 검색 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => 'Search sms_providers...',
            'debounce' => 300
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        return $this->jsonData['index']['filters'] ?? [];
    }
}
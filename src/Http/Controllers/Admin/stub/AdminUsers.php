<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * 사용자 관리 메인 컨트롤러 (목록/인덱스 페이지)
 *
 * 시스템 사용자 계정 목록을 표시하고 관리하는 기능을 제공합니다.
 * Livewire 컴포넌트(AdminTable)와 Hook 패턴을 통해 동작합니다.
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminUsers
 * @author  @jiny/admin Team
 * @since   1.0.0
 * 
 * ## 관련 컨트롤러
 * - AdminUsersCreate: 사용자 생성 처리
 * - AdminUsersEdit: 사용자 수정 처리
 * - AdminUsersDelete: 사용자 삭제 처리
 * - AdminUsersShow: 사용자 상세 정보 표시
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminTable Component
 * ├── hookIndexing()           [데이터 조회 전]
 * ├── DB Query 실행
 * ├── hookIndexed($rows)       [데이터 조회 후]
 * │   └── admin_user_types 테이블 조인
 * ├── hookTableHeader()        [테이블 헤더 설정]
 * ├── hookPagination()         [페이지네이션 설정]
 * ├── hookSorting()           [정렬 설정]
 * ├── hookSearch()            [검색 설정]
 * └── hookFilters()           [필터 설정]
 * ```
 * 
 * ## JSON 설정 구조 (AdminUsers.json)
 * ```json
 * {
 *   "template": { "index": "..." },
 *   "route": { "name": "..." },
 *   "table": { "name": "users" },
 *   "index": {
 *     "table": { "columns": {...} },
 *     "pagination": { "perPage": 10 },
 *     "sorting": { "default": "created_at" },
 *     "search": { "placeholder": "..." },
 *     "filters": {...}
 *   }
 * }
 * ```
 */
class AdminUsers extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     *
     * AdminUsers.json 설정 파일을 로드하여 컨트롤러를 초기화합니다.
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * 사용자 목록 페이지 표시
     *
     * 등록된 사용자 목록을 테이블 형태로 표시합니다.
     * JSON 설정에 지정된 뷰 템플릿을 사용합니다.
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response 사용자 목록 뷰 또는 에러 응답
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (! isset($this->jsonData['template']['index'])) {
            return response('Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUsers.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
        ]);
    }

    /**
     * Hook: 데이터 조회 전 실행
     *
     * Livewire 컴포넌트가 사용자 데이터를 조회하기 전에 호출됩니다.
     * 쿼리 조건을 수정하거나 필터를 추가할 수 있습니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 해당 값이 출력됨
     */
    public function hookIndexing($wire)
    {
        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     *
     * 조회된 사용자 데이터에 추가 정보를 부가합니다.
     * 사용자 타입 코드를 읽기 쉽게 한글 이름으로 변환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  mixed  $rows  조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        // admin_user_types 테이블에서 사용자 타입 정보 가져오기
        if ($rows && count($rows) > 0) {
            $userTypes = DB::table('admin_user_types')
                ->select('code', 'name')
                ->get()
                ->keyBy('code');

            // 각 사용자에 타입 이름 추가
            foreach ($rows as $row) {
                if ($row->utype && isset($userTypes[$row->utype])) {
                    $row->utype_name = $userTypes[$row->utype]->name;
                } else {
                    $row->utype_name = null;
                }
            }
        }

        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * 사용자 목록 테이블의 컬럼 헤더를 설정합니다.
     * JSON 설정의 index.table.columns 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 커스터마이징된 헤더 설정
     */
    public function hookTableHeader($wire)
    {
        return $this->jsonData['index']['table']['columns'] ?? [];
    }

    /**
     * Hook: 페이지네이션 설정
     *
     * 한 페이지에 표시할 사용자 수와 옵션을 설정합니다.
     * JSON 설정의 index.pagination 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 10,
            'perPageOptions' => [10, 25, 50, 100],
        ];
    }

    /**
     * Hook: 정렬 설정
     *
     * 기본 정렬 컬럼과 방향을 설정합니다.
     * 기본값은 created_at 컬럼의 내림차순입니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 정렬 설정
     */
    public function hookSorting($wire)
    {
        return $this->jsonData['index']['sorting'] ?? [
            'default' => 'created_at',
            'direction' => 'desc',
        ];
    }

    /**
     * Hook: 검색 설정
     *
     * 사용자 검색 필드의 placeholder와 디바운스 시간을 설정합니다.
     * JSON 설정의 index.search 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => 'Search users...',
            'debounce' => 300,
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * 사용자 목록에 적용할 필터 옵션을 설정합니다.
     * JSON 설정의 index.filters 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        return $this->jsonData['index']['filters'] ?? [];
    }
}

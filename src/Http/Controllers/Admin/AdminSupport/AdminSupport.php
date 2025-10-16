<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSupport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * 지원 요청 관리 메인 컨트롤러 (목록/인덱스 페이지)
 *
 * 사용자들의 지원 요청 목록을 표시하고 관리하는 기능을 제공합니다.
 * Livewire 컴포넌트(AdminTable)와 Hook 패턴을 통해 동작합니다.
 */
class AdminSupport extends Controller
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
     * AdminSupport.json 설정 파일을 로드하여 컨트롤러를 초기화합니다.
     */
    public function __construct()
    {
        // admin 미들웨어 적용
        $this->middleware('admin');

        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * 지원 요청 목록 페이지 표시
     *
     * 등록된 지원 요청 목록을 테이블 형태로 표시합니다.
     * JSON 설정에 지정된 뷰 템플릿을 사용합니다.
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response 지원 요청 목록 뷰 또는 에러 응답
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
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminSupport.json';
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
     * Livewire 컴포넌트가 지원 요청 데이터를 조회하기 전에 호출됩니다.
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
     * 조회된 지원 요청 데이터에 추가 정보를 부가합니다.
     * 사용자 정보와 담당자 정보를 조인하여 이름을 표시합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  mixed  $rows  조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        // users 테이블에서 사용자 정보 가져오기
        if ($rows && count($rows) > 0) {
            $userIds = array_unique(array_filter([
                ...array_column($rows->toArray(), 'user_id'),
                ...array_column($rows->toArray(), 'assigned_to')
            ]));

            if (!empty($userIds)) {
                $users = DB::table('users')
                    ->select('id', 'name', 'email')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');

                // 각 지원 요청에 사용자와 담당자 이름 추가
                foreach ($rows as $row) {
                    // 요청자 정보
                    if ($row->user_id && isset($users[$row->user_id])) {
                        $row->user_name = $users[$row->user_id]->name;
                        $row->user_email = $users[$row->user_id]->email;
                    } else {
                        $row->user_name = $row->name; // 비회원 요청자
                        $row->user_email = $row->email;
                    }

                    // 담당자 정보
                    if ($row->assigned_to && isset($users[$row->assigned_to])) {
                        $row->assignee_name = $users[$row->assigned_to]->name;
                    } else {
                        $row->assignee_name = null;
                    }

                    // 상태 라벨
                    $statusLabels = [
                        'pending' => '대기중',
                        'in_progress' => '처리중',
                        'resolved' => '해결완료',
                        'closed' => '종료',
                    ];
                    $row->status_label = $statusLabels[$row->status] ?? '알 수 없음';

                    // 우선순위 라벨
                    $priorityLabels = [
                        'urgent' => '긴급',
                        'high' => '높음',
                        'normal' => '보통',
                        'low' => '낮음',
                    ];
                    $row->priority_label = $priorityLabels[$row->priority] ?? '보통';

                    // 유형 라벨
                    $typeLabels = [
                        'technical' => '기술 지원',
                        'inquiry' => '일반 문의',
                        'bug_report' => '버그 신고',
                        'feature_request' => '기능 요청',
                        'account' => '계정 관련',
                        'other' => '기타',
                    ];
                    $row->type_label = $typeLabels[$row->type] ?? '기타';
                }
            }
        }

        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * 지원 요청 목록 테이블의 컬럼 헤더를 설정합니다.
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
     * 한 페이지에 표시할 지원 요청 수와 옵션을 설정합니다.
     * JSON 설정의 index.pagination 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 15,
            'perPageOptions' => [10, 15, 25, 50, 100],
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
     * 지원 요청 검색 필드의 placeholder와 디바운스 시간을 설정합니다.
     * JSON 설정의 index.search 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => '제목, 내용, 이름, 이메일로 검색...',
            'debounce' => 300,
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * 지원 요청 목록에 적용할 필터 옵션을 설정합니다.
     * JSON 설정의 index.filters 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        $defaultFilters = [
            'status' => [
                'type' => 'select',
                'label' => '상태',
                'options' => [
                    '' => '모든 상태',
                    'pending' => '대기중',
                    'in_progress' => '처리중',
                    'resolved' => '해결완료',
                    'closed' => '종료',
                ]
            ],
            'type' => [
                'type' => 'select',
                'label' => '유형',
                'options' => [
                    '' => '모든 유형',
                    'technical' => '기술 지원',
                    'inquiry' => '일반 문의',
                    'bug_report' => '버그 신고',
                    'feature_request' => '기능 요청',
                    'account' => '계정 관련',
                    'other' => '기타',
                ]
            ],
            'priority' => [
                'type' => 'select',
                'label' => '우선순위',
                'options' => [
                    '' => '모든 우선순위',
                    'urgent' => '긴급',
                    'high' => '높음',
                    'normal' => '보통',
                    'low' => '낮음',
                ]
            ]
        ];

        return $this->jsonData['index']['filters'] ?? $defaultFilters;
    }
}
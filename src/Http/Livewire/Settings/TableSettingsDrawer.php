<?php

namespace Jiny\Admin\Http\Livewire\Settings;

use Illuminate\Support\Facades\File;
use Jiny\admin\App\Services\JsonConfigService;
use Livewire\Component;

/**
 * 테이블 설정 드로어 컴포넌트 (Table Settings Drawer Component)
 *
 * ## 목적 (Purpose)
 * 관리자 패널의 데이터 테이블 표시 설정을 실시간으로 관리하는 Livewire 컴포넌트입니다.
 * 사용자가 테이블의 외관과 동작을 커스터마이징할 수 있는 UI 드로어를 제공합니다.
 *
 * ## 주요 기능 (Main Features)
 * 1. JSON 설정 파일 읽기/쓰기 - 테이블 설정을 영구 저장
 * 2. 실시간 설정 변경 - 페이지 새로고침 없이 설정 적용
 * 3. 컬럼 표시/숨김 관리 - 사용자가 원하는 컬럼만 표시
 * 4. 페이지네이션 설정 - 페이지당 표시 개수 조정
 * 5. 정렬 옵션 설정 - 기본 정렬 필드와 방향 설정
 * 6. 기능 토글 - 검색, 대량 작업, 상태 토글 등 기능 활성화/비활성화
 *
 * ## 데이터 흐름 (Data Flow)
 * 1. 컨트롤러 → 컴포넌트: jsonPath 전달
 * 2. 컴포넌트 → JSON 파일: 설정 읽기
 * 3. 사용자 → UI: 설정 변경
 * 4. 컴포넌트 → JSON 파일: 변경사항 저장
 * 5. 컴포넌트 → 페이지: 새로고침 이벤트 발생
 *
 * ## 의존성 (Dependencies)
 * - Laravel Livewire 3.x
 * - Illuminate\Support\Facades\File (파일 시스템 접근)
 * - JSON 설정 파일 (테이블 구성 정보)
 *
 * ## 사용 예시 (Usage Example)
 * ```blade
 *
 * @livewire('jiny-admin::settings.table-settings-drawer', [
 *     'jsonPath' => $jsonPath  // 컨트롤러에서 전달받은 JSON 파일 경로
 * ])
 * ```
 *
 * @author JinyPHP Team
 *
 * @version 1.0.0
 *
 * @since 2024-01-01
 */
class TableSettingsDrawer extends Component
{
    /* ===========================
     * 상태 관리 속성 (State Properties)
     * =========================== */

    /**
     * 드로어 UI의 열림/닫힘 상태
     *
     * @var bool true=열림, false=닫힘
     *
     * @default false (초기 상태는 닫힘)
     */
    public $isOpen = false;

    /**
     * JSON 설정 파일에서 로드한 전체 설정 데이터
     *
     * ## 구조 예시 (Structure Example):
     * ```php
     * [
     *     'index' => [
     *         'pagination' => ['perPage' => 10],
     *         'sorting' => ['default' => 'created_at', 'direction' => 'desc'],
     *         'features' => ['enableSearch' => true, ...],
     *         'table' => ['columns' => [...]]
     *     ],
     *     'create' => [...],
     *     'edit' => [...]
     * ]
     * ```
     *
     * @var array 다차원 연관 배열
     */
    public $settings = [];

    /**
     * JSON 설정 파일의 절대 경로
     *
     * ## 경로 결정 우선순위:
     * 1. 컨트롤러에서 mount() 파라미터로 전달된 경로
     * 2. openWithPath() 메서드로 동적 전달된 경로
     * 3. 기본값 (fallback): base_path('jiny/admin2/App/Http/Controllers/Admin/AdminTemplates/AdminTemplates.json')
     *
     * @var string 파일 시스템 절대 경로
     *
     * @example "/Users/project/jiny/admin2/App/Http/Controllers/Admin/AdminTemplates/AdminTemplates.json"
     */
    public $jsonPath;

    /**
     * JSON 데이터 (jsonPath 대신 직접 전달받는 경우)
     *
     * @var array|null
     */
    public $jsonData;

    /**
     * JSON 설정 서비스
     * JSON 파일 읽기/쓰기를 위한 전용 서비스
     *
     * @var JsonConfigService
     */
    private $jsonConfigService;

    /* ===========================
     * 테이블 표시 설정 속성 (Table Display Settings)
     * =========================== */

    /**
     * 페이지당 표시할 데이터 행 수
     *
     * ## 허용 값 (Allowed Values):
     * - 일반적으로: 10, 20, 25, 50, 100
     * - 최소값: 1
     * - 최대값: 제한 없음 (성능 고려 필요)
     *
     * @var int 양의 정수
     *
     * @default 10
     */
    public $perPage = 10;

    /**
     * 페이지당 표시 개수 옵션 목록
     *
     * @var array
     */
    public $perPageOptions = [10, 20, 25, 50, 100];

    /**
     * 테이블 정렬 기준 필드명
     *
     * ## 일반적인 값 (Common Values):
     * - 'id': 고유 식별자 기준
     * - 'created_at': 생성일시 기준
     * - 'updated_at': 수정일시 기준
     * - 'name', 'title': 이름/제목 기준
     * - 'priority', 'order': 우선순위/순서 기준
     *
     * @var string 데이터베이스 컬럼명
     *
     * @default 'created_at'
     */
    public $sortField = 'created_at';

    /**
     * 정렬 방향
     *
     * ## 허용 값 (Allowed Values):
     * - 'asc': 오름차순 (1→2→3, A→B→C, 과거→현재)
     * - 'desc': 내림차순 (3→2→1, C→B→A, 현재→과거)
     *
     * @var string 'asc' 또는 'desc'
     *
     * @default 'desc'
     */
    public $sortDirection = 'desc';

    /**
     * 테이블에 표시할 컬럼 목록
     *
     * ## 배열 구조 (Array Structure):
     * - 각 요소는 컬럼의 키(key) 이름
     * - 순서는 표시 순서와 무관 (JSON 설정에서 결정)
     *
     * ## 예시 (Example):
     * ```php
     * ['checkbox', 'id', 'title', 'status', 'created_at', 'actions']
     * ```
     *
     * @var array 문자열 배열
     *
     * @default 기본 컬럼 세트
     */
    public $visibleColumns = [];

    /**
     * 검색 기능 활성화 여부
     *
     * ## 동작 (Behavior):
     * - true: 검색 입력 필드 표시, 실시간 필터링 활성화
     * - false: 검색 UI 숨김, 필터링 비활성화
     *
     * @var bool
     *
     * @default true
     */
    public $enableSearch = true;

    /**
     * 대량 작업 기능 활성화 여부
     *
     * ## 관련 기능 (Related Features):
     * - 체크박스를 통한 다중 선택
     * - 선택한 항목 일괄 삭제
     * - 선택한 항목 일괄 상태 변경
     * - 전체 선택/해제 기능
     *
     * @var bool
     *
     * @default true
     */
    public $enableBulkActions = true;

    /**
     * 페이지네이션 활성화 여부
     *
     * ## 동작 (Behavior):
     * - true: 페이지 나누기 UI 표시, perPage 설정 적용
     * - false: 모든 데이터를 한 페이지에 표시 (주의: 성능 문제 가능)
     *
     * @var bool
     *
     * @default true
     */
    public $enablePagination = true;

    /**
     * 상태 토글 기능 활성화 여부
     *
     * ## 적용 대상 (Applicable To):
     * - enable/disable 필드
     * - active/inactive 상태
     * - published/draft 상태
     * - 기타 boolean 타입 필드
     *
     * @var bool
     *
     * @default true
     */
    public $enableStatusToggle = true;

    /* ===========================
     * Livewire 이벤트 설정 (Event Configuration)
     * =========================== */

    /**
     * Livewire 이벤트 리스너 매핑
     *
     * ## 이벤트 설명 (Event Descriptions):
     *
     * ### 'openTableSettings' → 'open'
     * - 발생 시점: 설정 버튼 클릭
     * - 동작: 현재 설정을 다시 로드하고 드로어 열기
     * - 파라미터: 없음
     *
     * ### 'openSettingsDrawer' → 'openWithPath'
     * - 발생 시점: 다른 컴포넌트에서 특정 JSON 경로와 함께 호출
     * - 동작: 새 경로의 설정을 로드하고 드로어 열기
     * - 파라미터: jsonPath (문자열)
     *
     * @var array 이벤트명 => 메서드명 매핑
     */
    protected $listeners = [
        'openTableSettings' => 'open',        // 기본 열기 이벤트
        'openSettingsDrawer' => 'openWithPath', // 경로 지정 열기 이벤트
    ];

    /* ===========================
     * 초기화 메서드 (Initialization Methods)
     * =========================== */

    /**
     * 컴포넌트 마운트 (초기화)
     *
     * ## 실행 시점 (Execution Timing):
     * - 컴포넌트가 처음 렌더링될 때 1회 실행
     * - Livewire 라이프사이클의 첫 단계
     *
     * ## 처리 순서 (Processing Order):
     * 1. 드로어 상태를 닫힘으로 초기화
     * 2. JSON 경로 또는 데이터 설정
     * 3. 설정 파일 로드 및 파싱
     *
     * ## 파라미터 전달 방법 (Parameter Passing):
     * ```blade
     * @livewire('component-name', ['jsonPath' => '/path/to/settings.json'])
     * // 또는
     * @livewire('component-name', ['jsonData' => $jsonData])
     * ```
     *
     * @param  string|null  $jsonPath  JSON 설정 파일의 절대 경로 (선택적)
     * @param  array|null  $jsonData  JSON 데이터 직접 전달 (선택적)
     * @return void
     */
    public function mount($jsonPath = null, $jsonData = null)
    {
        // JsonConfigService 초기화
        $this->jsonConfigService = new JsonConfigService;

        // 드로어는 초기에 항상 닫힌 상태로 시작 (UX 원칙)
        $this->isOpen = false;

        // jsonData가 전달된 경우 우선 사용
        if ($jsonData !== null) {
            $this->jsonData = $jsonData;
            // jsonPath가 별도로 전달되지 않았으면 그대로 사용
            $this->jsonPath = $jsonPath;
        } else {
            // JSON 경로 설정: 우선순위 = 전달값 > 기본값
            $this->jsonPath = $jsonPath;
        }

        // 설정 파일 로드 및 초기 상태 설정
        $this->loadSettings();
    }

    /**
     * 특정 JSON 경로와 함께 드로어 열기
     *
     * ## 사용 시나리오 (Use Cases):
     * 1. 다른 테이블의 설정을 동적으로 로드
     * 2. 사용자별 커스텀 설정 파일 로드
     * 3. 테마별 설정 파일 전환
     *
     * ## 이벤트 발생 예시 (Event Dispatch Example):
     * ```php
     * $this->dispatch('openSettingsDrawer', jsonPath: '/path/to/custom.json');
     * ```
     *
     * @param  string  $jsonPath  로드할 JSON 파일의 절대 경로
     * @return void
     */
    public function openWithPath($jsonPath)
    {
        // JsonConfigService가 초기화되지 않은 경우 초기화
        if (! $this->jsonConfigService) {
            $this->jsonConfigService = new JsonConfigService;
        }

        // 새 경로 설정
        $this->jsonPath = $jsonPath;

        // 새 설정 로드
        $this->loadSettings();

        // 드로어 열기
        $this->isOpen = true;
    }

    /* ===========================
     * 설정 관리 메서드 (Settings Management Methods)
     * =========================== */

    /**
     * JSON 설정 파일 로드 및 파싱
     *
     * ## 처리 단계 (Processing Steps):
     * 1. 파일 존재 여부 확인
     * 2. JSON 파일 읽기 및 디코딩
     * 3. 각 설정값을 컴포넌트 속성에 매핑
     * 4. 유효성 검증 및 기본값 적용
     *
     * ## 오류 처리 (Error Handling):
     * - 파일 없음: 기본값 사용
     * - JSON 파싱 오류: 기본값 사용
     * - 필수 키 누락: 개별 기본값 사용
     *
     * ## 성능 고려사항 (Performance Considerations):
     * - 파일 I/O는 비용이 큰 작업
     * - 필요시에만 호출 (mount, open, openWithPath)
     * - 대용량 JSON 파일 주의 (권장: 1MB 이하)
     *
     * @return void
     *
     * @throws 없음 (모든 예외는 내부적으로 처리)
     */
    public function loadSettings()
    {
        try {
            // jsonData가 있으면 직접 사용, 없으면 파일에서 로드
            if ($this->jsonData !== null) {
                $this->settings = $this->jsonData;
            } else {
                // jsonConfigService가 초기화되지 않은 경우 초기화
                if (!$this->jsonConfigService) {
                    $this->jsonConfigService = new JsonConfigService;
                }
                // JsonConfigService를 사용하여 설정 로드
                $this->settings = $this->jsonConfigService->loadFromPath($this->jsonPath);
            }

            if ($this->settings !== null) {

                // index 섹션 추출 (테이블 목록 페이지 설정)
                $indexSettings = $this->settings['index'] ?? [];

                // 페이지네이션 설정 로드
                // Null 병합 연산자(??)로 안전하게 접근
                // index.pagination.perPage만 사용 (paging 설정은 제거됨)
                $this->perPage = $indexSettings['pagination']['perPage'] ?? 10;
                $this->perPageOptions = $indexSettings['pagination']['perPageOptions'] ?? [10, 20, 25, 50, 100];

                // 정렬 설정 로드
                $this->sortField = $indexSettings['sorting']['default'] ?? 'created_at';
                $this->sortDirection = $indexSettings['sorting']['direction'] ?? 'desc';

                // 기능 활성화 플래그 로드
                $features = $indexSettings['features'] ?? [];
                $this->enableSearch = $features['enableSearch'] ?? true;
                $this->enableBulkActions = $features['enableBulkActions'] ?? true;
                $this->enablePagination = $features['enablePagination'] ?? true;
                $this->enableStatusToggle = $features['enableStatusToggle'] ?? true;

                // 표시할 컬럼 목록 구성
                $this->visibleColumns = [];
                $columns = $indexSettings['table']['columns'] ?? [];

                // visible=true인 컬럼만 수집
                foreach ($columns as $key => $column) {
                    if ($column['visible'] ?? false) {
                        $this->visibleColumns[] = $key;
                    }
                }

                // 표시할 컬럼이 하나도 없으면 기본 세트 사용
                // (빈 테이블 방지)
                if (empty($this->visibleColumns)) {
                    $this->visibleColumns = ['checkbox', 'id', 'title', 'description', 'enable', 'created_at', 'actions'];
                }
            } else {
                // JSON 파일이 존재하지 않는 경우
                // 기본값으로 초기화
                $this->setDefaults();
            }
        } catch (\Exception $e) {
            // 예외 발생 시 (파일 읽기 오류, JSON 파싱 오류 등)
            // 로그에 기록하고 기본값 사용
            // TODO: 필요시 로깅 추가
            // \Log::error('Settings load error: ' . $e->getMessage());
            $this->setDefaults();
        }
    }

    /**
     * 기본값 설정
     *
     * ## 호출 시점 (When Called):
     * 1. JSON 파일이 존재하지 않을 때
     * 2. JSON 파싱 오류 발생 시
     * 3. resetToDefaults() 메서드 호출 시
     *
     * ## 기본값 선정 기준 (Default Value Criteria):
     * - 가장 일반적인 사용 사례 기준
     * - 성능과 사용성의 균형 고려
     * - 대부분의 테이블에 적합한 설정
     *
     * @return void
     */
    private function setDefaults()
    {
        // 페이지당 10개 표시 (적당한 스크롤)
        $this->perPage = 10;

        // 최신 데이터 우선 표시
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';

        // 모든 기능 활성화 (최대 기능성)
        $this->enableSearch = true;
        $this->enableBulkActions = true;
        $this->enablePagination = true;
        $this->enableStatusToggle = true;

        // 필수 컬럼 세트
        $this->visibleColumns = [
            'checkbox',     // 선택 기능
            'id',          // 식별자
            'title',       // 제목/이름
            'description', // 설명
            'enable',      // 상태
            'created_at',  // 생성일
            'actions',      // 작업 버튼
        ];
    }

    /* ===========================
     * UI 제어 메서드 (UI Control Methods)
     * =========================== */

    /**
     * 설정 드로어 열기
     *
     * ## 동작 순서 (Action Sequence):
     * 1. 최신 설정 다시 로드 (파일 변경 감지)
     * 2. 드로어 상태를 열림으로 변경
     * 3. UI 업데이트 트리거
     *
     * ## UI 효과 (UI Effects):
     * - 슬라이드 애니메이션으로 드로어 표시
     * - 배경 오버레이 활성화
     * - 포커스 트랩 활성화 (접근성)
     *
     * @return void
     *
     * @emits 없음
     */
    public function open()
    {
        // 최신 설정 다시 로드
        // (다른 사용자나 프로세스가 파일을 변경했을 가능성)
        $this->loadSettings();

        // 드로어 열기
        $this->isOpen = true;
    }

    /**
     * 설정 드로어 닫기
     *
     * ## 동작 (Behavior):
     * - 변경사항 저장하지 않고 닫기
     * - 임시 변경사항은 모두 폐기
     *
     * @return void
     *
     * @emits 없음
     */
    public function close()
    {
        $this->isOpen = false;
    }

    /* ===========================
     * 데이터 저장 메서드 (Data Persistence Methods)
     * =========================== */

    /**
     * 설정 저장
     *
     * ## 저장 프로세스 (Save Process):
     * 1. 메모리의 설정값을 settings 배열에 반영
     * 2. JSON 형식으로 인코딩
     * 3. 파일 시스템에 쓰기
     * 4. 성공 알림 표시
     * 5. 페이지 새로고침 트리거
     *
     * ## JSON 포맷 옵션 (JSON Format Options):
     * - JSON_PRETTY_PRINT: 읽기 쉬운 들여쓰기
     * - JSON_UNESCAPED_UNICODE: 한글 등 유니코드 문자 그대로 저장
     *
     * ## 트랜잭션 고려사항 (Transaction Considerations):
     * - 파일 쓰기는 원자적 작업이 아님
     * - 동시 쓰기 시 충돌 가능
     * - TODO: 파일 잠금 구현 고려
     *
     * @return void
     *
     * @emits settingsUpdated 설정 업데이트 완료 이벤트
     * @emits notify 사용자 알림 이벤트
     * @emits refresh-page 페이지 새로고침 이벤트
     */
    public function save()
    {
        // settings가 없으면 초기화
        if (!isset($this->settings['index'])) {
            $this->settings['index'] = [];
        }

        // ===== 1. 페이지네이션 설정 업데이트 =====
        if (!isset($this->settings['index']['pagination'])) {
            $this->settings['index']['pagination'] = [];
        }
        $this->settings['index']['pagination']['perPage'] = $this->perPage;
        $this->settings['index']['pagination']['perPageOptions'] = $this->perPageOptions;

        // ===== 2. 정렬 설정 업데이트 =====
        if (!isset($this->settings['index']['sorting'])) {
            $this->settings['index']['sorting'] = [];
        }
        $this->settings['index']['sorting']['default'] = $this->sortField;
        $this->settings['index']['sorting']['direction'] = $this->sortDirection;

        // ===== 3. 기능 플래그 업데이트 =====
        if (!isset($this->settings['index']['features'])) {
            $this->settings['index']['features'] = [];
        }
        $this->settings['index']['features']['enableSearch'] = $this->enableSearch;
        $this->settings['index']['features']['enableBulkActions'] = $this->enableBulkActions;
        $this->settings['index']['features']['enablePagination'] = $this->enablePagination;
        $this->settings['index']['features']['enableStatusToggle'] = $this->enableStatusToggle;

        // ===== 4. 컬럼 표시 설정 업데이트 =====
        // 컬럼 설정이 있는 경우에만 처리
        if (isset($this->settings['index']['table']['columns'])) {
            // 참조(&)를 사용하여 직접 수정
            foreach ($this->settings['index']['table']['columns'] as $key => &$column) {
                // visibleColumns 배열에 포함된 컬럼만 visible=true
                $column['visible'] = in_array($key, $this->visibleColumns);
            }
        }

        // ===== 5. JSON 파일에 저장 =====
        // jsonPath가 있는 경우에만 파일에 저장
        if ($this->jsonPath) {
            // jsonConfigService가 초기화되지 않은 경우 초기화
            if (!$this->jsonConfigService) {
                $this->jsonConfigService = new JsonConfigService;
            }
            // JSON_PRETTY_PRINT: 가독성을 위한 들여쓰기
            // JSON_UNESCAPED_UNICODE: 한글/이모지 등을 이스케이프하지 않음
            $this->jsonConfigService->save($this->jsonPath, $this->settings);
        }

        // jsonData로 전달받은 경우, 업데이트된 데이터를 다시 설정
        if ($this->jsonData !== null) {
            $this->jsonData = $this->settings;
        }

        // ===== 6. 이벤트 발생 =====

        // 다른 컴포넌트에 설정 업데이트 알림
        $this->dispatch('settingsUpdated');

        // 사용자에게 성공 메시지 표시
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Table settings updated successfully!',
        ]);

        // ===== 7. UI 업데이트 =====

        // 드로어 닫기
        $this->close();

        // 페이지 새로고침으로 변경사항 즉시 반영
        // JavaScript에서 window.location.reload() 실행
        $this->dispatch('refresh-page');
    }

    /**
     * 설정을 기본값으로 초기화
     *
     * ## 용도 (Purpose):
     * - 잘못된 설정 복구
     * - 초기 상태로 되돌리기
     * - 테스트 후 클린업
     *
     * ## 주의사항 (Caution):
     * - 저장하지 않으면 변경사항은 임시적
     * - save() 호출 시 영구 적용
     *
     * @return void
     */
    public function resetToDefaults()
    {
        $this->setDefaults();
    }

    /* ===========================
     * 렌더링 메서드 (Rendering Method)
     * =========================== */

    /**
     * 컴포넌트 뷰 렌더링
     *
     * ## 뷰 파일 위치 (View File Location):
     * - 상대 경로: jiny-admin::template.settings.table-settings-drawer
     * - 실제 경로: /jiny/admin2/resources/views/template/settings/table-settings-drawer.blade.php
     *
     * ## 뷰에 전달되는 데이터 (Data Passed to View):
     * - 모든 public 속성이 자동으로 전달됨
     * - $isOpen, $settings, $jsonPath, $perPage 등
     *
     * ## 렌더링 시점 (Rendering Timing):
     * - 컴포넌트 초기화 시
     * - 속성 변경 시
     * - 이벤트 처리 후
     *
     * @return \Illuminate\View\View Blade 뷰 인스턴스
     */
    public function render()
    {
        return view('jiny-admin::template.settings.table-settings-drawer');
    }
}

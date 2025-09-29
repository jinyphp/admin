<?php

namespace Jiny\Admin\Http\Livewire\Admin\AdminTemplates\Settings;

use Illuminate\Support\Facades\File;
use Jiny\admin\App\Services\JsonConfigService;
use Livewire\Component;

/**
 * DetailSettingsDrawer Component
 * 상세보기 설정 관리를 위한 Livewire 컴포넌트
 *
 * 이 컴포넌트는 관리자 템플릿의 상세보기 페이지 설정을 관리합니다.
 * JSON 파일을 통해 설정을 읽고 저장하며, 동적으로 설정을 변경할 수 있습니다.
 *
 * This component manages the detail view settings for admin templates.
 * It reads and saves settings through a JSON file and allows dynamic configuration changes.
 *
 * @version 1.0
 *
 * @since 2025.01
 *
 * ## 주요 기능 (Key Features)
 *
 * 1. **날짜 형식 설정 (Date Format Settings)**
 *    - 상세보기에서 표시되는 날짜의 형식을 설정
 *    - PHP date() 함수의 형식 문자열 사용
 *    - 예: 'Y-m-d H:i:s', 'd/m/Y', 'M j, Y'
 *
 * 2. **기능 버튼 제어 (Feature Button Control)**
 *    - 편집, 삭제, 생성, 목록 버튼의 표시 여부 제어
 *    - 각 버튼을 개별적으로 활성화/비활성화 가능
 *    - 권한 기반 UI 제어에 활용
 *
 * 3. **섹션 가시성 관리 (Section Visibility Management)**
 *    - 정보 섹션, 타임스탬프 섹션 등의 표시 여부 제어
 *    - 필요한 정보만 선택적으로 표시 가능
 *    - 사용자 경험 최적화
 *
 * 4. **필드 가시성 제어 (Field Visibility Control)**
 *    - 각 섹션 내의 개별 필드 표시 여부 제어
 *    - 민감한 정보 숨기기 가능
 *    - 화면 단순화 지원
 *
 * ## 데이터 흐름 (Data Flow)
 *
 * 1. **초기 로드 (Initial Load)**
 *    Controller → JSON Path → Component Mount → Load Settings
 *
 * 2. **설정 변경 (Settings Change)**
 *    User Input → Component Properties → Validation → Update State
 *
 * 3. **저장 프로세스 (Save Process)**
 *    Save Button → Collect Properties → Update JSON → File Write → Emit Event
 *
 * 4. **리셋 프로세스 (Reset Process)**
 *    Reset Button → Load Defaults → Update Properties → Re-render
 *
 * ## JSON 구조 (JSON Structure)
 *
 * ```json
 * {
 *   "show": {
 *     "display": {
 *       "dateFormat": "Y-m-d H:i:s"
 *     },
 *     "features": {
 *       "enableEdit": true,
 *       "enableDelete": true,
 *       "enableCreate": true,
 *       "enableListButton": true
 *     },
 *     "sections": {
 *       "information": {
 *         "title": "Template Information",
 *         "fields": ["id", "title", "description", "enable"]
 *       },
 *       "timestamps": {
 *         "title": "Timestamps",
 *         "fields": ["created_at", "updated_at"]
 *       }
 *     },
 *     "lastUpdated": "2025-01-15T10:30:00+00:00"
 *   }
 * }
 * ```
 *
 * ## 이벤트 (Events)
 *
 * ### 수신 이벤트 (Listening Events)
 * - `openDrawer`: Drawer 열기 요청
 * - `openDetailSettings`: 상세 설정 열기 요청
 *
 * ### 발신 이벤트 (Emitting Events)
 * - `settingsUpdated`: 설정이 업데이트됨
 * - `notify`: 사용자 알림 표시
 *
 * ## 사용 예제 (Usage Example)
 *
 * ### Blade Template에서 사용
 * ```blade
 *
 * @livewire('detail-settings-drawer', ['jsonPath' => $jsonPath])
 * ```
 *
 * ### 이벤트 호출
 * ```javascript
 * Livewire.emit('openDetailSettings');
 * ```
 *
 * ### 다른 컴포넌트에서 호출
 * ```php
 * $this->dispatch('openDetailSettings');
 * ```
 */
class DetailSettingsDrawer extends Component
{
    /**
     * Drawer 열림 상태
     * Drawer의 표시/숨김 상태를 제어합니다.
     *
     * Controls the visibility state of the drawer.
     *
     * @var bool
     *
     * @default false
     */
    public $isOpen = false;

    /**
     * 전체 설정 데이터
     * JSON 파일에서 로드된 전체 설정 배열입니다.
     *
     * Complete settings array loaded from JSON file.
     *
     * @var array
     *
     * @example [
     *   'show' => [
     *     'display' => ['dateFormat' => 'Y-m-d H:i:s'],
     *     'features' => ['enableEdit' => true, ...],
     *     'sections' => [...]
     *   ]
     * ]
     */
    public $settings = [];

    /**
     * JSON 파일 경로
     * 설정을 읽고 저장할 JSON 파일의 전체 경로입니다.
     *
     * Full path to the JSON configuration file.
     *
     * @var string|null
     *
     * @example '/path/to/project/jiny/admin2/App/Http/Controllers/Admin/AdminTemplates/AdminTemplates.json'
     */
    public $jsonPath;

    // Display settings
    // 표시 설정

    /**
     * 날짜 표시 형식
     * 상세보기에서 날짜를 표시할 때 사용할 형식입니다.
     *
     * Date format for displaying dates in detail view.
     *
     * @var string
     *
     * @default 'Y-m-d H:i:s'
     *
     * @example 'Y-m-d H:i:s' // 2025-01-15 14:30:00
     * @example 'd/m/Y H:i' // 15/01/2025 14:30
     * @example 'M j, Y' // Jan 15, 2025
     *
     * @see https://www.php.net/manual/en/datetime.format.php
     */
    public $dateFormat = 'Y-m-d H:i:s';

    /**
     * 편집 버튼 활성화 여부
     * 상세보기 페이지에서 편집 버튼을 표시할지 결정합니다.
     *
     * Whether to show the edit button in detail view.
     *
     * @var bool
     *
     * @default true
     */
    public $enableEdit = true;

    /**
     * 삭제 버튼 활성화 여부
     * 상세보기 페이지에서 삭제 버튼을 표시할지 결정합니다.
     *
     * Whether to show the delete button in detail view.
     *
     * @var bool
     *
     * @default true
     */
    public $enableDelete = true;

    /**
     * 생성 버튼 활성화 여부
     * 상세보기 페이지에서 새 항목 생성 버튼을 표시할지 결정합니다.
     *
     * Whether to show the create new button in detail view.
     *
     * @var bool
     *
     * @default true
     */
    public $enableCreate = true;

    /**
     * 목록 버튼 활성화 여부
     * 상세보기 페이지에서 목록으로 돌아가기 버튼을 표시할지 결정합니다.
     *
     * Whether to show the back to list button in detail view.
     *
     * @var bool
     *
     * @default true
     */
    public $enableListButton = true;

    /**
     * 표시할 섹션 목록
     * 상세보기에서 표시할 섹션들의 키 배열입니다.
     *
     * Array of section keys to display in detail view.
     *
     * @var array
     *
     * @default ['information', 'timestamps']
     *
     * @example ['information', 'timestamps', 'metadata', 'relations']
     */
    public $visibleSections = ['information', 'timestamps'];

    /**
     * 표시할 필드 목록
     * 모든 섹션에서 표시할 필드들의 통합 배열입니다.
     *
     * Consolidated array of all fields to display across sections.
     *
     * @var array
     *
     * @default []
     *
     * @example ['id', 'title', 'description', 'created_at', 'updated_at']
     */
    public $visibleFields = [];

    /**
     * JSON 설정 서비스
     * JSON 파일 읽기/쓰기를 위한 전용 서비스
     *
     * @var JsonConfigService
     */
    private $jsonConfigService;

    /**
     * Livewire 이벤트 리스너
     * 이 컴포넌트가 수신하는 이벤트와 처리 메서드를 매핑합니다.
     *
     * Maps events this component listens to with their handler methods.
     *
     * @var array
     *
     * @see https://livewire.laravel.com/docs/events
     */
    protected $listeners = [
        'openDrawer' => 'open',           // 일반 drawer 열기 이벤트
        'openDetailSettings' => 'open',     // 상세 설정 전용 열기 이벤트
    ];

    /**
     * 컴포넌트 초기화
     * 컴포넌트가 렌더링되기 전에 한 번 실행됩니다.
     *
     * Component initialization - runs once before rendering.
     *
     * @param  string|null  $jsonPath  JSON 설정 파일 경로 (JSON configuration file path)
     * @return void
     *
     * ## 처리 과정 (Processing Steps)
     *
     * 1. **JSON 경로 설정 (Set JSON Path)**
     *    - 전달된 경로가 있으면 사용
     *    - 없으면 기본 경로 사용
     *
     * 2. **설정 로드 (Load Settings)**
     *    - loadSettings() 메서드 호출
     *    - JSON 파일에서 설정 읽기
     *
     * 3. **초기 상태 설정 (Set Initial State)**
     *    - Drawer를 닫힌 상태로 초기화
     *
     * @example
     * // Blade에서 jsonPath 전달
     *
     * @livewire('detail-settings-drawer', ['jsonPath' => $jsonPath])
     *
     * // 기본 경로 사용
     * @livewire('detail-settings-drawer')
     */
    public function mount($jsonPath = null)
    {
        // JsonConfigService 초기화
        $this->jsonConfigService = new JsonConfigService;

        // jsonPath가 전달되지 않으면 오류 방지를 위한 기본값 설정
        // Set default path if jsonPath is not provided
        $this->jsonPath = $jsonPath ?: base_path('jiny/admin2/App/Http/Controllers/Admin/AdminTemplates/AdminTemplates.json');

        // JSON 파일에서 설정 로드
        // Load settings from JSON file
        $this->loadSettings();

        // Drawer를 닫힌 상태로 초기화
        // Ensure isOpen is false on mount
        $this->isOpen = false;
    }

    /**
     * JSON 파일에서 설정 로드
     * 지정된 경로의 JSON 파일을 읽어 컴포넌트 속성을 초기화합니다.
     *
     * Load settings from JSON file and initialize component properties.
     *
     * @return void
     *
     * ## 로드 프로세스 (Loading Process)
     *
     * 1. **파일 존재 확인 (Check File Existence)**
     *    - File::exists()로 JSON 파일 확인
     *    - 파일이 없으면 기본값 유지
     *
     * 2. **JSON 파싱 (Parse JSON)**
     *    - File::get()으로 파일 내용 읽기
     *    - json_decode()로 배열 변환
     *
     * 3. **표시 설정 로드 (Load Display Settings)**
     *    - dateFormat 설정 읽기
     *    - 기본값: 'Y-m-d H:i:s'
     *
     * 4. **기능 설정 로드 (Load Feature Settings)**
     *    - 각 버튼의 활성화 상태 읽기
     *    - 기본값: 모두 true
     *
     * 5. **섹션 설정 로드 (Load Section Settings)**
     *    - 표시할 섹션 키 추출
     *    - 각 섹션의 필드 목록 수집
     *
     * ## 오류 처리 (Error Handling)
     *
     * - 파일이 없으면 기본값 사용
     * - JSON 파싱 실패 시 빈 배열 처리
     * - 각 설정이 없으면 기본값 사용 (null coalescing)
     *
     * @example JSON 구조
     * {
     *   "show": {
     *     "display": {"dateFormat": "Y-m-d"},
     *     "features": {
     *       "enableEdit": true,
     *       "enableDelete": false
     *     },
     *     "sections": {
     *       "information": {
     *         "fields": ["id", "title"]
     *       }
     *     }
     *   }
     * }
     */
    public function loadSettings()
    {
        // JsonConfigService를 사용하여 설정 로드
        $this->settings = $this->jsonConfigService->loadFromPath($this->jsonPath);

        if ($this->settings !== null) {

            // 상세보기 설정 로드
            // Load show settings
            $showSettings = $this->settings['show'] ?? [];

            // 날짜 형식 설정 로드
            // Load date format setting
            $this->dateFormat = $showSettings['display']['dateFormat'] ?? 'Y-m-d H:i:s';

            // 기능 버튼 설정 로드
            // Load features
            $features = $showSettings['features'] ?? [];
            $this->enableEdit = $features['enableEdit'] ?? true;
            $this->enableDelete = $features['enableDelete'] ?? true;
            $this->enableCreate = $features['enableCreate'] ?? true;
            $this->enableListButton = $features['enableListButton'] ?? true;

            // 표시할 섹션 및 필드 로드
            // Load visible sections and fields
            $sections = $showSettings['sections'] ?? [];
            $this->visibleSections = array_keys($sections);

            // 모든 섹션의 필드를 통합 배열로 수집
            // Collect all fields from all sections
            foreach ($sections as $section) {
                if (isset($section['fields'])) {
                    $this->visibleFields = array_merge($this->visibleFields, $section['fields']);
                }
            }
        }
    }

    /**
     * Drawer 열기
     * 설정 drawer를 표시합니다.
     *
     * Open the settings drawer.
     *
     * @return void
     *
     * @listens openDrawer
     * @listens openDetailSettings
     */
    public function open()
    {
        $this->isOpen = true;
    }

    /**
     * Drawer 닫기
     * 설정 drawer를 숨깁니다.
     *
     * Close the settings drawer.
     *
     * @return void
     */
    public function close()
    {
        $this->isOpen = false;
    }

    /**
     * 설정 저장
     * 현재 설정을 JSON 파일에 저장합니다.
     *
     * Save current settings to JSON file.
     *
     * @return void
     *
     * ## 저장 프로세스 (Save Process)
     *
     * 1. **표시 설정 업데이트 (Update Display Settings)**
     *    - dateFormat을 JSON 구조에 반영
     *
     * 2. **기능 설정 업데이트 (Update Feature Settings)**
     *    - 각 버튼의 활성화 상태 저장
     *    - enableEdit, enableDelete, enableCreate, enableListButton
     *
     * 3. **섹션 재구성 (Rebuild Sections)**
     *    - visibleSections 배열 기반으로 섹션 재구성
     *    - 각 섹션에 대한 제목과 필드 설정
     *
     * 4. **타임스탬프 업데이트 (Update Timestamp)**
     *    - lastUpdated를 현재 시간으로 설정
     *    - ISO 8601 형식 사용
     *
     * 5. **파일 저장 (Write to File)**
     *    - JSON_PRETTY_PRINT: 읽기 쉬운 포맷팅
     *    - JSON_UNESCAPED_UNICODE: 유니코드 문자 보존
     *
     * 6. **이벤트 발송 (Emit Events)**
     *    - settingsUpdated: 설정 변경 알림
     *    - notify: 사용자 알림 표시
     *
     * 7. **Drawer 닫기 (Close Drawer)**
     *
     * ## 섹션 구조 (Section Structure)
     *
     * - information: 템플릿 기본 정보
     *   - fields: id, title, description, enable
     *
     * - timestamps: 시간 정보
     *   - fields: created_at, updated_at
     *
     * @emits settingsUpdated 설정이 업데이트됨
     * @emits notify 사용자 알림 표시
     */
    public function save()
    {
        // JSON에 표시 설정 업데이트
        // Update settings in the JSON
        $this->settings['show']['display']['dateFormat'] = $this->dateFormat;

        // 기능 버튼 설정 업데이트
        // Update features
        $this->settings['show']['features']['enableEdit'] = $this->enableEdit;
        $this->settings['show']['features']['enableDelete'] = $this->enableDelete;
        $this->settings['show']['features']['enableCreate'] = $this->enableCreate;
        $this->settings['show']['features']['enableListButton'] = $this->enableListButton;

        // 표시할 섹션 재구성
        // Update visible sections
        $sections = [];

        // 정보 섹션 설정
        if (in_array('information', $this->visibleSections)) {
            $sections['information'] = [
                'title' => 'Template Information',
                'fields' => ['id', 'title', 'description', 'enable'],
            ];
        }

        // 타임스탬프 섹션 설정
        if (in_array('timestamps', $this->visibleSections)) {
            $sections['timestamps'] = [
                'title' => 'Timestamps',
                'fields' => ['created_at', 'updated_at'],
            ];
        }

        $this->settings['show']['sections'] = $sections;

        // 마지막 업데이트 시간 기록
        // Update timestamp
        $this->settings['show']['lastUpdated'] = now()->toIso8601String();

        // JsonConfigService를 사용하여 JSON 파일에 저장
        // Save to file using JsonConfigService
        $this->jsonConfigService->save($this->jsonPath, $this->settings);

        // 설정 업데이트 이벤트 발송
        $this->dispatch('settingsUpdated');

        // 성공 알림 표시
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Detail view settings updated successfully!',
        ]);

        // Drawer 닫기
        $this->close();
    }

    /**
     * 기본값으로 리셋
     * 모든 설정을 기본값으로 초기화합니다.
     *
     * Reset all settings to default values.
     *
     * @return void
     *
     * ## 기본값 (Default Values)
     *
     * - **날짜 형식**: 'Y-m-d H:i:s'
     * - **기능 버튼**: 모두 활성화 (true)
     * - **표시 섹션**: information, timestamps
     * - **표시 필드**: id, title, description, enable, created_at, updated_at
     *
     * ## 사용 시나리오 (Usage Scenarios)
     *
     * 1. 잘못된 설정으로 인한 문제 해결
     * 2. 초기 상태로 되돌리기
     * 3. 테스트 후 원래 상태 복원
     *
     * @note 이 메서드는 메모리상의 값만 변경하며, save() 호출 전까지 파일에 반영되지 않습니다.
     */
    public function resetToDefaults()
    {
        // 날짜 형식 기본값
        $this->dateFormat = 'Y-m-d H:i:s';

        // 모든 기능 버튼 활성화
        $this->enableEdit = true;
        $this->enableDelete = true;
        $this->enableCreate = true;
        $this->enableListButton = true;

        // 기본 섹션 설정
        $this->visibleSections = ['information', 'timestamps'];

        // 기본 필드 설정
        $this->visibleFields = ['id', 'title', 'description', 'enable', 'created_at', 'updated_at'];
    }

    /**
     * 컴포넌트 렌더링
     * Blade 템플릿을 반환하여 UI를 렌더링합니다.
     *
     * Render the component by returning the Blade template.
     *
     * @return \Illuminate\View\View
     *
     * ## 템플릿 경로 (Template Path)
     *
     * - 패키지: jiny-admin
     * - 경로: template/settings/detail-settings-drawer.blade.php
     * - 전체: resources/views/template/settings/detail-settings-drawer.blade.php
     *
     * ## 템플릿에서 사용 가능한 변수 (Available Variables in Template)
     *
     * - `$isOpen`: Drawer 열림 상태
     * - `$dateFormat`: 날짜 표시 형식
     * - `$enableEdit`: 편집 버튼 활성화 여부
     * - `$enableDelete`: 삭제 버튼 활성화 여부
     * - `$enableCreate`: 생성 버튼 활성화 여부
     * - `$enableListButton`: 목록 버튼 활성화 여부
     * - `$visibleSections`: 표시할 섹션 배열
     * - `$visibleFields`: 표시할 필드 배열
     *
     * ## 템플릿 구조 (Template Structure)
     *
     * 1. Drawer 컨테이너 (x-show="$isOpen")
     * 2. 헤더 (제목 및 닫기 버튼)
     * 3. 설정 폼
     *    - 날짜 형식 입력
     *    - 기능 토글 스위치들
     *    - 섹션 체크박스
     * 4. 액션 버튼 (저장, 리셋, 취소)
     */
    public function render()
    {
        return view('jiny-admin::template.settings.detail-settings-drawer');
    }
}

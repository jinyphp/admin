<?php

namespace Jiny\Admin\Http\Livewire\Settings;

use Illuminate\Support\Facades\File;
use Jiny\admin\App\Services\JsonConfigService;
use Livewire\Component;

/**
 * 상세보기 설정 드로어 컴포넌트 (Show/Detail View Settings Drawer Component)
 *
 * ## 목적 (Purpose)
 * 관리자 패널의 데이터 상세보기 페이지 설정을 실시간으로 관리하는 Livewire 컴포넌트입니다.
 * 읽기 전용 뷰의 표시 형식, 날짜 포맷, 불린 라벨 등을 커스터마이징할 수 있습니다.
 *
 * ## 주요 기능 (Main Features)
 * 1. 날짜 형식 설정 - 다양한 날짜/시간 표시 형식
 * 2. 불린 값 라벨 - true/false를 사용자 친화적 텍스트로 변환
 * 3. 필드 토글 - 선택적 필드 표시/숨김
 * 4. 섹션 토글 - 정보 그룹별 접기/펼치기
 * 5. 날짜 형식 선택 - 절대/상대 시간 표시
 * 6. 데이터 포맷팅 - 숫자, 통화, 백분율 등 형식화
 *
 * ## 데이터 흐름 (Data Flow)
 * 1. 컨트롤러 → 컴포넌트: jsonPath 전달
 * 2. 컴포넌트 → JSON 파일: show 섹션 설정 읽기
 * 3. 사용자 → UI: 표시 설정 변경
 * 4. 컴포넌트 → JSON 파일: 변경사항 저장
 * 5. 컴포넌트 → 페이지: 새로고침 이벤트 발생
 *
 * ## JSON 구조 (JSON Structure)
 * ```json
 * {
 *     "show": {
 *         "display": {
 *             "dateFormat": "Y-m-d H:i:s",
 *             "booleanLabels": {
 *                 "true": "Enabled",
 *                 "false": "Disabled"
 *             }
 *         },
 *         "settingsDrawer": {
 *             "enableFieldToggle": true,
 *             "enableDateFormat": true,
 *             "enableSectionToggle": true
 *         }
 *     }
 * }
 * ```
 *
 * ## 사용 예시 (Usage Example)
 * ```blade
 *
 * @livewire('jiny-admin::settings.show-settings-drawer', [
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
class ShowSettingsDrawer extends Component
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
     * ## 주요 섹션 (Main Sections):
     * - show: 상세보기 관련 설정
     * - display: 표시 형식 설정
     * - fields: 필드별 표시 옵션
     * - sections: 섹션 구성
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
     * 3. 기본값: base_path('jiny/admin2/.../AdminTemplates.json')
     *
     * @var string 파일 시스템 절대 경로
     */
    public $jsonPath;

    /**
     * JSON 설정 서비스
     * JSON 파일 읽기/쓰기를 위한 전용 서비스
     *
     * @var JsonConfigService
     */
    private $jsonConfigService;

    /* ===========================
     * 표시 설정 속성 (Display Settings)
     * =========================== */

    /**
     * 날짜/시간 표시 형식
     *
     * ## PHP 날짜 형식 문자 (PHP Date Format Characters):
     * - Y: 4자리 연도 (2024)
     * - m: 2자리 월 (01-12)
     * - d: 2자리 일 (01-31)
     * - H: 24시간 형식 시 (00-23)
     * - i: 분 (00-59)
     * - s: 초 (00-59)
     *
     * ## 일반적인 형식 예시 (Common Format Examples):
     * - 'Y-m-d H:i:s': 2024-01-15 14:30:45
     * - 'Y년 m월 d일': 2024년 01월 15일
     * - 'd/m/Y': 15/01/2024 (유럽 형식)
     * - 'm/d/Y': 01/15/2024 (미국 형식)
     * - 'F j, Y': January 15, 2024
     *
     * @var string PHP date() 형식 문자열
     *
     * @default 'Y-m-d H:i:s'
     */
    public $dateFormat = 'Y-m-d H:i:s';

    /**
     * 불린 true 값의 표시 라벨
     *
     * ## 사용 예시 (Usage Examples):
     * - 활성화 상태: 'Enabled', '활성', 'Active', 'On'
     * - 공개 상태: 'Published', '공개', 'Public', 'Visible'
     * - 승인 상태: 'Approved', '승인됨', 'Confirmed', 'Yes'
     *
     * ## 다국어 지원 (Internationalization):
     * - 영어: 'Enabled', 'Active', 'Yes'
     * - 한국어: '활성', '사용', '예'
     * - 일본어: '有効', 'はい'
     *
     * @var string 사용자에게 표시할 텍스트
     *
     * @default 'Enabled'
     */
    public $booleanTrueLabel = 'Enabled';

    /**
     * 불린 false 값의 표시 라벨
     *
     * ## 사용 예시 (Usage Examples):
     * - 비활성화 상태: 'Disabled', '비활성', 'Inactive', 'Off'
     * - 비공개 상태: 'Unpublished', '비공개', 'Private', 'Hidden'
     * - 미승인 상태: 'Pending', '대기중', 'Unconfirmed', 'No'
     *
     * ## 다국어 지원 (Internationalization):
     * - 영어: 'Disabled', 'Inactive', 'No'
     * - 한국어: '비활성', '미사용', '아니오'
     * - 일본어: '無効', 'いいえ'
     *
     * @var string 사용자에게 표시할 텍스트
     *
     * @default 'Disabled'
     */
    public $booleanFalseLabel = 'Disabled';

    /**
     * 필드 토글 기능 활성화
     *
     * ## 기능 설명 (Feature Description):
     * - 개별 필드 표시/숨김
     * - 사용자별 맞춤 뷰
     * - 중요 정보만 표시
     *
     * @var bool
     *
     * @default true
     */
    public $enableFieldToggle = true;

    /**
     * 날짜 형식 선택 기능 활성화
     *
     * ## 제공 옵션 (Available Options):
     * - 절대 시간: 정확한 날짜/시간
     * - 상대 시간: "3시간 전", "어제"
     * - 사용자 로케일 기반 형식
     *
     * @var bool
     *
     * @default true
     */
    public $enableDateFormat = true;

    /**
     * 섹션 토글 기능 활성화
     *
     * ## 섹션 유형 (Section Types):
     * - 기본 정보 (Basic Information)
     * - 상세 정보 (Detailed Information)
     * - 메타데이터 (Metadata)
     * - 타임스탬프 (Timestamps)
     * - 관련 데이터 (Related Data)
     *
     * ## UI 동작 (UI Behavior):
     * - 아코디언 스타일 접기/펼치기
     * - 섹션별 아이콘 표시
     * - 애니메이션 효과
     *
     * @var bool
     *
     * @default true
     */
    public $enableSectionToggle = true;

    /**
     * 수정 버튼 표시 여부
     *
     * ## 기능 설명 (Feature Description):
     * - 상세보기 페이지에서 수정 버튼 표시/숨김
     * - JSON 설정의 show.enableEdit 값과 연동
     * - 권한에 따른 조건부 표시 가능
     *
     * @var bool
     *
     * @default true
     */
    public $enableEdit = true;

    /**
     * 삭제 버튼 표시 여부
     *
     * ## 기능 설명 (Feature Description):
     * - 상세보기 페이지에서 삭제 버튼 표시/숨김
     * - JSON 설정의 show.enableDelete 값과 연동
     * - 삭제 확인 다이얼로그와 연동
     *
     * @var bool
     *
     * @default true
     */
    public $enableDelete = true;

    /**
     * 목록 버튼 표시 여부
     *
     * ## 기능 설명 (Feature Description):
     * - 상세보기 페이지에서 목록 버튼 표시/숨김
     * - JSON 설정의 show.enableListButton 값과 연동
     * - 목록 페이지로 이동 기능
     *
     * @var bool
     *
     * @default true
     */
    public $enableListButton = true;

    /* ===========================
     * Livewire 이벤트 설정 (Event Configuration)
     * =========================== */

    /**
     * Livewire 이벤트 리스너 매핑
     *
     * ## 이벤트 설명 (Event Descriptions):
     *
     * ### 'openShowSettings' → 'openWithPath'
     * - 발생 시점: 상세보기 페이지에서 설정 버튼 클릭
     * - 동작: JSON 경로와 함께 드로어 열기
     * - 파라미터: jsonPath (문자열)
     *
     * @var array 이벤트명 => 메서드명 매핑
     */
    protected $listeners = ['openShowSettings' => 'openWithPath'];

    /* ===========================
     * 초기화 메서드 (Initialization Methods)
     * =========================== */

    /**
     * 컴포넌트 마운트 (초기화)
     *
     * ## 초기화 순서 (Initialization Order):
     * 1. 드로어 상태를 닫힘으로 설정
     * 2. JSON 경로 설정 (전달값 또는 기본값)
     * 3. 설정 파일 로드
     * 4. 각 속성 초기화
     *
     * ## 중요 사항 (Important Notes):
     * - 컨트롤러에서 jsonPath 전달 권장
     * - 기본값은 폴백용
     *
     * @param  string|null  $jsonPath  JSON 설정 파일의 절대 경로
     * @return void
     */
    public function mount($jsonPath = null)
    {
        // JsonConfigService 초기화
        $this->jsonConfigService = new JsonConfigService;

        // 드로어 초기 상태: 닫힌
        $this->isOpen = false;

        // JSON 경로 설정
        $this->jsonPath = $jsonPath ?: base_path('jiny/admin2/App/Http/Controllers/Admin/AdminTemplates/AdminTemplates.json');

        // 설정 로드
        $this->loadSettings();
    }

    /**
     * 특정 경로와 함께 드로어 열기
     *
     * ## 사용 시나리오 (Use Cases):
     * 1. 다른 엔티티의 상세보기 설정 로드
     * 2. 역할별 표시 설정 적용
     * 3. 테마별 표시 형식 전환
     *
     * ## 파라미터 검증 (Parameter Validation):
     * - 경로 존재 여부 확인
     * - JSON 파일 유효성 검증
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

        // 설정 다시 로드
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
     * 3. show 섹션 추출
     * 4. display 설정 로드
     * 5. settingsDrawer 옵션 로드
     *
     * ## 오류 처리 (Error Handling):
     * - 파일 없음 → 기본값 사용
     * - JSON 파싱 오류 → 기본값 사용
     * - 키 누락 → 개별 기본값 사용
     *
     * ## 데이터 구조 (Data Structure):
     * ```php
     * $this->settings['show'] = [
     *     'display' => [
     *         'dateFormat' => string,
     *         'booleanLabels' => [
     *             'true' => string,
     *             'false' => string
     *         ]
     *     ],
     *     'settingsDrawer' => [
     *         'enableFieldToggle' => bool,
     *         'enableDateFormat' => bool,
     *         'enableSectionToggle' => bool
     *     ]
     * ];
     * ```
     *
     * @return void
     *
     * @throws 없음 (모든 예외는 내부 처리)
     */
    public function loadSettings()
    {
        try {
            // JsonConfigService를 사용하여 설정 로드
            $this->settings = $this->jsonConfigService->loadFromPath($this->jsonPath);

            if ($this->settings !== null) {

                // show 섹션 추출
                $showSettings = $this->settings['show'] ?? [];

                // 표시 설정 로드
                $display = $showSettings['display'] ?? [];
                $this->dateFormat = $display['dateFormat'] ?? 'Y-m-d H:i:s';

                // 불린 라벨 설정 로드
                $booleanLabels = $display['booleanLabels'] ?? [];
                $this->booleanTrueLabel = $booleanLabels['true'] ?? 'Enabled';
                $this->booleanFalseLabel = $booleanLabels['false'] ?? 'Disabled';

                // 설정 드로어 옵션 로드
                $settingsDrawer = $showSettings['settingsDrawer'] ?? [];
                $this->enableFieldToggle = $settingsDrawer['enableFieldToggle'] ?? true;
                $this->enableDateFormat = $settingsDrawer['enableDateFormat'] ?? true;
                $this->enableSectionToggle = $settingsDrawer['enableSectionToggle'] ?? true;
                
                // 액션 버튼 표시 설정 로드
                $this->enableEdit = $showSettings['enableEdit'] ?? true;
                $this->enableDelete = $showSettings['enableDelete'] ?? true;
                $this->enableListButton = $showSettings['enableListButton'] ?? true;
            } else {
                // 파일이 없으면 기본값 설정
                $this->setDefaults();
            }
        } catch (\Exception $e) {
            // 오류 발생 시 기본값 사용
            // TODO: 필요시 로깅 추가
            // \Log::error('Show settings load error: ' . $e->getMessage());
            $this->setDefaults();
        }
    }

    /**
     * 기본값 설정
     *
     * ## 호출 시점 (When Called):
     * 1. JSON 파일이 없을 때
     * 2. JSON 파싱 오류 시
     * 3. resetToDefaults() 호출 시
     *
     * ## 기본값 선정 기준 (Default Value Criteria):
     * - 국제 표준 날짜 형식 (ISO 8601)
     * - 일반적인 불린 라벨
     * - 모든 기능 활성화
     *
     * @return void
     */
    private function setDefaults()
    {
        // 표시 형식 기본값
        $this->dateFormat = 'Y-m-d H:i:s';           // ISO 형식
        $this->booleanTrueLabel = 'Enabled';         // 긍정 라벨
        $this->booleanFalseLabel = 'Disabled';       // 부정 라벨

        // 기능 활성화 기본값
        $this->enableFieldToggle = true;             // 필드 토글 허용
        $this->enableDateFormat = true;              // 날짜 형식 선택 허용
        $this->enableSectionToggle = true;           // 섹션 토글 허용
    }

    /* ===========================
     * UI 제어 메서드 (UI Control Methods)
     * =========================== */

    /**
     * 설정 드로어 열기
     *
     * ## 동작 순서 (Action Sequence):
     * 1. 최신 설정 다시 로드
     * 2. 드로어 상태를 열림으로 변경
     * 3. UI 렌더링 트리거
     *
     * ## 애니메이션 (Animation):
     * - 우측에서 슬라이드 인
     * - 배경 딤 처리
     *
     * @return void
     */
    public function open()
    {
        // 최신 설정 로드
        $this->loadSettings();

        // 드로어 열기
        $this->isOpen = true;
    }

    /**
     * 설정 드로어 닫기
     *
     * ## 동작 (Behavior):
     * - 변경사항 저장 없이 닫기
     * - ESC 키 또는 외부 클릭으로 닫기
     *
     * @return void
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
     * 2. 중첩된 구조 유지하며 업데이트
     * 3. JSON 형식으로 인코딩
     * 4. 파일 시스템에 쓰기
     * 5. 성공 알림 표시
     * 6. 페이지 새로고침
     *
     * ## 데이터 무결성 (Data Integrity):
     * - show 섹션만 업데이트
     * - 다른 섹션 보존
     * - 중첩 구조 유지
     *
     * ## 원자성 고려 (Atomicity Considerations):
     * - 파일 쓰기는 원자적이지 않음
     * - 동시 쓰기 시 충돌 가능
     * - TODO: 파일 잠금 구현
     *
     * @return void
     *
     * @emits settingsUpdated 설정 업데이트 완료
     * @emits notify 사용자 알림
     * @emits refresh-page 페이지 새로고침
     */
    public function save()
    {
        // JsonConfigService가 초기화되지 않은 경우 초기화
        if (!$this->jsonConfigService) {
            $this->jsonConfigService = new JsonConfigService;
        }
        
        // ===== 1. 표시 설정 업데이트 =====
        // display 섹션 구조 보장
        if (! isset($this->settings['show']['display'])) {
            $this->settings['show']['display'] = [];
        }

        // 날짜 형식 저장
        $this->settings['show']['display']['dateFormat'] = $this->dateFormat;

        // 불린 라벨 저장 (중첩 구조)
        if (! isset($this->settings['show']['display']['booleanLabels'])) {
            $this->settings['show']['display']['booleanLabels'] = [];
        }
        $this->settings['show']['display']['booleanLabels']['true'] = $this->booleanTrueLabel;
        $this->settings['show']['display']['booleanLabels']['false'] = $this->booleanFalseLabel;

        // ===== 2. 설정 드로어 옵션 업데이트 =====
        // settingsDrawer 섹션 구조 보장
        if (! isset($this->settings['show']['settingsDrawer'])) {
            $this->settings['show']['settingsDrawer'] = [];
        }

        $this->settings['show']['settingsDrawer']['enableFieldToggle'] = $this->enableFieldToggle;
        $this->settings['show']['settingsDrawer']['enableDateFormat'] = $this->enableDateFormat;
        $this->settings['show']['settingsDrawer']['enableSectionToggle'] = $this->enableSectionToggle;
        
        // ===== 3. 액션 버튼 표시 설정 저장 =====
        $this->settings['show']['enableEdit'] = $this->enableEdit;
        $this->settings['show']['enableDelete'] = $this->enableDelete;
        $this->settings['show']['enableListButton'] = $this->enableListButton;

        // ===== 4. JSON 파일에 저장 =====
        // JSON_PRETTY_PRINT: 가독성을 위한 들여쓰기
        // JSON_UNESCAPED_UNICODE: 한글/유니코드 보존
        $this->jsonConfigService->save($this->jsonPath, $this->settings);

        // ===== 4. 이벤트 발생 =====

        // 다른 컴포넌트에 업데이트 알림
        $this->dispatch('settingsUpdated');

        // 사용자에게 성공 메시지
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Display settings updated successfully!',
        ]);

        // ===== 5. UI 업데이트 =====

        // 드로어 닫기
        $this->close();

        // 페이지 새로고침으로 변경사항 즉시 반영
        $this->dispatch('refresh-page');
    }

    /**
     * 설정을 기본값으로 초기화
     *
     * ## 용도 (Purpose):
     * - 잘못된 설정 복구
     * - 표준 형식으로 리셋
     * - 테스트 환경 초기화
     *
     * ## 영향 범위 (Scope):
     * - show 섹션만 초기화
     * - 다른 섹션 유지
     *
     * ## 주의 (Caution):
     * - save() 호출 전까지 임시
     * - 사용자 확인 권장
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
     * - 네임스페이스: jiny-admin::template.settings.show-settings-drawer
     * - 실제 경로: /jiny/admin2/resources/views/template/settings/show-settings-drawer.blade.php
     *
     * ## 뷰에 전달되는 데이터 (Data Passed to View):
     * - $isOpen: 드로어 상태
     * - $dateFormat: 날짜 형식
     * - $booleanTrueLabel: true 라벨
     * - $booleanFalseLabel: false 라벨
     * - 기타 모든 public 속성
     *
     * ## 렌더링 시점 (Rendering Timing):
     * - 초기 마운트
     * - 속성 변경
     * - 이벤트 처리 후
     *
     * @return \Illuminate\View\View Blade 뷰 인스턴스
     */
    public function render()
    {
        return view('jiny-admin::template.settings.show-settings-drawer');
    }
}

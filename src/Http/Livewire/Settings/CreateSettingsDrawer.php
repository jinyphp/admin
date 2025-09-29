<?php

namespace Jiny\Admin\Http\Livewire\Settings;

use Illuminate\Support\Facades\File;
use Jiny\admin\App\Services\JsonConfigService;
use Livewire\Component;

/**
 * 생성 폼 설정 드로어 컴포넌트 (Create Form Settings Drawer Component)
 *
 * ## 목적 (Purpose)
 * 관리자 패널의 데이터 생성 폼 설정을 실시간으로 관리하는 Livewire 컴포넌트입니다.
 * 새로운 데이터를 추가하는 폼의 동작과 UI 옵션을 커스터마이징할 수 있습니다.
 *
 * ## 주요 기능 (Main Features)
 * 1. 폼 레이아웃 설정 - vertical/horizontal 레이아웃 선택
 * 2. 계속 생성 옵션 - 저장 후 새 항목 계속 생성 기능
 * 3. 네비게이션 버튼 설정 - 목록으로 돌아가기 버튼 표시/숨김
 * 4. 기본값 관리 - 폼 필드의 기본값 설정
 * 5. 필드 토글 - 선택적 필드 표시/숨김
 * 6. 유효성 검사 규칙 - 실시간 검증 규칙 설정
 *
 * ## 데이터 흐름 (Data Flow)
 * 1. 컨트롤러 → 컴포넌트: jsonPath 전달
 * 2. 컴포넌트 → JSON 파일: create 섹션 설정 읽기
 * 3. 사용자 → UI: 폼 설정 변경
 * 4. 컴포넌트 → JSON 파일: 변경사항 저장
 * 5. 컴포넌트 → 페이지: 새로고침 이벤트 발생
 *
 * ## JSON 구조 (JSON Structure)
 * ```json
 * {
 *     "create": {
 *         "enableContinueCreate": true,
 *         "enableListButton": true,
 *         "formLayout": "vertical",
 *         "settingsDrawer": {
 *             "enableDefaultValues": true,
 *             "enableFieldToggle": true,
 *             "enableValidationRules": true
 *         }
 *     }
 * }
 * ```
 *
 * ## 사용 예시 (Usage Example)
 * ```blade
 *
 * @livewire('jiny-admin::settings.create-settings-drawer', [
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
class CreateSettingsDrawer extends Component
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
     * - create: 생성 폼 관련 설정
     * - store: 데이터 저장 관련 설정
     * - validation: 유효성 검사 규칙
     * - defaults: 기본값 설정
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
     * JSON 설정 서비스 인스턴스
     *
     * @var JsonConfigService
     */
    private $jsonConfigService;

    /* ===========================
     * 생성 폼 설정 속성 (Create Form Settings)
     * =========================== */

    /**
     * 계속 생성 기능 활성화
     *
     * ## 동작 (Behavior):
     * - true: 저장 후 "저장 및 계속 생성" 버튼 표시
     * - false: 단일 저장 버튼만 표시
     *
     * ## 사용 시나리오 (Use Cases):
     * - 대량 데이터 입력 작업
     * - 연속적인 항목 추가가 필요한 경우
     *
     * @var bool
     *
     * @default true
     */
    public $enableContinueCreate = true;

    /**
     * 목록 버튼 표시 여부
     *
     * ## UI 위치 (UI Position):
     * - 폼 상단 헤더 영역
     * - 일반적으로 왼쪽 상단에 배치
     *
     * ## 동작 (Behavior):
     * - 클릭 시 목록 페이지로 이동
     * - 미저장 데이터 경고 표시 가능
     *
     * @var bool
     *
     * @default true
     */
    public $enableListButton = true;

    /**
     * 설정 드로어 활성화
     *
     * ## 기능 (Features):
     * - 폼 설정을 실시간으로 변경
     * - 관리자 권한으로만 접근 가능
     *
     * @var bool
     *
     * @default true
     */
    public $enableSettingsDrawer = true;

    /**
     * 폼 레이아웃 방향
     *
     * ## 허용 값 (Allowed Values):
     * - 'vertical': 라벨 위, 입력 필드 아래 (모바일 친화적)
     * - 'horizontal': 라벨 왼쪽, 입력 필드 오른쪽 (데스크톱 최적화)
     * - 'inline': 한 줄에 라벨과 필드 배치 (공간 절약)
     *
     * @var string 'vertical'|'horizontal'|'inline'
     *
     * @default 'vertical'
     */
    public $formLayout = 'vertical';

    /**
     * 기본값 설정 기능 활성화
     *
     * ## 기능 설명 (Feature Description):
     * - 폼 필드의 초기값 설정
     * - 자주 사용되는 값 미리 입력
     * - 사용자 편의성 향상
     *
     * @var bool
     *
     * @default true
     */
    public $enableDefaultValues = true;

    /**
     * 필드 토글 기능 활성화
     *
     * ## 용도 (Purpose):
     * - 선택적 필드 표시/숨김
     * - 고급 옵션 접기/펼치기
     * - 폼 복잡도 관리
     *
     * @var bool
     *
     * @default true
     */
    public $enableFieldToggle = true;

    /**
     * 유효성 검사 규칙 활성화
     *
     * ## 검증 시점 (Validation Timing):
     * - 실시간: 입력 중 즉시 검증
     * - 포커스 아웃: 필드 벗어날 때 검증
     * - 제출 시: 폼 제출 전 전체 검증
     *
     * @var bool
     *
     * @default true
     */
    public $enableValidationRules = true;

    /* ===========================
     * Livewire 이벤트 설정 (Event Configuration)
     * =========================== */

    /**
     * Livewire 이벤트 리스너 매핑
     *
     * ## 이벤트 설명 (Event Descriptions):
     *
     * ### 'openCreateSettings' → 'openWithPath'
     * - 발생 시점: 생성 페이지에서 설정 버튼 클릭
     * - 동작: JSON 경로와 함께 드로어 열기
     * - 파라미터: jsonPath (문자열, 선택적)
     *
     * @var array 이벤트명 => 메서드명 매핑
     */
    protected $listeners = ['openCreateSettings' => 'openWithPath'];

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
     * - 컨트롤러에서 jsonPath를 전달하는 것을 권장
     * - 기본값은 폴백용으로만 사용
     *
     * @param  string|null  $jsonPath  JSON 설정 파일의 절대 경로
     * @return void
     */
    public function mount($jsonPath = null)
    {
        // 드로어는 초기에 닫힌 상태
        $this->isOpen = false;

        // JSON 경로 설정: 컨트롤러 전달값 우선, 없으면 기본값
        $this->jsonPath = $jsonPath ?: base_path('jiny/admin2/App/Http/Controllers/Admin/AdminTemplates/AdminTemplates.json');

        // JsonConfigService 인스턴스 생성
        $this->jsonConfigService = new JsonConfigService;

        // 설정 로드
        $this->loadSettings();
    }

    /**
     * 특정 경로와 함께 드로어 열기
     *
     * ## 사용 시나리오 (Use Cases):
     * 1. 다른 엔티티의 생성 폼 설정 로드
     * 2. 템플릿별 폼 설정 전환
     * 3. 사용자 커스텀 설정 적용
     *
     * ## 파라미터 처리 (Parameter Handling):
     * - null인 경우: 현재 경로 유지
     * - 값이 있는 경우: 새 경로로 변경 후 로드
     *
     * @param  string|null  $jsonPath  로드할 JSON 파일 경로
     * @return void
     */
    public function openWithPath($jsonPath = null)
    {
        // JsonConfigService가 초기화되지 않은 경우 초기화
        if (! $this->jsonConfigService) {
            $this->jsonConfigService = new JsonConfigService;
        }

        // 경로가 전달된 경우에만 변경
        if ($jsonPath) {
            $this->jsonPath = $jsonPath;
        }

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
     * 3. create 섹션 추출
     * 4. 각 설정값을 컴포넌트 속성에 매핑
     * 5. 누락된 값은 기본값 사용
     *
     * ## 오류 처리 (Error Handling):
     * - 파일 없음 → 기본값 사용
     * - JSON 파싱 오류 → 기본값 사용
     * - 키 누락 → 개별 기본값 사용
     *
     * ## 데이터 구조 (Data Structure):
     * ```php
     * $this->settings['create'] = [
     *     'enableContinueCreate' => bool,
     *     'enableListButton' => bool,
     *     'formLayout' => string,
     *     'settingsDrawer' => [
     *         'enableDefaultValues' => bool,
     *         'enableFieldToggle' => bool,
     *         'enableValidationRules' => bool
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
            // JsonConfigService를 사용하여 JSON 파일 로드
            $this->settings = $this->jsonConfigService->loadFromPath($this->jsonPath);

            if ($this->settings) {
                // create 섹션 추출
                $createSettings = $this->settings['create'] ?? [];

                // 기본 폼 설정 로드
                $this->enableContinueCreate = $createSettings['enableContinueCreate'] ?? true;
                $this->enableListButton = $createSettings['enableListButton'] ?? true;
                $this->enableSettingsDrawer = $createSettings['enableSettingsDrawer'] ?? true;
                $this->formLayout = $createSettings['formLayout'] ?? 'vertical';

                // 설정 드로어 옵션 로드
                $settingsDrawer = $createSettings['settingsDrawer'] ?? [];
                $this->enableDefaultValues = $settingsDrawer['enableDefaultValues'] ?? true;
                $this->enableFieldToggle = $settingsDrawer['enableFieldToggle'] ?? true;
                $this->enableValidationRules = $settingsDrawer['enableValidationRules'] ?? true;
            } else {
                // 파일이 없거나 로드 실패 시 기본값 설정
                $this->setDefaults();
            }
        } catch (\Exception $e) {
            // 오류 발생 시 기본값 사용
            // TODO: 필요시 로깅 추가
            // \Log::error('Create settings load error: ' . $e->getMessage());
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
     * - 가장 일반적인 폼 설정
     * - 사용자 친화적 옵션
     * - 모든 기능 활성화 (최대 유연성)
     *
     * @return void
     */
    private function setDefaults()
    {
        // 기본 폼 동작 설정
        $this->enableContinueCreate = true;    // 연속 생성 허용
        $this->enableListButton = true;        // 목록 버튼 표시
        $this->enableSettingsDrawer = true;    // 설정 기능 활성화
        $this->formLayout = 'vertical';        // 세로 레이아웃 (반응형)

        // 고급 기능 설정
        $this->enableDefaultValues = true;     // 기본값 지원
        $this->enableFieldToggle = true;       // 필드 토글 지원
        $this->enableValidationRules = true;   // 유효성 검사 활성화
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
     * ## 사용 시점 (When Used):
     * - 설정 버튼 클릭
     * - 키보드 단축키 (예: Ctrl+,)
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
     * - ESC 키로도 닫기 가능
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
     * ## 데이터 구조 보존 (Structure Preservation):
     * - create 섹션만 업데이트
     * - 다른 섹션은 영향 없음
     * - 중첩 구조 유지
     *
     * ## 원자성 고려 (Atomicity Considerations):
     * - 파일 쓰기는 원자적이지 않음
     * - 동시 쓰기 시 충돌 가능
     * - TODO: 파일 잠금 또는 트랜잭션 구현
     *
     * @return void
     *
     * @emits settingsUpdated 설정 업데이트 완료
     * @emits notify 사용자 알림
     * @emits refresh-page 페이지 새로고침
     */
    public function save()
    {
        // ===== 1. 기본 폼 설정 업데이트 =====
        $this->settings['create']['enableContinueCreate'] = $this->enableContinueCreate;
        $this->settings['create']['enableListButton'] = $this->enableListButton;
        $this->settings['create']['enableSettingsDrawer'] = $this->enableSettingsDrawer;
        $this->settings['create']['formLayout'] = $this->formLayout;

        // ===== 2. 설정 드로어 옵션 업데이트 =====
        // 중첩 구조 보장
        if (! isset($this->settings['create']['settingsDrawer'])) {
            $this->settings['create']['settingsDrawer'] = [];
        }

        $this->settings['create']['settingsDrawer']['enableDefaultValues'] = $this->enableDefaultValues;
        $this->settings['create']['settingsDrawer']['enableFieldToggle'] = $this->enableFieldToggle;
        $this->settings['create']['settingsDrawer']['enableValidationRules'] = $this->enableValidationRules;

        // ===== 3. JSON 파일에 저장 =====
        // JsonConfigService를 사용하여 저장
        $this->jsonConfigService->save($this->jsonPath, $this->settings);

        // ===== 4. 이벤트 발생 =====

        // 다른 컴포넌트에 알림
        $this->dispatch('settingsUpdated');

        // 사용자 알림
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Form settings updated successfully!',
        ]);

        // ===== 5. UI 업데이트 =====

        // 드로어 닫기
        $this->close();

        // 페이지 새로고침으로 변경사항 반영
        $this->dispatch('refresh-page');
    }

    /**
     * 설정을 기본값으로 초기화
     *
     * ## 용도 (Purpose):
     * - 잘못된 설정 복구
     * - 초기 상태로 리셋
     * - 테스트 환경 초기화
     *
     * ## 주의 (Caution):
     * - save() 호출 전까지는 임시
     * - 다른 섹션은 영향 없음
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
     * - 네임스페이스: jiny-admin::template.settings.create-settings-drawer
     * - 실제 경로: /jiny/admin2/resources/views/template/settings/create-settings-drawer.blade.php
     *
     * ## 뷰에 전달되는 데이터 (Data Passed to View):
     * - $isOpen: 드로어 상태
     * - $enableContinueCreate: 계속 생성 옵션
     * - $formLayout: 폼 레이아웃
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
        return view('jiny-admin::template.settings.create-settings-drawer');
    }
}

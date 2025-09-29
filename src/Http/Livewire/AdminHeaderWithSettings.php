<?php

namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;

class AdminHeaderWithSettings extends Component
{
    public $title = '';

    public $description = '';

    public $buttonText = '';  // Create 버튼 텍스트

    public $mode = 'index'; // index, show, create, edit

    public $jsonData = [];

    public $jsonPath = '';

    public $createRoute = '';

    public $listRoute = '';

    public $settingsPath = '';

    public $enableCreate = true; // Create 버튼 활성화 여부

    // 타이틀 설정 모달 관련 속성
    public $showTitleSettingsModal = false;

    public $editTitle = '';

    public $editDescription = '';

    public function mount($jsonData = [], $jsonPath = null, $mode = 'index')
    {
        $this->jsonData = $jsonData;
        $this->jsonPath = $jsonPath;
        $this->mode = $mode;
        $this->settingsPath = $jsonPath; // jsonPath를 settingsPath로도 사용

        // mode에 따라 적절한 데이터 추출
        if ($mode === 'index' && isset($jsonData['index'])) {
            // index 모드일 때 heading 정보 추출
            $this->title = $jsonData['index']['heading']['title'] ?? 'Admin Page';
            $this->description = $jsonData['index']['heading']['description'] ?? '';
        } elseif ($mode === 'create' && isset($jsonData['create'])) {
            // create 모드일 때 heading 정보 추출
            $this->title = $jsonData['create']['heading']['title'] ?? 'Create New';
            $this->description = $jsonData['create']['heading']['description'] ?? '';
        } elseif ($mode === 'edit' && isset($jsonData['edit'])) {
            // edit 모드일 때 heading 정보 추출
            $this->title = $jsonData['edit']['heading']['title'] ?? 'Edit';
            $this->description = $jsonData['edit']['heading']['description'] ?? '';
        } elseif ($mode === 'show' && isset($jsonData['show'])) {
            // show 모드일 때 heading 정보 추출
            $this->title = $jsonData['show']['heading']['title'] ?? 'Details';
            $this->description = $jsonData['show']['heading']['description'] ?? '';
        } else {
            // 기본값 설정
            $this->title = $jsonData['title'] ?? 'Admin Page';
            $this->description = $jsonData['subtitle'] ?? $jsonData['description'] ?? '';
        }

        // Create 버튼 설정
        $this->buttonText = $jsonData['create']['buttonText'] ?? $jsonData['create']['buttonTitle'] ?? '새 항목 추가';

        // Create 버튼 활성화 여부 확인
        // index.features.enableCreate 또는 create.enabled 확인
        if ($mode === 'index' && isset($jsonData['index']['features']['enableCreate'])) {
            $this->enableCreate = $jsonData['index']['features']['enableCreate'];
        } elseif (isset($jsonData['create']['enabled'])) {
            $this->enableCreate = $jsonData['create']['enabled'];
        } elseif (isset($jsonData['create']['enableCreate'])) {
            $this->enableCreate = $jsonData['create']['enableCreate'];
        }

        // 라우트 설정 (currentRoute가 있는 경우)
        if (isset($jsonData['currentRoute'])) {
            // create 라우트가 존재하는지 확인
            $routeName = $jsonData['currentRoute'].'.create';
            if (\Route::has($routeName)) {
                $this->createRoute = route($routeName);
            }
            // list 라우트 설정 - show 페이지인 경우 index 라우트로 변경
            if (str_ends_with($jsonData['currentRoute'], '.show')) {
                $indexRoute = str_replace('.show', '', $jsonData['currentRoute']);
                if (\Route::has($indexRoute)) {
                    $this->listRoute = route($indexRoute);
                }
            } else {
                $currentRoute = \Route::current();
                if ($currentRoute && $currentRoute->hasParameters()) {
                    // 파라미터가 있는 라우트인 경우, 파라미터 없는 버전으로 시도
                    $baseRouteName = preg_replace('/\.(show|edit|delete)$/', '', $jsonData['currentRoute']);
                    if (\Route::has($baseRouteName)) {
                        $this->listRoute = route($baseRouteName);
                    }
                } else {
                    $this->listRoute = route($jsonData['currentRoute']);
                }
            }
        } elseif (isset($jsonData['route']['name'])) {
            // route.name이 설정된 경우
            $routeName = $jsonData['route']['name'].'.create';
            if (\Route::has($routeName)) {
                $this->createRoute = route($routeName);
            }
            // list 라우트 설정
            $currentRoute = \Route::current();
            if ($currentRoute && $currentRoute->hasParameters()) {
                // 파라미터가 있는 라우트인 경우, 기본 라우트 사용
                if (\Route::has($jsonData['route']['name'])) {
                    $this->listRoute = route($jsonData['route']['name']);
                }
            } else {
                $this->listRoute = route($jsonData['route']['name']);
            }
        }
    }

    public function navigateToCreate()
    {
        if ($this->createRoute) {
            return redirect($this->createRoute);
        }
    }

    public function navigateToList()
    {
        if ($this->listRoute) {
            return redirect($this->listRoute);
        }
    }

    // 팝업 호출시
    public function openSettings()
    {
        if ($this->settingsPath) {
            // mode에 따라 다른 이벤트 발생
            if ($this->mode === 'show') {
                $this->dispatch('openShowSettings', $this->settingsPath);
            } elseif ($this->mode === 'create') {
                $this->dispatch('openCreateSettings', $this->settingsPath);
            } elseif ($this->mode === 'edit') {
                $this->dispatch('openEditSettings', $this->settingsPath);
            } else {
                $this->dispatch('openSettingsDrawer', $this->settingsPath);
            }
        } else {
            $this->dispatch('openCreateSettings');
        }
    }

    // 타이틀 설정 모달 열기
    public function openTitleSettings()
    {
        $this->editTitle = $this->title;
        $this->editDescription = $this->description;
        $this->showTitleSettingsModal = true;
    }

    // 타이틀 설정 모달 닫기
    public function closeTitleSettings()
    {
        $this->showTitleSettingsModal = false;
        $this->editTitle = '';
        $this->editDescription = '';
    }

    // 타이틀 설정 저장
    public function saveTitleSettings()
    {
        // 현재 값 업데이트
        $this->title = $this->editTitle;
        $this->description = $this->editDescription;

        // JSON 파일 업데이트
        if ($this->jsonPath && file_exists($this->jsonPath)) {
            try {
                // JSON 파일 읽기
                $jsonContent = file_get_contents($this->jsonPath);
                $jsonData = json_decode($jsonContent, true);

                // mode에 따라 적절한 위치에 저장
                if ($this->mode === 'index' && isset($jsonData['index'])) {
                    $jsonData['index']['heading']['title'] = $this->editTitle;
                    $jsonData['index']['heading']['description'] = $this->editDescription;
                } elseif ($this->mode === 'create' && isset($jsonData['create'])) {
                    $jsonData['create']['heading']['title'] = $this->editTitle;
                    $jsonData['create']['heading']['description'] = $this->editDescription;
                } elseif ($this->mode === 'edit' && isset($jsonData['edit'])) {
                    $jsonData['edit']['heading']['title'] = $this->editTitle;
                    $jsonData['edit']['heading']['description'] = $this->editDescription;
                } elseif ($this->mode === 'show' && isset($jsonData['show'])) {
                    $jsonData['show']['heading']['title'] = $this->editTitle;
                    $jsonData['show']['heading']['description'] = $this->editDescription;
                } else {
                    // 기본 위치에 저장
                    $jsonData['title'] = $this->editTitle;
                    $jsonData['subtitle'] = $this->editDescription;
                }

                // JSON 파일 쓰기 (포맷팅 적용)
                $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                file_put_contents($this->jsonPath, $jsonString);

                // 성공 메시지
                session()->flash('message', '타이틀 설정이 저장되었습니다.');
            } catch (\Exception $e) {
                // 에러 메시지
                session()->flash('error', 'JSON 파일 저장 중 오류가 발생했습니다: '.$e->getMessage());
            }
        }

        // 모달 닫기
        $this->closeTitleSettings();
    }

    public function render()
    {
        // mode에 따라 다른 view를 직접 반환
        switch ($this->mode) {
            case 'index':
                return view('jiny-admin::template.livewire.admin-header-index');

            case 'show':
                return view('jiny-admin::template.livewire.admin-header-show');

            case 'create':
                return view('jiny-admin::template.livewire.admin-header-create');

            case 'edit':
                return view('jiny-admin::template.livewire.admin-header-edit');

            default:
                // 기본값은 index 헤더 사용
                return view('jiny-admin::template.livewire.admin-header-index');
        }
    }
}

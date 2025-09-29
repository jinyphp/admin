<?php

namespace Jiny\Admin\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Jiny\Admin\Models\AdminUserSession;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * 관리자 테이블 Livewire 컴포넌트
 *
 * 데이터 테이블을 표시하고 검색, 정렬, 페이지네이션, 필터링 등의 기능을 제공합니다.
 * JSON 설정과 컨트롤러 Hook을 통해 동적으로 커스터마이징이 가능합니다.
 */
class AdminTable extends Component
{
    use WithPagination;

    /**
     * JSON 설정 데이터 및 컨트롤러 인스턴스
     */
    public $jsonData;

    protected $controller = null;
    public $controllerClass = null;  // Livewire가 상태를 유지하도록 public으로 변경

    /**
     * 페이지네이션 설정
     */
    public $perPage = 10;

    /**
     * 페이지 로딩 시간 추적
     */
    public $loadTime = 0;

    protected $startTime;

    /**
     * 정렬 설정 (URL 파라미터로 유지)
     */
    #[Url]
    public $sortField = 'created_at';

    #[Url]
    public $sortDirection = 'desc';

    /**
     * 검색 및 필터 설정
     */
    public $filters = [];      // 레거시 필터 (filter_컬럼명 형식)

    public $search = '';       // 검색어

    public $filter = [];       // 새 필터 형식

    /**
     * 체크박스 선택 관리
     */
    public $selectedAll = false;    // 전체 선택 상태

    public $selected = [];           // 선택된 항목 ID 배열

    public $selectedCount = 0;       // 선택된 항목 수

    /**
     * Livewire 이벤트 리스너 설정
     *
     * 다른 컴포넌트로부터 이벤트를 받아 처리합니다.
     */
    protected $listeners = [
        'search-updated' => 'updateSearch',
        'filter-updated' => 'updateFilter',
        'sort-updated' => 'updateSort',
        'perPage-updated' => 'updatePerPage',
        'search-reset' => 'resetSearch',
    ];

    // /**
    //  * 메서드 호출 처리
    //  *
    //  * 컨트롤러의 Hook 메서드를 우선 호출하고,
    //  * 없으면 컴포넌트의 내부 메서드를 호출합니다.
    //  *
    //  * @param string $method 호출할 메서드명
    //  * @param mixed ...$args 메서드 인자
    //  * @return mixed
    //  */
    // public function call($method, ...$args)
    // {
    //     // 컨트롤러가 있고 메서드가 존재하면 호출
    //     if($this->controller && method_exists($this->controller, $method)) {
    //         return $this->controller->$method($this, ...$args);
    //     }

    //     // 기본 메서드 체크
    //     if(method_exists($this, $method)) {
    //         return $this->$method(...$args);
    //     }

    //     return null;
    // }

    /**
     * 컴포넌트 부트 (매 요청마다 호출)
     *
     * Livewire가 매 요청마다 호출하는 메서드입니다.
     */
    public function boot()
    {
        // 매 요청마다 시작 시간 기록
        $this->startTime = microtime(true);
        
        // 컨트롤러 재초기화 (hydration 이후)
        if ($this->controllerClass && !$this->controller) {
            if (class_exists($this->controllerClass)) {
                $this->controller = new $this->controllerClass;
                \Log::debug('AdminTable: Controller re-initialized in boot', [
                    'class' => $this->controllerClass,
                ]);
            }
        }
    }

    /**
     * 컴포넌트 초기화
     *
     * Livewire 컴포넌트가 마운트될 때 실행됩니다.
     * JSON 설정을 로드하고 컨트롤러를 설정합니다.
     *
     * @param  array|null  $jsonData  JSON 설정 데이터
     */
    public function mount($jsonData = null)
    {
        $this->startTime = microtime(true);

        if ($jsonData) {
            $this->jsonData = $jsonData;

            // 페이지네이션 설정 초기화
            $this->initializePagination();

            // 정렬 설정
            if (isset($jsonData['index']['sorting'])) {
                $this->sortField = $jsonData['index']['sorting']['default'] ?? 'created_at';
                $this->sortDirection = $jsonData['index']['sorting']['direction'] ?? 'desc';
            }

            // 동적 쿼리 조건이 있으면 필터에 초기값 설정
            if (isset($jsonData['queryConditions']) && is_array($jsonData['queryConditions'])) {
                foreach ($jsonData['queryConditions'] as $field => $value) {
                    // filters 배열에 추가 (UI 반영용)
                    $this->filters[$field] = $value;
                }
            }

            // 컨트롤러 설정
            $this->setupController();

            // hookIndexing 호출 (데이터 조회 전 처리)
            if ($this->controller && method_exists($this->controller, 'hookIndexing')) {
                $this->controller->hookIndexing($this);
            }
        }
    }

    /**
     * 페이지네이션 설정 초기화
     * JSON 데이터에서 페이지네이션 설정을 읽어 초기화합니다.
     * 
     * @return void
     */
    private function initializePagination()
    {
        // index.pagination.perPage 확인
        if (isset($this->jsonData['index']['pagination']['perPage'])) {
            $this->perPage = $this->jsonData['index']['pagination']['perPage'];
        }
        // 기본값 10 유지
        else {
            $this->perPage = 10;
        }
    }

    /**
     * 컨트롤러 설정
     *
     * JSON 데이터에서 컨트롤러 클래스를 가져와 인스턴스를 생성합니다.
     * controllerClass가 없으면 URL 기반으로 폴백합니다.
     */
    protected function setupController()
    {
        // 이미 컨트롤러가 설정되어 있으면 스킵
        if ($this->controller) {
            return;
        }

        // 1. JSON 데이터에서 컨트롤러 클래스 확인 (우선순위 1)
        if (isset($this->jsonData['controllerClass']) && ! empty($this->jsonData['controllerClass'])) {
            $this->controllerClass = $this->jsonData['controllerClass'];

            // 컨트롤러 인스턴스 생성
            if (class_exists($this->controllerClass)) {
                $this->controller = new $this->controllerClass;
                \Log::info('AdminTable: Controller loaded from JSON data', [
                    'class' => $this->controllerClass,
                ]);

                return;
            } else {
                \Log::warning('AdminTable: Controller class not found', [
                    'class' => $this->controllerClass,
                ]);
            }
        }

        // 2. URL 기반 컨트롤러 결정 (폴백) - Deprecated: 모든 컨트롤러는 controllerClass를 전달해야 함
        // $currentUrl = request()->url();

        // 하드코딩된 URL 매핑은 제거됨 - 각 컨트롤러에서 controllerClass를 전달하도록 변경
        // if (strpos($currentUrl, '/admin/user/sessions') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminSessions\AdminSessions::class;
        // } elseif (strpos($currentUrl, '/admin/user/password/logs') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogs::class;
        // } elseif (strpos($currentUrl, '/admin/user/logs') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminUserLogs\AdminUserLogs::class;
        // } elseif (strpos($currentUrl, '/admin/users') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminUsers\AdminUsers::class;
        // } elseif (strpos($currentUrl, '/admin/user/type') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminUsertype\AdminUsertype::class;
        // } elseif (strpos($currentUrl, '/admin/hello') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminHello\AdminHello::class;
        // } elseif (strpos($currentUrl, '/admin/templates') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminTemplates\AdminTemplates::class;
        // } elseif (strpos($currentUrl, '/admin/test') !== false) {
        //     $this->controllerClass = \Jiny\Admin\App\Http\Controllers\Admin\AdminTest\AdminTest::class;
        // }

        // 컨트롤러가 JSON 데이터에서 전달되지 않은 경우 경고 로그
        if (! $this->controllerClass) {
            \Log::warning('AdminTable: No controller class provided in JSON data', [
                'jsonData' => $this->jsonData,
                'url' => request()->url(),
            ]);
        }
    }

    /**
     * 검색어 업데이트 처리
     *
     * @param  string  $search  검색어
     */
    #[On('search-updated')]
    public function updateSearch($search)
    {
        $this->search = $search;
        $this->resetPage();
    }

    #[On('filter-updated')]
    public function updateFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    #[On('sort-updated')]
    public function updateSort($sortBy)
    {
        $this->sortField = $sortBy;
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    #[On('perPage-updated')]
    public function updatePerPage($perPage)
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    #[On('search-reset')]
    public function resetSearch()
    {
        $this->search = '';
        $this->filter = [];
        $this->resetPage();
    }

    /**
     * 정렬 필드 변경
     *
     * 테이블 헤더 클릭 시 정렬 필드와 방향을 토글합니다.
     *
     * @param  string  $field  정렬할 필드명
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[On('search-filters')]
    public function handleSearchFilters($filters)
    {
        $this->filters = $filters;
        $this->resetPage();
    }

    #[On('search-reset')]
    public function handleSearchReset()
    {
        $this->filters = [];
        $this->resetPage();
    }

    /**
     * 전체 선택 체크박스 처리
     *
     * 현재 페이지의 모든 항목을 선택 또는 해제합니다.
     *
     * @param  bool  $value  체크박스 상태
     */
    public function updatedSelectedAll($value)
    {
        if ($value) {
            // 현재 페이지의 모든 ID 선택
            $this->selected = [];
            foreach ($this->rows as $row) {
                $this->selected[] = (string) $row->id;
            }
        } else {
            // 모든 선택 해제
            $this->selected = [];
        }

        $this->selectedCount = count($this->selected);
    }

    /**
     * 개별 항목 선택 처리
     *
     * 개별 항목 선택 시 전체 선택 상태를 업데이트합니다.
     */
    public function updatedSelected()
    {
        $currentPageIds = $this->rows->pluck('id')->map(function ($id) {
            return (string) $id;
        })->toArray();

        // 현재 페이지의 모든 항목이 선택되었는지 확인
        $currentPageSelectedCount = count(array_intersect($this->selected, $currentPageIds));

        if ($currentPageSelectedCount == count($currentPageIds) && count($currentPageIds) > 0) {
            $this->selectedAll = true;
        } else {
            $this->selectedAll = false;
        }

        $this->selectedCount = count($this->selected);
    }

    /**
     * 페이지 변경 시 처리
     *
     * 페이지가 변경되기 전에 선택 상태를 초기화합니다.
     */
    public function updatingPage()
    {
        $this->selectedAll = false;
        $this->selected = [];
        $this->selectedCount = 0;
    }

    /**
     * 페이지당 항목 수 변경 처리
     *
     * perPage 값 변경 시 선택 상태를 초기화하고 첫 페이지로 이동합니다.
     */
    public function updatedPerPage()
    {
        $this->selectedAll = false;
        $this->selected = [];
        $this->selectedCount = 0;
        $this->resetPage();
    }

    /**
     * 선택된 항목 삭제 요청
     *
     * 선택된 여러 항목을 삭제하기 위해 AdminDelete 컴포넌트에 이벤트를 전달합니다.
     */
    public function requestDeleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        // AdminDelete 컴포넌트에 이벤트 전달
        $this->dispatch('delete-multiple', ids: $this->selected);
    }

    /**
     * 개별 항목 삭제 요청
     *
     * 특정 항목을 삭제하기 위해 AdminDelete 컴포넌트에 이벤트를 전달합니다.
     *
     * @param  int  $id  삭제할 항목 ID
     */
    public function requestDeleteSingle($id)
    {
        // AdminDelete 컴포넌트에 이벤트 전달
        $this->dispatch('delete-single', id: $id);
    }

    /**
     * 삭제 완료 이벤트 처리
     *
     * 삭제 작업 완료 후 선택 상태를 초기화하고 페이지를 새로고침합니다.
     *
     * @param  string|null  $message  성공 메시지
     */
    #[On('delete-completed')]
    public function handleDeleteCompleted($message = null)
    {
        // 선택 초기화
        $this->selectedAll = false;
        $this->selected = [];
        $this->selectedCount = 0;

        // 성공 메시지 표시
        if ($message) {
            session()->flash('success', $message);
        }

        // 페이지 새로고침
        $this->resetPage();
    }


    /**
     * 세션 테이블 전용 데이터 조회
     *
     * AdminUserSession 모델을 사용하여 사용자 세션 데이터를 조회합니다.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getSessionRows()
    {
        $query = AdminUserSession::with('user');

        // 검색어 적용
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                    ->orWhere('ip_address', 'like', '%'.$this->search.'%');
            });
        }

        // 필터 적용
        if (! empty($this->filter)) {
            foreach ($this->filter as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $query->where($key, $value);
                }
            }
        }

        // 정렬 및 페이지네이션
        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * 테이블 데이터 조회 (Computed Property)
     *
     * JSON 설정에 지정된 테이블에서 데이터를 조회하고
     * 검색, 필터, 정렬, 페이지네이션을 적용합니다.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getRowsProperty()
    {
        // hookCustomRows가 있으면 우선 사용
        if ($this->controller && method_exists($this->controller, 'hookCustomRows')) {
            $result = $this->controller->hookCustomRows($this);
            if ($result !== false) {
                return $result;
            }
        }
        
        // 테이블 이름 가져오기
        $tableName = $this->jsonData['table']['name'] ?? 'admin_templates';

        // 특별 처리가 필요한 테이블 (Eloquent 모델 사용)
        if ($tableName === 'admin_user_sessions') {
            return $this->getSessionRows();
        }

        // 쿼리 생성
        $query = DB::table($tableName);

        // 동적 쿼리 조건 적용 (컨트롤러에서 전달된 queryConditions)
        if (isset($this->jsonData['queryConditions']) && is_array($this->jsonData['queryConditions'])) {
            foreach ($this->jsonData['queryConditions'] as $field => $value) {
                if ($value !== '' && $value !== null) {
                    // 특별한 조건 처리
                    if ($field === 'date_from') {
                        $query->where('created_at', '>=', $value);
                    } elseif ($field === 'date_to') {
                        $query->where('created_at', '<=', $value);
                    } else {
                        // 일반 조건
                        $query->where($field, $value);
                    }
                }
            }
        }

        // 검색어 적용
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        // 필터 조건 적용
        if (! empty($this->filter)) {
            foreach ($this->filter as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $query->where($key, $value);
                }
            }
        }

        // 기존 필터 조건 적용 (filter_컬럼명 형식) - 하위 호환성
        if (! empty($this->filters)) {
            foreach ($this->filters as $filterKey => $filterValue) {
                if (! empty($filterValue)) {
                    // filter_ 접두사 제거하여 실제 컬럼명 추출
                    $column = str_replace('filter_', '', $filterKey);
                    $query->where($column, 'like', '%'.$filterValue.'%');
                }
            }
        }

        // 정렬 및 페이지네이션
        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * 컴포넌트 렌더링
     *
     * 데이터를 조회하고 Hook 메서드를 호출한 후 뷰를 렌더링합니다.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $rows = $this->rows;

        // hookIndexed 호출 (데이터 조회 후 처리)
        if ($this->controller && method_exists($this->controller, 'hookIndexed')) {
            $rows = $this->controller->hookIndexed($this, $rows);
        }

        // 페이지 로딩 시간 계산 (startTime이 설정되어 있을 때만)
        if ($this->startTime) {
            $this->loadTime = microtime(true) - $this->startTime;
        } else {
            // startTime이 없으면 Laravel 시작 시간 사용 (fallback)
            if (defined('LARAVEL_START')) {
                $this->loadTime = microtime(true) - LARAVEL_START;
            } else {
                $this->loadTime = 0;
            }
        }

        $tablePath = $this->jsonData['index']['tableLayoutPath'] ?? 'jiny-admin::template.livewire.admin-table';

        return view($tablePath, [
            'rows' => $rows,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'selectedCount' => $this->selectedCount,
            'jsonData' => $this->jsonData,
            'perPage' => $this->perPage,
            'selected' => $this->selected,
            'selectedAll' => $this->selectedAll,
            'loadTime' => $this->loadTime,
        ]);
    }

    /**
     * 커스텀 Hook 처리 (wire:click="hookCustom"을 위한 메서드)
     *
     * @param  string  $actionName  액션명
     * @param  array  $params  파라미터
     */
    public function hookCustom($actionName, $params = [])
    {
        // 컨트롤러 재설정 (필요시)
        if (! $this->controller) {
            // controllerClass가 있으면 컨트롤러 재초기화
            if ($this->controllerClass && class_exists($this->controllerClass)) {
                $this->controller = new $this->controllerClass;
                \Log::info('AdminTable: Controller re-initialized in hookCustom', [
                    'class' => $this->controllerClass,
                ]);
            } else {
                // 그래도 없으면 setupController 시도
                $this->setupController();
            }

            if (! $this->controller) {
                \Log::error('Controller not set in AdminTable::hookCustom', [
                    'controllerClass' => $this->controllerClass,
                    'jsonData' => $this->jsonData,
                ]);
                session()->flash('error', '컨트롤러가 설정되지 않았습니다.');
                return;
            }
        }

        // Hook 메소드명 생성
        $methodName = 'hookCustom'.ucfirst($actionName);

        \Log::info('AdminTable::hookCustom called', [
            'actionName' => $actionName,
            'methodName' => $methodName,
            'controller' => $this->controller ? get_class($this->controller) : 'null',
            'params' => $params,
        ]);

        if (! method_exists($this->controller, $methodName)) {
            \Log::error('Hook method not found', [
                'methodName' => $methodName,
                'availableMethods' => $this->controller ? get_class_methods($this->controller) : [],
            ]);
            session()->flash('error', "Hook 메소드 '{$methodName}'를 찾을 수 없습니다.");
            return;
        }

        try {
            $result = $this->controller->$methodName($this, $params);

            // 결과에 따른 처리
            if ($result === true) {
                $this->dispatch('$refresh');
            }
        } catch (\Exception $e) {
            \Log::error('Hook execution failed', [
                'methodName' => $methodName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Hook 실행 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * 세션 종료 처리
     */
    public function terminateSession($id)
    {
        // 컨트롤러 재설정
        if (! $this->controller) {
            $this->setupController();

            if (! $this->controller) {
                session()->flash('error', '컨트롤러가 설정되지 않았습니다.');

                return;
            }
        }

        // Hook 메소드 호출
        $methodName = 'hookCustomTerminate';

        if (! method_exists($this->controller, $methodName)) {
            session()->flash('error', "Hook 메소드 '{$methodName}'를 찾을 수 없습니다.");

            return;
        }

        try {
            $result = $this->controller->$methodName($this, ['id' => $id]);

            if ($result === true) {
                $this->dispatch('$refresh');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Hook 실행 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }


    /**
     * 테이블 새로고침 이벤트 리스너
     */
    #[On('refresh-table')]
    public function refreshTable()
    {
        $this->resetPage();
    }

    /**
     * 메시지 표시 이벤트 리스너
     */
    #[On('show-message')]
    public function showMessage($data)
    {
        $type = $data['type'] ?? 'info';
        $message = $data['message'] ?? '';

        if ($message) {
            session()->flash($type, $message);
        }
    }
}

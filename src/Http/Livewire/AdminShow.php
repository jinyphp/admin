<?php

namespace Jiny\Admin\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminShow extends Component
{
    // 레코드 ID
    public $itemId;

    // 데이터
    public $item;

    public $data = [];

    // 설정
    public $jsonData;

    protected $controller = null;

    public $controllerClass = null;

    // Livewire가 상태를 유지하도록 public으로 변경
    public $controllerClassName;

    // 표시 설정
    public $sections = [];

    public $display = [];

    public function mount($jsonData = null, $data = [], $id = null, $controllerClass = null)
    {
        $this->jsonData = $jsonData;
        $this->data = $data;
        $this->itemId = $id;

        // 컨트롤러 클래스 설정
        if ($controllerClass) {
            $this->controllerClass = $controllerClass;
            $this->controllerClassName = $controllerClass; // Livewire가 유지할 수 있도록 저장
            $this->setupController();
        } elseif (isset($this->jsonData['controllerClass'])) {
            $this->controllerClass = $this->jsonData['controllerClass'];
            $this->controllerClassName = $this->jsonData['controllerClass']; // Livewire가 유지할 수 있도록 저장
            $this->setupController();
        }

        // Apply display formatting if configured
        if (isset($this->jsonData['show']['display'])) {
            $this->display = $this->jsonData['show']['display'];
        }

        // Apply section configuration if available
        if (isset($this->jsonData['formSections'])) {
            $this->sections = $this->jsonData['formSections'];
        }

        // hookShowing 호출
        if ($this->controller && method_exists($this->controller, 'hookShowing')) {
            $result = $this->controller->hookShowing($this, $this->data);
            if (is_array($result)) {
                $this->data = $result;
            }
        }
    }

    /**
     * 컨트롤러 설정
     */
    protected function setupController()
    {
        // 컨트롤러 인스턴스 생성
        if ($this->controllerClass && class_exists($this->controllerClass)) {
            $this->controller = new $this->controllerClass;
            \Log::info('AdminShow: Controller loaded successfully', [
                'class' => $this->controllerClass,
            ]);
        } else {
            \Log::warning('AdminShow: Controller class not found', [
                'class' => $this->controllerClass,
            ]);
        }
    }

    /**
     * 삭제 요청
     */
    public function requestDelete()
    {
        if ($this->itemId) {
            $this->dispatch('delete-single', id: $this->itemId);
        }
    }

    /**
     * 삭제 완료 처리
     */
    #[On('delete-completed')]
    public function handleDeleteCompleted($message = null)
    {
        // 디버깅 로그
        \Log::info('AdminShow::handleDeleteCompleted called', [
            'route_name' => $this->jsonData['route']['name'] ?? 'not set',
            'route_prefix' => $this->jsonData['route']['prefix'] ?? 'not set',
        ]);

        // 목록 페이지로 리다이렉트 (메시지 포함)
        $redirectUrl = '/admin/user/sessions';  // 기본 세션 목록 경로

        // route 설정에서 리다이렉트 URL 생성
        if (isset($this->jsonData['route']['prefix'])) {
            $redirectUrl = '/'.$this->jsonData['route']['prefix'];
            \Log::info('Using route prefix', ['url' => $redirectUrl]);
        } elseif (isset($this->jsonData['route']['name'])) {
            try {
                // route 이름이 있으면 실제 URL 경로 생성
                $routeName = $this->jsonData['route']['name'];
                \Log::info('Trying route name', ['route' => $routeName]);

                // 먼저 기본 route 이름으로 시도
                if (Route::has($routeName)) {
                    $redirectUrl = route($routeName);
                    \Log::info('Route found', ['route' => $routeName, 'url' => $redirectUrl]);
                }
                // .index를 붙여서 시도
                elseif (Route::has($routeName.'.index')) {
                    $redirectUrl = route($routeName.'.index');
                    \Log::info('Route with .index found', ['route' => $routeName.'.index', 'url' => $redirectUrl]);
                }
                // 실패하면 기본 경로 사용
                else {
                    \Log::warning('Route not found: '.$routeName);
                }
            } catch (\Exception $e) {
                // route가 없으면 기본 경로 사용
                \Log::warning('Route error: '.($this->jsonData['route']['name'] ?? 'unknown'), [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        \Log::info('Final redirect URL', ['url' => $redirectUrl]);

        // Livewire 리다이렉트 방식 사용
        if ($message) {
            session()->flash('success', $message);
        }

        // Livewire의 redirect 메소드 사용
        $this->redirect($redirectUrl);
    }

    /**
     * 커스텀 Hook 처리 (wire:click="hookCustom"을 위한 메서드)
     *
     * @param  string  $hookName  Hook 이름
     * @param  array  $params  파라미터
     */
    public function hookCustom($hookName, $params = [])
    {
        \Log::info('hookCustom method called', ['hookName' => $hookName, 'params' => $params]);

        return $this->callCustomAction($hookName, $params);
    }

    /**
     * 커스텀 액션 처리
     * 컨트롤러의 hookCustom{Name} 메소드를 호출합니다.
     *
     * @param  string  $actionName  액션명
     * @param  array  $params  파라미터
     */
    public function callCustomAction($actionName, $params = [])
    {
        // 컨트롤러 확인 및 재초기화
        if (! $this->controller) {
            // controllerClassName이 있으면 컨트롤러 재초기화
            if ($this->controllerClassName) {
                $this->controllerClass = $this->controllerClassName;
                $this->setupController();
                \Log::info('Controller re-initialized in callCustomAction', [
                    'controllerClass' => $this->controllerClassName,
                ]);
            }

            // 그래도 없으면 에러
            if (! $this->controller) {
                \Log::error('Controller not set in AdminShow', [
                    'controllerClass' => $this->controllerClass,
                    'controllerClassName' => $this->controllerClassName,
                    'actionName' => $actionName,
                ]);
                session()->flash('error', '컨트롤러가 설정되지 않았습니다.');

                return;
            }
        }

        // Hook 메소드명 생성
        $methodName = 'hookCustom'.ucfirst($actionName);

        \Log::info('Calling hook method', [
            'methodName' => $methodName,
            'controller' => get_class($this->controller),
            'params' => $params,
        ]);

        // Hook 메소드 존재 확인
        if (! method_exists($this->controller, $methodName)) {
            \Log::error('Hook method not found', [
                'methodName' => $methodName,
                'availableMethods' => get_class_methods($this->controller),
            ]);
            session()->flash('error', "Hook 메소드 '{$methodName}'를 찾을 수 없습니다.");

            return;
        }

        // Hook 호출
        try {
            $result = $this->controller->$methodName($this, $params);

            // 결과 처리
            if (isset($result['redirect'])) {
                return redirect($result['redirect']);
            }

            // 데이터 새로고침
            $this->refreshData();

            \Log::info('Hook executed successfully', ['methodName' => $methodName]);

        } catch (\Exception $e) {
            \Log::error('Hook execution failed', [
                'methodName' => $methodName,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Hook 실행 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * 데이터 새로고침
     */
    public function refreshData()
    {
        if (! $this->itemId) {
            return;
        }

        // 테이블명 가져오기
        $tableName = $this->jsonData['table']['name'] ?? 'users';

        // 데이터 다시 조회
        $item = DB::table($tableName)->where('id', $this->itemId)->first();

        if ($item) {
            $this->data = (array) $item;

            // hookShowing 다시 호출
            if ($this->controller && method_exists($this->controller, 'hookShowing')) {
                $result = $this->controller->hookShowing($this, $this->data);
                if (is_array($result)) {
                    $this->data = $result;
                }
            }
        }
    }

    public function render()
    {
        $viewPath = $this->jsonData['show']['showLayoutPath'] ?? 'jiny-admin::template.livewire.admin-show';

        return view($viewPath);
    }
}

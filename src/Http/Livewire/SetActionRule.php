<?php
namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

use Livewire\Attributes\On;

/**
 * Actions Popup 설정
 */
class SetActionRule extends Component
{
    const PATH = "actions";
    public $uri;
    public $actions;
    public $viewFile;
    public $viewForms;
    public $actionPath;

    public $popupForms = false;
    public $mode;
    public $popupWindowWidth = "4xl";
    use \Jiny\Widgets\Http\Trait\DesignMode;

    public function mount()
    {
        if(!$this->viewFile) {
            $this->viewFile = "jiny-admin::actions_set.popup";
        }

        $this->viewForms = "jiny-admin::actions_set.forms";

        $current_url = $this->detectURI();
        $this->uri = $current_url;

        $this->loading($current_url);
    }

    private function loading($current_url)
    {
        $filename = $this->getFilename();

        if (file_exists($filename)) {
            $rules = json_decode(file_get_contents($filename), true);

            foreach ($rules as $key => $value) {
                $this->forms[$key] = $value;
            }
        }
    }

    private function detectURI()
    {
        // 라우터에서 uri 정보 확인
        // 컨트롤러가 동작하지 않는 경우
        $uri = Route::current()->uri;
        if($uri == "{fallbackPlaceholder}") {
            $this->viewForms = "jiny-admin::actions_set.json";
            $uri = Request::path();
        }


        if($uri == '/') {
            $uri = "index";
            $this->actions['route']['uri'] = $uri;
            return $uri;
        }

        // uri에서 {} 매개변수 제거
        $slug = explode('/', $uri);
        $_slug = [];
        foreach($slug as $key => $item) {
            if($item[0] == "{") {
                $param = substr($item, 1, strlen($item)-2);
                $param = trim($param,'?');
                $this->actions['nesteds'] []= $param;

                continue; //unset($slug[$key])
            }
            $_slug []= $item;
        }

        // resource 컨트롤러에서 ~/create 는 삭제.
        if(count($_slug)>0) {
            $last = count($_slug)-1;
            if($_slug[$last] == "create" ||  $_slug[$last] == "edit") {
                unset($_slug[$last]);
            }
        }

        $slugPath = implode("/",$_slug); // 다시 url 연결.

        // Actions 정보를 설정함
        $this->actions['route']['uri'] = $slugPath;

        return $slugPath;
    }


    /**
     * 팝업창 관리
     */
    protected $listeners = ['popupRuleOpen','popupRuleClose'];
    public $popupRule = false;
    public function popupRuleOpen()
    {
        $this->popupRule = true;
    }

    public function popupRuleClose()
    {
        $this->popupRule = false;
    }

    public function render()
    {
        return view($this->viewFile);
    }

    public $forms = [];
    public function save()
    {
        //유효성 검사
        if (isset($this->actions['validate'])) {
            $validator = Validator::make($this->forms, $this->actions['validate'])->validate();
        }

        // 수정일자 갱신
        $this->forms['updated_at'] = date("Y-m-d H:i:s");

        $filename = $this->getFilename();

        // json 포맷으로 데이터 변환
        $json = json_encode($this->forms,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $json);

        $this->popupRuleClose();

        // Livewire Table을 갱신을 호출합니다.
        $this->dispatch('refeshTable');
    }

    private function getFilename()
    {
        $path = resource_path("actions");

        $uri = rtrim($this->uri,'/');
        $uri = str_replace('/',DIRECTORY_SEPARATOR,$uri);
        if(!is_dir($path.DIRECTORY_SEPARATOR.$uri)) mkdir($path.DIRECTORY_SEPARATOR.$uri, 0777, true);

        $this->actionPath = $uri.".json";

        $filename = $path.DIRECTORY_SEPARATOR.$this->actionPath;
        //dd($filename);
        return $filename;
    }


    public $content;
    public $resourceFile;
    public $popupResourceEdit = false;
    public function resourceEdit($file)
    {
        $this->popupRuleClose();
        $this->popupResourceEdit = true;

    }

    public function returnRule()
    {
        $this->popupResourceEdit = false;
        $this->popupRuleOpen();
    }

    public function update()
    {
        $this->popupResourceEdit = false;
        $this->popupRuleOpen();
    }


    public $addKeyStatus = false;
    public $key_name;
    public function addNewCreate()
    {
        $this->addKeyStatus = true;
        $this->key_name = null;
    }

    public function addNewCancel()
    {
        $this->addKeyStatus = false;
        $this->key_name = null;
    }

    public function addNewSubmit()
    {
        $this->addKeyStatus = false;
        if($this->key_name) {
            $this->forms['site'][$this->key_name] = null;
        }

    }

    public function itemRemove($key)
    {
        unset($this->forms['site'][$key]);
    }


    public function addBlade()
    {
        if(isset($this->forms['blade'])) {
            $cnt = count($this->forms['blade']);
            $this->forms['blade'][$cnt] = null;
        } else {
            $this->forms['blade'][0] = "";
        }
    }

    public function removeBlade($i)
    {
        unset( $this->forms['blade'][$i] );
    }

    public function bladeUp($i)
    {
        if($i>0) {
            $temp = $this->forms['blade'][$i-1];
            $this->forms['blade'][$i-1] = $this->forms['blade'][$i];
            $this->forms['blade'][$i] = $temp;
        }
    }

    public function bladeDown($i)
    {
        if( $i < count($this->forms['blade'])-1 ) {
            $temp = $this->forms['blade'][$i];
            $this->forms['blade'][$i] = $this->forms['blade'][$i+1];
            $this->forms['blade'][$i+1] = $temp;
        }
    }

    public function close()
    {
        $this->popupForms = false;
        $this->design = null;
    }


    public $layouts = [];

    #[On('layout-mode')]
    public function layoutMode($mode=null)
    {
        if($this->design) {
            $this->design = false;
            $this->popupForms = false;
        } else {
            $this->design = "layout";
            $this->popupForms = true;

            $layouts = DB::table('site_layouts')->get();
            $this->layouts = [];
            foreach($layouts as $item) {
                $tag = $item->tag;
                $this->layouts[$tag] []= $item;
            }

        }
    }

    #[On('action-mode')]
    public function actionMode($mode=null)
    {
        if($this->design) {
            $this->design = false;
            $this->popupForms = false;
        } else {
            $this->design = "action";
            $this->popupForms = true;

            // $layouts = DB::table('site_layouts')->get();
            // $this->layouts = [];
            // foreach($layouts as $item) {
            //     $tag = $item->tag;
            //     $this->layouts[$tag] []= $item;
            // }

        }
    }

}

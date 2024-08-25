<?php
namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class ActionEdit extends Component
{
    public $actions = [];
    public $filename;

    public $rows = [];
    public $upload_path;
    public $forms = [];
    public $edit_id;
    public $ref;

    public $message;

    public $viewFile;
    public $viewForm;
    public $viewList;

    // json 파일을 수정할 수 있는 모드로 전환합니다.
    public $editable = false;

    public $popupWindowWidth = "4xl";
    public $popupForm = false;
    public $popupSubForm = false;
    public $popupDelete = false;
    public $confirm = false;

    public function mount()
    {
        if(!$this->viewFile) {
            $this->viewFile = "jiny-admin::actions.json";
        }

        if($this->filename) {
            $this->getLoad();
        }

        $this->viewListFile();
        $this->viewFormFile();

        $this->upload_path = "/actions";
    }

    private function getLoad()
    {
        $path = resource_path('actions');
        if(!is_dir($path)) mkdir($path,0777,true);

        $json = json_file_decode($path.DIRECTORY_SEPARATOR.$this->filename);
        $this->rows = $json;

    }

    protected function viewListFile()
    {
        if(!$this->viewList) {
            $this->viewList = 'jiny-admin::actions.json';
        }
    }

    protected function viewFormFile()
    {
        if(!$this->viewForm) {
            $this->viewForm = "jiny-admin::actions.form";
            // 팝업창에서 사용
            $this->actions['view']['form'] = $this->viewForm;
        }
    }


    public function render()
    {
        if(!$this->filename) {
            return <<<EOD
            <div class="card">
            <div class="card-header">
            Action 파일이 선택되지 않았습니다.
            </div>
            </div>
            EOD;
        }

        return view($this->viewFile);
    }

    public function modify()
    {
        $this->editable = true;
    }

    public function modifyCancel()
    {
        $this->editable = false;
    }



    public function create($ref=null)
    {
        if($ref) {
            $this->ref = trim($ref,'-');
            //dd($this->ref);
            $this->popupSubForm = $this->ref;
        } else {
            $this->ref = null;
            $this->popupForm = true;
        }

        $this->popupEdit = true;

        $this->forms = []; // 데이터초기화
    }

    public function cancel()
    {
        $this->forms = [];
        $this->edit_id = null;
        $this->popupForm = false;
        $this->popupSubForm = false;
        $this->popupEdit = false;
        $this->popupDelete = false;
        $this->setup = false;
    }

    public function store()
    {
        if($this->ref == null ) {
            $this->storeRoot();
        } else {
            $this->storeSub();
        }
    }

    private function storeRoot()
    {
        if(!empty($this->forms) && isset($this->forms['key'])) {
            $key = $this->forms['key'];
            $this->rows[$key] = "";

            $this->forms = [];
        }

        $this->popupForm = false;
        $this->popupEdit = false;
    }


    private function storeSub()
    {
        $key = $this->forms['key'];

        // 재귀 포인트 위치
        $ref = explode('-',$this->ref);
        $temp = &$this->rows;
        foreach( $ref  as $i) {
            if(!isset($temp[$i])) {
                $temp[$i] = [];
            }

            $temp = &$temp[$i];
        }

        // 서브배열
        if(is_array($temp)) {
            $temp[$key] = "";
        } else {
            $temp = []; // 강제변경

            $key = $this->forms['key'];
            $temp[$key] = "";
        }



        $this->cancel();
    }


    public function save()
    {
        $path = resource_path('actions');
        if(!is_dir($path)) mkdir($path,0777,true);

        json_file_encode($path.DIRECTORY_SEPARATOR.$this->filename, $this->rows);

        $this->editable = false;
    }

    public $popupEdit = false;
    public function edit($id)
    {
        $id = trim($id,'-');
        $this->actions['id'] = $id;
        $this->edit_id = $id;

        $ref = explode('-',$id);
        $temp = &$this->rows;
        //dump($ref);
        //dump($temp);
        $key = null;
        foreach( $ref  as $i) {
            if(isset($temp[$i])) {
                $temp = &$temp[$i];
                $key = $i;
            }
            // else if(isset($temp['items'][$i])) {
            //     $temp = &$temp['items'][$i];
            // }
        }
        //dump($key);
        //dd($temp);

        $this->forms['old'] = $key;
        $this->forms['key'] = $key;
        $this->popupEdit = true;

    }

    public function update()
    {
        $id = $this->edit_id;

        $ref = explode('-',$id);
        $temp = &$this->rows;
        foreach( $ref as $i) {
            if(isset($temp[$i])) {
                $ddd = &$temp;
                $temp = &$temp[$i];
            }
        }

        // 순서에 맞게 다시 작성
        $arr = [];
        foreach($ddd as $key => $item) {
            if($key == $this->forms['old']) {
                $k = $this->forms['key'];
                $arr[$k] = $ddd[$key];
            } else {
                $arr[$key] = $ddd[$key];
            }
        }
        //dd($arr);
        $ddd = $arr; // 변경

        /*
        if($this->forms['old'] != $this->forms['key']) {
            $old = $this->forms['old'];
            //dump($old);
            //dump($ddd);
            $value = $ddd[$old];
            //dd($value);

            $key = $this->forms['key'];
            $ddd[$key] = $value;
            //dump($ddd);
            //dd($temp);

            unset($ddd[$old]);
        }
            */



        $this->forms = [];
        $this->edit_id = null;
        $this->actions['id'] = null;
        $this->popupForm = false;
        $this->popupEdit = false;
        //$this->setup = false;
    }


    /**
     * 삭제 팝업창 활성화
     */
    public function delete($id=null)
    {
        //dd("dfkalsjdf");
        //dd($id);
        $this->popupDelete = true;

    }


    public function deleteCancel()
    {
        $this->popupDelete = false;
        $this->popupForm = false;
        $this->setup = false;
    }

    /**
     * 삭제 확인 컨펌을 하는 경우에,
     * 실제적인 삭제가 이루어짐
     */
    public function deleteConfirm()
    {
        $this->popupDelete = false;
        $this->popupForm = false;
        $this->popupEdit = false;
        $this->setup = false;

        $id = $this->edit_id;
        $this->edit_id = null;

        // 이미지삭제
        // $this->deleteUploadFiles($this->rows[$id]);


        // 데이터삭제
        $ref = explode('-',trim($id,'-'));
        //dump($ref);
        $temp = &$this->rows;
        $ddd = &$this->rows;
        foreach( $ref as $i) {
            if(isset($temp[$i])) {
                $ddd = &$temp;
                $temp = &$temp[$i];

            }
            // else if(isset($temp[$i])) {
            //     $ddd = &$temp;
            //     $temp = &$temp[$i];

            // }
        }

        //dump($ddd);
        //dump($i);
        //dd($temp);
        unset($ddd[$i]);

        if(is_array($ddd) && empty($ddd)) {
            $ddd = "";
        }


    }


}

<?php
namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class ActionFiles extends Component
{
    public $viewFile;
    public $files = [];

    public function mount()
    {
        if(!$this->viewFile) {
            $this->viewFile = "jiny-admin::actions.files";
        }

        $path = resource_path('actions');
        $this->files = $this->scandir($path);

    }

    private function scandir($path)
    {
        $files = [];
        foreach(scandir($path) as $item) {
            if($item == "." || $item == "..") continue;

            if(is_dir($path.DIRECTORY_SEPARATOR.$item)) {
                $files[$item] =$this->scandir($path.DIRECTORY_SEPARATOR.$item);
                continue;
            }

            $files []= $item;
        }

        return $files;
    }

    public function render()
    {
        //dd($this->files);
        return view($this->viewFile);
    }
}

<?php
namespace Jiny\Admin;

use Illuminate\Support\Facades\Request;

class Actions
{
    private static $Instance;

    public $uri;
    public $data=[];

    /**
     * 싱글턴 인스턴스를 생성합니다.
     */
    public static function instance()
    {
        if (!isset(self::$Instance)) {
            // 자기 자신의 인스턴스를 생성합니다.
            $obj = new self();

            $obj->uri = Request::path();
            $obj->load();
            // $path = $obj->path();
            // $data = json_file_decode($path);
            // $obj->data = $data;
            // foreach($data as $key => $item) {
            //     $obj->$key = $item;
            // }

            self::$Instance = $obj;

            return self::$Instance;
        } else {
            // 인스턴스가 중복
            return self::$Instance;
        }
    }

    private function path()
    {
        // actions 파일 경로 체크
        $path = resource_path('actions');
        $path .= DIRECTORY_SEPARATOR;
        if($this->uri == '/') {
            return $path."index.json";
        } else {
            $path .= str_replace('/', DIRECTORY_SEPARATOR, $this->uri);
            $path .= ".json";
            return $path;
        }
    }

    public function load()
    {
        $path = $this->path();
        //dump($path);
        $this->data = json_file_decode($path);

        return $this;
    }

    public function save()
    {
        $path = $this->path();
        json_file_encode($path, $this->data);

        return $this;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function get($key=null)
    {
        if($key) {
            $temp = explode('.',$key);
            $data = &$this->data;
            foreach($temp as $k) {
                if(isset($data[$k])) {
                    $data = &$data[$k];
                } else {
                    return false;
                }
            }

            return $data;
        }

        return $this->data;
    }
}

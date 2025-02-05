<?php
namespace Jiny\Admin;

use Illuminate\Support\Facades\Request;

class Prefix
{
    private static $Instance;
    public $data=[];

    /**
     * 싱글턴 인스턴스를 생성합니다.
     */
    public static function instance()
    {
        if (!isset(self::$Instance)) {
            // 자기 자신의 인스턴스를 생성합니다.
            $obj = new self();

            //$obj->load();
            $obj->data = config("jiny.prefix");

            self::$Instance = $obj;

            return self::$Instance;
        } else {
            // 인스턴스가 중복
            return self::$Instance;
        }
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

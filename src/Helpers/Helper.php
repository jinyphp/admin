<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 어드민 url 접속 경로를 확인합니다.
 */
if(!function_exists('prefix')) {
    function prefix($key)
    {
        $obj = \Jiny\Admin\Prefix::instance();
        $prefix = $obj->get($key);
        return trim($prefix,'/');
    }
}

if(!function_exists('Prefix')) {
    function Prefix($key)
    {
        $obj = \Jiny\Admin\Prefix::instance();
        $prefix = $obj->get($key);
        return trim($prefix,'/');
    }
}

function adminPath($path) {
    return "/".prefix('admin').$path;
}







/**
 * 사용자 관리자 여부 확인
 */
if(!function_exists('isAdmin')) {
    function isAdmin() {
        $user = Auth::user();
        if($user->isAdmin) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 슈퍼 관리자 여부 확인
 */
if(!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        $user = Auth::user();
        if($user->isAdmin && $user->utype == "super") {
            return true;
        } else {
            return false;
        }
    }
}


/**
 * Actions
 */
if(!function_exists('Action')) {
    function Action($path=null)
    {
        return \Jiny\Admin\Actions::instance($path);
    }
}

if(!function_exists('ActionWidgets')) {
    function ActionWidgets()
    {
        return Action()->widgets();
    }
}





// 어드민 패키지가 설치가 되어 있는지 확인을 위한 함수
if(!function_exists('isAdminPackage')) {
    function isAdminPackage() {
        return true;
    }
}


if(!function_exists('admin_prefix')) {
    function admin_prefix()
    {
        //$prefix = config('jiny.admin.prefix');
        $prefix = Prefix("admin");
        if($prefix) {
            return $prefix;
        }

        // 기본값
        return "admin";
    }
}


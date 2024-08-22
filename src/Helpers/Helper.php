<?php
use Illuminate\Support\Facades\DB;

// 어드민 패키지가 설치가 되어 있는지 확인을 위한 함수
if(!function_exists('isAdminPackage')) {
    function isAdminPackage() {
        return true;
    }
}


if(!function_exists('admin_prefix')) {
    function admin_prefix()
    {
        $prefix = config('jiny.admin.prefix');
        if($prefix) {
            return $prefix;
        }

        // 기본값
        return "admin";
    }
}


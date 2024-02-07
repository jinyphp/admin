<?php

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

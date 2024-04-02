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


// --- Dashboard
function table_count($tablename,$where=[]) {
    $db = DB::table($tablename);
    /*
    ->where('created_at',">", date("Y-m-d 00:00:00"))
    */
    return $db->count();
}


function table_count_today($tablename,$where=[]) {
    $db = DB::table($tablename);
    $db->where('created_at',">", date("Y-m-d 00:00:00"));
    return $db->count();
}


function table_top5($tablename,$where=[]) {
    $db = DB::table($tablename);
    return $db->limit(5)->get();
}

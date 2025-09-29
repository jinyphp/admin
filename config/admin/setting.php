<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Security Settings
    |--------------------------------------------------------------------------
    |
    | IP 기반 보안 설정 및 로그인 제한 관련 설정
    |
    */
    
    'security' => [
        // IP별 로그인 시도 제한
        'ip_max_attempts' => env('ADMIN_IP_MAX_ATTEMPTS', 5),
        'ip_decay_minutes' => env('ADMIN_IP_DECAY_MINUTES', 15),
        'ip_block_duration' => env('ADMIN_IP_BLOCK_DURATION', 60), // 분 단위
        
        // 계정별 로그인 시도 제한
        'account_max_attempts' => env('ADMIN_ACCOUNT_MAX_ATTEMPTS', 3),
        'account_decay_minutes' => env('ADMIN_ACCOUNT_DECAY_MINUTES', 15),
        'account_lock_duration' => env('ADMIN_ACCOUNT_LOCK_DURATION', 30), // 분 단위
        
        // GeoIP 설정
        'geoip_enabled' => env('ADMIN_GEOIP_ENABLED', false),
        'allowed_countries' => env('ADMIN_ALLOWED_COUNTRIES', ''), // 콤마로 구분된 국가 코드
        'allow_unknown_country' => env('ADMIN_ALLOW_UNKNOWN_COUNTRY', true),
        
        // 알림 설정
        'notify_on_block' => env('ADMIN_NOTIFY_ON_BLOCK', false),
        'notification_email' => env('ADMIN_NOTIFICATION_EMAIL', ''),
        
        // 로그 설정
        'log_all_attempts' => env('ADMIN_LOG_ALL_ATTEMPTS', true),
        'log_retention_days' => env('ADMIN_LOG_RETENTION_DAYS', 30),
        
        // 추가 보안 옵션
        'enable_ip_whitelist' => env('ADMIN_ENABLE_IP_WHITELIST', false),
        'require_ip_whitelist' => env('ADMIN_REQUIRE_IP_WHITELIST', false), // 화이트리스트에 있는 IP만 허용
        'session_ip_check' => env('ADMIN_SESSION_IP_CHECK', true), // 세션 중 IP 변경 감지
        
        // 2FA 설정
        '2fa_enabled' => env('ADMIN_2FA_ENABLED', false),
        '2fa_required' => env('ADMIN_2FA_REQUIRED', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Admin UI Settings
    |--------------------------------------------------------------------------
    */
    
    'ui' => [
        'per_page' => env('ADMIN_PER_PAGE', 10),
        'theme' => env('ADMIN_THEME', 'light'),
        'sidebar_collapsed' => false,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Admin Access Control
    |--------------------------------------------------------------------------
    */
    
    'access' => [
        'enabled' => env('ADMIN_ACCESS_CONTROL', true),
        'default_role' => 'viewer',
        'super_admin_email' => env('ADMIN_SUPER_ADMIN_EMAIL', ''),
    ],
];
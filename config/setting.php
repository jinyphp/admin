<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Settings
    |--------------------------------------------------------------------------
    |
    | This file contains various configuration options for the admin panel.
    |
    */

    'name' => 'Jiny Admin',
    'version' => '1.0.0',
    'app_name' => 'Jiny Admin', // SMS/Email 템플릿에 사용할 앱 이름
    'app_url' => 'http://localhost:8000', // 애플리케이션 URL

    /*
    |--------------------------------------------------------------------------
    | Password Rules Configuration
    |--------------------------------------------------------------------------
    |
    | Define password validation rules and requirements for the admin system.
    |
    */
    'password' => [
        // 최소 길이
        'min_length' => 8,

        // 최대 길이
        'max_length' => 128,

        // 대문자 포함 필수 여부
        'require_uppercase' => true,

        // 소문자 포함 필수 여부
        'require_lowercase' => true,

        // 숫자 포함 필수 여부
        'require_numbers' => true,

        // 특수문자 포함 필수 여부
        'require_special_chars' => true,

        // 허용된 특수문자 목록
        'allowed_special_chars' => '!@#$%^&*()_+-=[]{}|;:,.<>?',

        // 공백 허용 여부
        'allow_spaces' => false,

        // 이전 비밀번호 재사용 방지 (몇 개까지 기억할지)
        'password_history' => 5,

        // 비밀번호 갱신 주기 설정
        // 옵션: 30, 90, 120, 365, 0 (0은 만료 없음)
        'expiry_days_options' => [
            0 => '없음',
            30 => '30일',
            90 => '90일',
            120 => '120일',
            365 => '365일 (1년)',
        ],

        // 비밀번호 만료 기간 (일 단위, 0은 만료 없음)
        'expiry_days' => 90,

        // 비밀번호 만료 알림 기간 (만료 며칠 전부터 알림)
        'expiry_warning_days' => 7,

        // 로그인 실패 시 계정 잠금
        'lockout' => [
            // 최대 시도 횟수
            'max_attempts' => 5,

            // 잠금 시간 (분 단위)
            'lockout_duration' => 30,

            // DB에 기록 시작할 실패 횟수 (이 횟수부터 DB에 기록)
            'log_after_attempts' => 5,

            // 실패 카운트 유지 시간 (초 단위, 캐시 TTL)
            'attempt_cache_ttl' => 3600, // 1시간

            // 경고 메시지 표시 시작 횟수
            'warning_after_attempts' => 3,
        ],

        // 비밀번호 강도 체크
        'strength' => [
            // 최소 강도 레벨 (1: weak, 2: fair, 3: good, 4: strong, 5: very strong)
            'min_level' => 3,

            // 일반적인 비밀번호 체크
            'check_common_passwords' => true,

            // 사용자 정보와 유사성 체크 (이름, 이메일 등)
            'check_user_similarity' => true,

            // 연속된 문자/숫자 체크 (예: abc, 123)
            'check_sequential' => true,

            // 반복된 문자 체크 (예: aaa, 111)
            'check_repeated' => true,
            'max_repeated_chars' => 3,
        ],

        // 비밀번호 복잡도 규칙 메시지
        'messages' => [
            'min_length' => '비밀번호는 최소 :min자 이상이어야 합니다.',
            'max_length' => '비밀번호는 최대 :max자를 초과할 수 없습니다.',
            'require_uppercase' => '비밀번호는 최소 1개의 대문자를 포함해야 합니다.',
            'require_lowercase' => '비밀번호는 최소 1개의 소문자를 포함해야 합니다.',
            'require_numbers' => '비밀번호는 최소 1개의 숫자를 포함해야 합니다.',
            'require_special_chars' => '비밀번호는 최소 1개의 특수문자를 포함해야 합니다.',
            'no_spaces' => '비밀번호에 공백을 포함할 수 없습니다.',
            'password_used' => '이전에 사용한 비밀번호는 재사용할 수 없습니다.',
            'too_common' => '너무 일반적인 비밀번호입니다.',
            'too_similar' => '사용자 정보와 너무 유사한 비밀번호입니다.',
            'sequential_chars' => '연속된 문자나 숫자를 사용할 수 없습니다.',
            'repeated_chars' => '동일한 문자를 :max개 이상 연속으로 사용할 수 없습니다.',
            'weak_password' => '비밀번호 강도가 너무 약합니다.',
        ],

        // 비밀번호 생성 도구 설정
        'generator' => [
            // 자동 생성 비밀번호 길이
            'default_length' => 16,

            // 생성 시 포함할 문자 유형
            'include_uppercase' => true,
            'include_lowercase' => true,
            'include_numbers' => true,
            'include_special' => true,

            // 혼동하기 쉬운 문자 제외 (0, O, l, 1 등)
            'exclude_ambiguous' => true,
            'ambiguous_chars' => '0O1lI',
        ],

        // 2단계 인증 설정
        'two_factor' => [
            // 2단계 인증 사용 여부
            'enabled' => true,

            // 2단계 인증 강제 여부
            'required' => false,

            // 2단계 인증 방법 (totp, sms, email)
            'methods' => ['totp', 'sms', 'email'],
            
            // 기본 인증 방법
            'default_method' => 'totp',

            // 백업 코드 개수
            'backup_codes' => 8,
            
            // TOTP 설정
            'totp' => [
                'issuer' => 'Jiny Admin',
                'digits' => 6,
                'period' => 30,
                'algorithm' => 'sha1',
                'qr_code_size' => 200,
            ],
            
            // SMS/Email 코드 설정
            'code' => [
                'length' => 6,
                'expiry_minutes' => 5,
                'resend_cooldown' => 60, // 초 단위
                'max_attempts' => 5,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Settings
    |--------------------------------------------------------------------------
    |
    | CAPTCHA 관련 설정 (Google reCAPTCHA, hCaptcha 지원)
    |
    */
    'captcha' => [
        // CAPTCHA 기능 활성화 여부
        'enabled' => false,
        
        // CAPTCHA 드라이버 (recaptcha, hcaptcha, cloudflare)
        'driver' => 'recaptcha',
        
        // CAPTCHA 표시 모드 (always: 항상, conditional: 조건부, disabled: 비활성화)
        'mode' => 'conditional',
        
        // 조건부 모드에서 CAPTCHA를 표시할 실패 시도 횟수
        'show_after_attempts' => 3,
        
        // 캐시 TTL (초 단위)
        'cache_ttl' => 3600,
        
        // Google reCAPTCHA 설정
        'recaptcha' => [
            'site_key' => '',
            'secret_key' => '',
            'version' => 'v2',
            'threshold' => 0.5,
        ],
        
        // hCaptcha 설정
        'hcaptcha' => [
            'site_key' => '',
            'secret_key' => '',
        ],
        
        // Cloudflare Turnstile 설정
        'cloudflare' => [
            'site_key' => '',
            'secret_key' => '',
        ],
        
        // CAPTCHA 로그 설정
        'log' => [
            'enabled' => true,
            'failed_only' => false,
        ],
        
        // CAPTCHA 메시지
        'messages' => [
            'required' => 'CAPTCHA 인증이 필요합니다.',
            'failed' => 'CAPTCHA 인증에 실패했습니다. 다시 시도해주세요.',
            'expired' => 'CAPTCHA가 만료되었습니다. 페이지를 새로고침해주세요.',
            'not_configured' => 'CAPTCHA가 올바르게 설정되지 않았습니다.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist Settings
    |--------------------------------------------------------------------------
    |
    | IP 기반 접근 제어 설정
    |
    */
    'ip_whitelist' => [
        // IP 화이트리스트 기능 활성화 여부
        'enabled' => false,
        
        // IP 화이트리스트 모드 (strict: 차단, log_only: 로그만 기록)
        'mode' => 'strict',
        
        // 신뢰할 수 있는 프록시 서버 목록 (쉼표로 구분)
        'trusted_proxies' => '',
        
        // 기본 허용 IP 목록 (개발 환경용)
        'default_allowed' => [
            '127.0.0.1',    // IPv4 localhost
            '::1',          // IPv6 localhost
        ],
        
        // IP 접근 로그 보관 기간 (일)
        'log_retention_days' => 90,
        
        // IP 차단 임계값
        'rate_limit' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
            'block_duration' => 60, // 분 단위
        ],
        
        // 캐시 설정
        'cache' => [
            'ttl' => 300, // 5분
            'key' => 'admin_ip_whitelist',
        ],
        
        // 알림 설정
        'notifications' => [
            'enabled' => false,
            'email' => '',
            'slack_webhook' => '',
        ],
        
        // GeoIP 설정 (IP 지역 기반 제한)
        'geoip' => [
            'enabled' => false,
            'allowed_countries' => ['KR', 'US', 'JP'], // ISO 국가 코드
            'blocked_countries' => [], // 차단할 국가 목록
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Settings
    |--------------------------------------------------------------------------
    |
    | SMS 발송 관련 설정
    |
    */
    'sms' => [
        // SMS 기능 활성화 여부
        'enabled' => false,
        
        // SMS 드라이버 (twilio, vonage, aws_sns, aligo)
        'driver' => 'twilio',
        
        // Twilio 설정
        'twilio' => [
            'enabled' => false,
            'account_sid' => '',
            'auth_token' => '',
            'from' => '', // Twilio 전화번호
        ],
        
        // Vonage (Nexmo) 설정
        'vonage' => [
            'enabled' => false,
            'api_key' => '',
            'api_secret' => '',
            'from' => '',
        ],
        
        // AWS SNS 설정
        'aws_sns' => [
            'enabled' => false,
            'region' => 'ap-northeast-2',
            'access_key_id' => '',
            'secret_access_key' => '',
        ],
        
        // 알리고 설정 (한국 SMS)
        'aligo' => [
            'enabled' => false,
            'api_key' => '',
            'user_id' => '',
            'sender' => '',
        ],
        
        // SMS 발송 설정
        'settings' => [
            'max_retries' => 3,
            'retry_delay' => 60, // 초 단위
            'rate_limit' => [
                'per_minute' => 10,
                'per_hour' => 100,
                'per_day' => 1000,
            ],
        ],
        
        // SMS 템플릿
        'templates' => [
            '2fa_code' => '[{app_name}] 인증 코드: {code}',
            'password_reset' => '[{app_name}] 비밀번호 재설정 코드: {code}',
            'account_locked' => '[{app_name}] 계정이 잠금되었습니다. 잠금 해제: {unlock_url}',
            'login_alert' => '[{app_name}] 새로운 로그인이 감지되었습니다. IP: {ip_address}',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | 이메일 발송 관련 설정
    |
    */
    'email' => [
        // 이메일 알림 활성화 여부
        'notifications_enabled' => true,
        
        // 알림 이벤트별 활성화 설정
        'events' => [
            'login_failed' => true,
            'account_locked' => true,
            'password_changed' => true,
            'two_fa_enabled' => true,
            'two_fa_disabled' => true,
            'ip_blocked' => true,
        ],
        
        // 관리자 알림 받을 이메일 주소
        'admin_emails' => [],
        
        // 이메일 템플릿 설정
        'templates' => [
            'from_name' => 'Jiny Admin',
            'from_email' => 'admin@example.com',
            'reply_to' => 'support@example.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Unlock Settings
    |--------------------------------------------------------------------------
    |
    | 계정 잠금 해제 관련 설정
    |
    */
    'unlock' => [
        // 잠금 해제 링크 유효 시간 (분)
        'token_expiry' => 60,
        
        // 최대 시도 횟수
        'max_attempts' => 5,
        
        // 재발송 제한 시간 (분)
        'resend_cooldown' => 5,
        
        // 보안 질문 사용 여부
        'use_security_question' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Webhook 알림 관련 설정
    |
    */
    'webhook' => [
        // Webhook 기능 활성화 여부
        'enabled' => false,
        
        // 캐시 설정
        'cache' => [
            'key' => 'admin_webhook_channels',
            'ttl' => 3600, // 1시간
        ],
        
        // 기본 타임아웃 (초)
        'timeout' => 10,
        
        // 재시도 설정
        'retry' => [
            'max_attempts' => 3,
            'delay' => 60, // 초 단위
        ],
        
        // 로그 설정
        'log' => [
            'enabled' => true,
            'retention_days' => 30, // 로그 보관 기간
        ],
        
        // 지원 타입
        'supported_types' => [
            'slack' => 'Slack',
            'discord' => 'Discord',
            'teams' => 'Microsoft Teams',
            'custom' => 'Custom Webhook',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Other Admin Settings
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'default' => 10,
        'options' => [10, 25, 50, 100],
    ],

    'datetime' => [
        'format' => 'Y-m-d H:i:s',
        'timezone' => 'Asia/Seoul',
    ],

    'upload' => [
        'max_file_size' => 10485760, // 10MB in bytes
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
    ],
];

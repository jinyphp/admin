<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default CAPTCHA Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default CAPTCHA driver that will be used.
    | Supported: "recaptcha", "hcaptcha", "cloudflare"
    |
    */
    'default' => env('CAPTCHA_DRIVER', 'recaptcha'),
    
    /*
    |--------------------------------------------------------------------------
    | Fallback CAPTCHA Driver
    |--------------------------------------------------------------------------
    |
    | If the primary driver fails, the system will automatically try this driver.
    |
    */
    'fallback' => env('CAPTCHA_FALLBACK_DRIVER', 'hcaptcha'),
    
    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, CAPTCHA verification will always pass.
    | WARNING: Only use for testing, never in production!
    |
    */
    'test_mode' => env('CAPTCHA_TEST_MODE', false),
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for CAPTCHA attempts.
    |
    */
    'rate_limit' => [
        'max_attempts' => env('CAPTCHA_MAX_ATTEMPTS', 5),
        'decay_minutes' => env('CAPTCHA_DECAY_MINUTES', 60),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Drivers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each CAPTCHA driver.
    |
    */
    'drivers' => [
        'recaptcha' => [
            'site_key' => env('RECAPTCHA_SITE_KEY'),
            'secret_key' => env('RECAPTCHA_SECRET_KEY'),
            'version' => env('RECAPTCHA_VERSION', 'v2'), // v2, v3
            'threshold' => env('RECAPTCHA_THRESHOLD', 0.5), // For v3 only
        ],
        
        'hcaptcha' => [
            'site_key' => env('HCAPTCHA_SITE_KEY'),
            'secret_key' => env('HCAPTCHA_SECRET_KEY'),
        ],
        
        'cloudflare' => [
            'site_key' => env('CLOUDFLARE_TURNSTILE_SITE_KEY'),
            'secret_key' => env('CLOUDFLARE_TURNSTILE_SECRET_KEY'),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Auto-blocking Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic blocking of suspicious IPs.
    |
    */
    'auto_block' => [
        'enabled' => env('CAPTCHA_AUTO_BLOCK_ENABLED', true),
        'suspicious_threshold' => env('CAPTCHA_SUSPICIOUS_THRESHOLD', 50),
        'block_duration' => env('CAPTCHA_BLOCK_DURATION', 1440), // minutes
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Honeypot Configuration
    |--------------------------------------------------------------------------
    |
    | Configure honeypot field for additional bot protection.
    |
    */
    'honeypot' => [
        'enabled' => env('CAPTCHA_HONEYPOT_ENABLED', true),
        'field_name' => env('CAPTCHA_HONEYPOT_FIELD', 'website'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CAPTCHA logging.
    |
    */
    'logging' => [
        'enabled' => env('CAPTCHA_LOGGING_ENABLED', true),
        'cleanup_days' => env('CAPTCHA_CLEANUP_DAYS', 30),
    ],
];
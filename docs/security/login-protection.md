# 로그인 시도 제한 및 보호

## 개요

Jiny Admin은 다층 보안 전략을 통해 무차별 대입 공격(Brute Force)과 자동화된 공격으로부터 시스템을 보호합니다.

## 보안 계층 구조

```
┌─────────────────────────────────────────────────────┐
│                   사용자 요청                          │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│              IP 화이트리스트 체크                      │
│            (IpWhitelistMiddleware)                   │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│                 IP 차단 확인                          │
│            (IpTrackingService)                       │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│                CAPTCHA 검증                          │
│             (CaptchaMiddleware)                      │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│             로그인 시도 제한 확인                       │
│            (AdminPasswordLog)                        │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│                 인증 처리                             │
│               (AdminAuth)                            │
└─────────────────────────────────────────────────────┘
```

## 핵심 컴포넌트

### 1. IpTrackingService

**위치**: `App\Services\IpTrackingService.php`

**주요 기능**:

```php
class IpTrackingService {
    /**
     * 호출 관계 트리:
     * 
     * trackFailedAttempt()
     * ├── incrementFailedAttempts()  // 실패 횟수 증가
     * ├── checkThreshold()           // 임계값 확인
     * │   └── blockIp()              // IP 차단
     * │       ├── saveToDatabase()
     * │       ├── updateCache()
     * │       └── notifyAdmins()
     * └── logAttempt()               // 시도 기록
     */
    public function trackFailedAttempt(string $ip, string $email = null)
    
    /**
     * IP 차단 확인 프로세스:
     * 
     * isBlocked()
     * ├── checkPermanentBlacklist()  // 영구 차단 목록
     * ├── checkTemporaryBlock()      // 임시 차단
     * ├── checkGeoBlocking()         // 지역 차단
     * └── checkRateLimit()           // 속도 제한
     */
    public function isBlocked(string $ip): bool
}
```

### 2. CaptchaManager

**위치**: `App\Services\Captcha\CaptchaManager.php`

**CAPTCHA 트리거 조건**:

```php
/**
 * CAPTCHA 필요 조건 판단 트리:
 * 
 * isRequired()
 * ├── checkMode()                    // always|smart
 * ├── checkFailedAttempts()          // 실패 횟수
 * │   └── threshold: 3               // 3회 이상
 * ├── checkIpReputation()            // IP 평판
 * │   ├── knownBotNetworks()
 * │   ├── vpnDetection()
 * │   └── torExitNodes()
 * └── checkUserHistory()             // 사용자 기록
 *     └── previousBlocks()
 */
public function isRequired(?string $email, string $ip): bool
```

**지원 CAPTCHA 서비스**:

| 서비스 | 드라이버 | 설정 키 |
|--------|---------|---------|
| Google reCAPTCHA v2 | `RecaptchaDriver` | `services.recaptcha` |
| Google reCAPTCHA v3 | `RecaptchaV3Driver` | `services.recaptcha` |
| hCaptcha | `HcaptchaDriver` | `services.hcaptcha` |
| Cloudflare Turnstile | `TurnstileDriver` | `services.turnstile` |

### 3. AdminPasswordLog

**위치**: `App\Models\AdminPasswordLog.php`

**차단 로직**:

```php
/**
 * 계정 차단 결정 트리:
 * 
 * isBlocked()
 * ├── getRecentAttempts()           // 최근 시도 조회
 * │   └── timeWindow: 30분
 * ├── countFailures()               // 실패 횟수 계산
 * │   └── threshold: 5              // 5회 이상
 * ├── checkLockoutStatus()          // 잠금 상태 확인
 * │   ├── locked_until 확인
 * │   └── force_locked 확인
 * └── applyLockout()                // 잠금 적용
 *     ├── duration: 30분
 *     └── sendNotification()        // 알림 발송
 */
public static function isBlocked(string $email, string $ip = null): bool
```

## 계정 잠금 및 해제

### 잠금 프로세스

```php
/**
 * 계정 잠금 플로우:
 * 
 * 1. 로그인 실패 5회 도달
 * 2. 계정 잠금 (30분)
 * 3. 이메일/SMS 알림 발송
 * 4. 잠금 해제 토큰 생성
 * 5. 해제 링크 전송
 */

// 실제 구현
if ($failedAttempts >= 5) {
    // 계정 잠금
    $user->locked_until = now()->addMinutes(30);
    $user->save();
    
    // 토큰 생성
    $token = UnlockToken::createToken($user->id);
    
    // 알림 발송
    $notificationService->notifyAccountLockedAll(
        $user->id,
        '반복된 로그인 실패',
        url('/admin/unlock/' . $token),
        60  // 60분 유효
    );
}
```

### 해제 메커니즘

**UnlockToken 모델**:

```php
class UnlockToken {
    /**
     * 토큰 검증 및 해제 트리:
     * 
     * validateAndUnlock()
     * ├── findToken()                // 토큰 조회
     * ├── checkExpiry()              // 만료 확인
     * ├── verifyUser()               // 사용자 확인
     * ├── unlockAccount()            // 계정 해제
     * │   ├── clearLockout()
     * │   ├── resetFailedAttempts()
     * │   └── logUnlock()
     * └── invalidateToken()          // 토큰 무효화
     */
    public static function validateAndUnlock(string $token): bool
}
```

## IP 관리

### IP 화이트리스트

```php
// 화이트리스트 추가
IpWhitelist::add([
    'ip_address' => '192.168.1.0',
    'ip_range' => '192.168.1.255',
    'description' => '사내 네트워크',
    'expires_at' => null  // 영구
]);

// CIDR 표기법
IpWhitelist::addCidr('10.0.0.0/8', '내부 네트워크');
```

### IP 블랙리스트

```php
// 블랙리스트 추가
IpBlacklist::add([
    'ip_address' => '1.2.3.4',
    'reason' => '의심스러운 활동',
    'blocked_until' => now()->addDays(7)
]);

// 영구 차단
IpBlacklist::blockPermanently('5.6.7.8', '악성 봇');
```

### 지역 기반 차단

```php
// 특정 국가 차단
IpTracking::blockCountry(['CN', 'RU', 'KP']);

// 허용 국가만 설정
IpTracking::allowOnlyCountries(['KR', 'US', 'JP']);
```

## CAPTCHA 설정

### 설정 파일

```php
// config/admin/setting.php
return [
    'captcha' => [
        'enabled' => true,
        'mode' => 'smart',  // always|smart
        'threshold' => [
            'failed_attempts' => 3,
            'time_window' => 30,  // 분
        ],
        'services' => [
            'default' => 'recaptcha',  // recaptcha|hcaptcha|turnstile
        ]
    ]
];
```

### 서비스별 설정

**Google reCAPTCHA**:
```php
// config/services.php
'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'version' => 'v2',  // v2|v3
    'score_threshold' => 0.5,  // v3용
]
```

**hCaptcha**:
```php
'hcaptcha' => [
    'site_key' => env('HCAPTCHA_SITE_KEY'),
    'secret_key' => env('HCAPTCHA_SECRET_KEY'),
]
```

**Cloudflare Turnstile**:
```php
'turnstile' => [
    'site_key' => env('TURNSTILE_SITE_KEY'),
    'secret_key' => env('TURNSTILE_SECRET_KEY'),
]
```

## 로그인 플로우 상세

### AdminAuth 컨트롤러

```php
/**
 * 로그인 처리 전체 플로우:
 * 
 * login()
 * ├── Step 1: CAPTCHA 검증
 * │   └── CaptchaMiddleware::handle()
 * ├── Step 2: IP 차단 확인
 * │   ├── checkIpBlocking()
 * │   ├── isBlocked()
 * │   └── isAllowedCountry()
 * ├── Step 3: 계정 차단 확인
 * │   └── AdminPasswordLog::isBlocked()
 * ├── Step 4: 인증 시도
 * │   └── Auth::attempt()
 * ├── Step 5: 관리자 권한 검증
 * │   ├── verifyAdminAuthorization()
 * │   ├── checkIsAdmin()
 * │   └── checkUserType()
 * ├── Step 6: 2FA 확인 (필요시)
 * │   └── handle2FA()
 * ├── Step 7: 비밀번호 변경 확인
 * │   └── checkPasswordChangeRequired()
 * └── Step 8: 로그인 완료
 *     ├── completeLogin()
 *     ├── updateLastLogin()
 *     ├── trackSession()
 *     └── logSuccess()
 */
public function login(Request $request)
```

## 보안 통계 및 모니터링

### 대시보드 메트릭

```php
// 실시간 통계
$stats = [
    'failed_attempts_today' => AdminPasswordLog::today()->failed()->count(),
    'blocked_ips' => IpBlacklist::active()->count(),
    'locked_accounts' => User::locked()->count(),
    'captcha_failures' => CaptchaLog::today()->failed()->count(),
];
```

### 알림 설정

```php
// 의심스러운 활동 알림
NotificationRule::create([
    'event' => 'suspicious_activity',
    'conditions' => [
        'failed_attempts' => ['>', 10],
        'time_window' => 10,  // 10분 내
    ],
    'channels' => ['email', 'slack'],
    'recipients' => ['security@example.com']
]);
```

## 테스트 시나리오

### 1. 로그인 차단 테스트

```php
// 테스트 코드
public function testLoginBlockAfterMaxAttempts()
{
    $email = 'test@example.com';
    
    // 5회 실패 시도
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post('/admin/login', [
            'email' => $email,
            'password' => 'wrong_password'
        ]);
    }
    
    // 6번째 시도 - 차단됨
    $response = $this->post('/admin/login', [
        'email' => $email,
        'password' => 'correct_password'
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHas('error', '계정이 차단되었습니다');
}
```

### 2. IP 차단 테스트

```php
public function testIpBlocking()
{
    // IP 블랙리스트 추가
    IpBlacklist::add(['ip_address' => '192.168.1.100']);
    
    // 해당 IP에서 요청
    $response = $this->withServerVariables([
        'REMOTE_ADDR' => '192.168.1.100'
    ])->get('/admin/login');
    
    $response->assertStatus(403);
}
```

## 문제 해결

### 계정 잠금 해제

```bash
# Artisan 명령어로 해제
php artisan admin:unlock-user test@example.com

# 수동 해제 (DB)
UPDATE users SET 
    locked_until = NULL,
    failed_attempts = 0 
WHERE email = 'test@example.com';
```

### IP 차단 해제

```bash
# Artisan 명령어
php artisan admin:unblock-ip 192.168.1.100

# 수동 해제
DELETE FROM admin_ip_blacklist 
WHERE ip_address = '192.168.1.100';
```

### CAPTCHA 문제

1. 사이트 키/시크릿 키 확인
2. 도메인 등록 확인 (CAPTCHA 서비스)
3. 네트워크 연결 확인
4. 로그 확인 (`storage/logs/captcha.log`)

## 보안 권장사항

1. **최소 권한 원칙**: 필요한 IP만 화이트리스트에 추가
2. **정기적인 검토**: 차단 목록과 로그 주기적 검토
3. **강력한 비밀번호 정책**: 복잡도 요구사항 설정
4. **2FA 의무화**: 모든 관리자 계정에 2FA 적용
5. **로그 모니터링**: 실시간 알림 설정
6. **백업 접근 방법**: 비상시 접근 절차 수립

## 관련 파일

- `App\Http\Controllers\Web\Login\AdminAuth.php`
- `App\Services\IpTrackingService.php`
- `App\Services\Captcha\CaptchaManager.php`
- `App\Models\AdminPasswordLog.php`
- `App\Models\UnlockToken.php`
- `App\Http\Middleware\IpWhitelistMiddleware.php`
- `App\Http\Middleware\CaptchaMiddleware.php`
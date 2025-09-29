# 2단계 인증 (2FA) 시스템

## 개요

Jiny Admin의 2FA 시스템은 Google Authenticator(TOTP), SMS, 이메일 기반의 다중 인증 방법을 지원하여 계정 보안을 강화합니다.

## 시스템 아키텍처

```
┌─────────────────────────────────────────────────────┐
│              TwoFactorAuthService                    │
│              (2FA 통합 관리 서비스)                     │
└────────────┬────────────────────────────────────────┘
             │
    ┌────────┼────────┬────────────┬──────────────┐
    ▼        ▼        ▼            ▼              ▼
  TOTP      SMS     Email     BackupCodes    Recovery
  (GA)   (Twilio)  (SMTP)     (일회용)        (복구)
```

## 핵심 서비스

### TwoFactorAuthService

**위치**: `App\Services\TwoFactorAuthService.php`

**주요 메서드 및 호출 관계**:

```php
class TwoFactorAuthService {
    /**
     * 2FA 활성화 플로우:
     * 
     * enable2FA()
     * ├── generateSecret()           // 비밀키 생성
     * ├── generateQRCode()          // QR 코드 생성
     * ├── generateBackupCodes()      // 백업 코드 생성 (10개)
     * ├── saveToDatabase()          // DB 저장
     * │   ├── admin_user2fas 테이블
     * │   └── users.two_factor_secret 업데이트
     * └── sendNotification()        // 활성화 알림
     *     └── NotificationService::notify2FAChanged()
     */
    public function enable2FA(User $user, string $method = 'app'): array
    
    /**
     * 인증 검증 플로우:
     * 
     * verify()
     * ├── getUser2FAConfig()        // 설정 조회
     * ├── checkMethod()             // 방법 확인
     * │   ├── verifyTOTP()         // TOTP 검증
     * │   ├── verifySMS()          // SMS 검증
     * │   ├── verifyEmail()        // 이메일 검증
     * │   └── verifyBackupCode()   // 백업 코드 검증
     * ├── markCodeAsUsed()          // 사용 표시
     * ├── updateLastUsed()          // 마지막 사용 시간
     * └── logVerification()         // 로그 기록
     */
    public function verify(User $user, string $code): bool
}
```

## 2FA 방법별 상세

### 1. TOTP (Google Authenticator)

**설정 프로세스**:

```php
// 1. 비밀키 생성
$secret = $twoFactorService->generateSecret();

// 2. QR 코드 생성
$qrCode = $twoFactorService->generateQRCode($user, $secret);

// 3. 사용자 확인 (6자리 코드 입력)
if ($twoFactorService->verifyTOTP($secret, $userCode)) {
    // 4. 활성화
    $twoFactorService->enableTOTP($user, $secret);
}
```

**QR 코드 생성**:

```php
/**
 * QR 코드 생성 트리:
 * 
 * generateQRCode()
 * ├── createOtpUri()            // otpauth:// URI 생성
 * │   ├── type: totp
 * │   ├── label: 사용자 이메일
 * │   ├── secret: Base32 인코딩
 * │   └── issuer: 앱 이름
 * └── QrCode::generate()        // QR 이미지 생성
 *     └── format: SVG
 */
```

### 2. SMS 기반 2FA

**SMS 발송 플로우**:

```php
/**
 * SMS 2FA 플로우:
 * 
 * sendSMS2FACode()
 * ├── generateCode()            // 6자리 코드 생성
 * ├── saveToCache()            // 5분간 캐시 저장
 * │   └── key: 2fa_sms_{user_id}
 * ├── selectProvider()         // SMS 제공자 선택
 * │   ├── Twilio
 * │   ├── Vonage
 * │   ├── AWS SNS
 * │   └── Aligo
 * ├── formatMessage()          // 메시지 포맷
 * └── sendSMS()               // 실제 발송
 *     └── SmsService::send()
 */
```

**지원 SMS 제공자**:

| 제공자 | 드라이버 | 설정 |
|--------|---------|------|
| Twilio | `TwilioSmsProvider` | `TWILIO_SID`, `TWILIO_TOKEN` |
| Vonage | `VonageSmsProvider` | `VONAGE_KEY`, `VONAGE_SECRET` |
| AWS SNS | `AwsSnsProvider` | `AWS_KEY`, `AWS_SECRET` |
| Aligo | `AligoSmsProvider` | `ALIGO_KEY`, `ALIGO_USER_ID` |

### 3. 이메일 기반 2FA

```php
/**
 * 이메일 2FA 플로우:
 * 
 * sendEmail2FACode()
 * ├── generateCode()           // 6자리 코드 생성
 * ├── saveToDatabase()        // DB 저장 (5분 유효)
 * │   └── admin_2fa_codes 테이블
 * ├── renderTemplate()        // 이메일 템플릿
 * │   └── template: 2fa_code
 * └── sendEmail()            // 발송
 *     └── NotificationService::notify()
 */
```

### 4. 백업 코드

**백업 코드 생성**:

```php
/**
 * 백업 코드 시스템:
 * 
 * generateBackupCodes()
 * ├── generateCodes()         // 10개 생성
 * │   └── format: XXXX-XXXX (8자리)
 * ├── hashCodes()            // bcrypt 해싱
 * ├── saveToDatabase()       // DB 저장
 * └── returnPlainCodes()     // 평문 반환 (1회만)
 */

// 사용 예
$backupCodes = $twoFactorService->generateBackupCodes($user);
// ['ABCD-1234', 'EFGH-5678', ...]
```

**백업 코드 사용**:

```php
// 검증 시 백업 코드 확인
if ($twoFactorService->verifyBackupCode($user, $code)) {
    // 사용된 코드 무효화
    $twoFactorService->invalidateBackupCode($user, $code);
    
    // 남은 코드가 3개 이하면 경고
    if ($remainingCodes <= 3) {
        session()->flash('warning', '백업 코드가 3개 남았습니다.');
    }
}
```

## 2FA 설정 UI

### 활성화 화면

```php
// Controller
public function enable2FA(Request $request)
{
    $user = auth()->user();
    
    // 1. 비밀키 생성 (세션 저장)
    $secret = session()->get('2fa_secret') 
        ?? $twoFactorService->generateSecret();
    session()->put('2fa_secret', $secret);
    
    // 2. QR 코드 생성
    $qrCode = $twoFactorService->generateQRCode($user, $secret);
    
    // 3. 백업 코드 미리 생성
    $backupCodes = session()->get('2fa_backup_codes')
        ?? $twoFactorService->generateBackupCodes($user, false);
    session()->put('2fa_backup_codes', $backupCodes);
    
    return view('admin.2fa.enable', compact('qrCode', 'secret', 'backupCodes'));
}
```

### 복구 프로세스

```php
/**
 * 2FA 복구 플로우:
 * 
 * recover2FA()
 * ├── validateRecoveryMethod()   // 복구 방법 검증
 * │   ├── backup_code           // 백업 코드
 * │   ├── email_verification    // 이메일 인증
 * │   └── admin_reset          // 관리자 초기화
 * ├── verifyIdentity()          // 신원 확인
 * │   ├── security_questions
 * │   └── id_verification
 * ├── disable2FA()              // 2FA 비활성화
 * └── notifyUser()             // 알림 발송
 */
```

## 데이터베이스 구조

### admin_user2fas 테이블

```sql
CREATE TABLE admin_user2fas (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    method ENUM('app', 'sms', 'email'),
    secret VARCHAR(255),           -- 암호화된 TOTP secret
    backup_codes TEXT,              -- JSON 배열 (해시)
    recovery_codes TEXT,            -- 복구 코드
    phone_number VARCHAR(20),       -- SMS용
    email VARCHAR(255),             -- 이메일용
    is_enabled BOOLEAN DEFAULT true,
    last_used_at TIMESTAMP,
    created_at TIMESTAMP
);
```

### admin_2fa_codes 테이블

```sql
CREATE TABLE admin_2fa_codes (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    code VARCHAR(10),
    type ENUM('sms', 'email'),
    expires_at TIMESTAMP,           -- 5분 후 만료
    used_at TIMESTAMP,
    created_at TIMESTAMP
);
```

## 보안 고려사항

### 1. 비밀키 보호

```php
// 암호화 저장
$encryptedSecret = Crypt::encryptString($secret);

// 복호화
$secret = Crypt::decryptString($encryptedSecret);
```

### 2. Rate Limiting

```php
// 2FA 시도 제한
RateLimiter::for('2fa', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()->id);
});
```

### 3. 시간 동기화

```php
// TOTP 시간 윈도우 설정
$window = 1; // ±30초 허용
$valid = $google2fa->verifyKey($secret, $code, $window);
```

## 통계 및 모니터링

### 2FA 사용 통계

```php
$stats = [
    'total_enabled' => AdminUser2fa::where('is_enabled', true)->count(),
    'by_method' => AdminUser2fa::groupBy('method')
        ->selectRaw('method, count(*) as count')
        ->pluck('count', 'method'),
    'recent_verifications' => Admin2FALog::today()->count(),
    'failed_attempts' => Admin2FALog::today()->failed()->count(),
];
```

### 보안 이벤트 추적

```php
// 2FA 관련 이벤트 로깅
Admin2FALog::create([
    'user_id' => $user->id,
    'event' => 'verification_failed',
    'method' => 'app',
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'metadata' => [
        'reason' => 'invalid_code',
        'code_entered' => substr($code, 0, 2) . '****'
    ]
]);
```

## 테스트 가이드

### 1. TOTP 테스트

```php
public function testTOTPGeneration()
{
    $service = new TwoFactorAuthService();
    $secret = $service->generateSecret();
    
    // Google Authenticator 시뮬레이션
    $google2fa = new Google2FA();
    $code = $google2fa->getCurrentOtp($secret);
    
    $this->assertTrue($service->verifyTOTP($secret, $code));
}
```

### 2. SMS 테스트

```php
public function testSMS2FA()
{
    // Mock SMS 서비스
    $this->mock(SmsService::class, function ($mock) {
        $mock->shouldReceive('send')
            ->once()
            ->andReturn(true);
    });
    
    $service = new TwoFactorAuthService();
    $result = $service->sendSMS2FACode($user);
    
    $this->assertTrue($result);
}
```

## 문제 해결

### QR 코드가 작동하지 않음

1. 시간 동기화 확인 (서버와 모바일)
2. 비밀키 인코딩 확인 (Base32)
3. Issuer 이름에 특수문자 제거
4. Google Authenticator 앱 업데이트

### SMS가 도착하지 않음

1. 전화번호 형식 확인 (+82...)
2. SMS 제공자 잔액 확인
3. 제공자 API 상태 확인
4. 로그 확인 (`admin_sms_send` 테이블)

### 백업 코드 분실

```bash
# 새 백업 코드 생성
php artisan admin:regenerate-backup-codes user@example.com

# 2FA 완전 초기화 (관리자 권한)
php artisan admin:reset-2fa user@example.com --force
```

## 모범 사례

1. **필수 적용**: 모든 관리자에게 2FA 의무화
2. **다중 방법**: 주 방법과 백업 방법 설정
3. **정기 검토**: 비활성 2FA 설정 정리
4. **백업 코드**: 안전한 장소에 보관 권고
5. **복구 절차**: 명확한 복구 프로세스 문서화
6. **교육**: 사용자 교육 및 가이드 제공

## 관련 파일

- `App\Services\TwoFactorAuthService.php`
- `App\Http\Controllers\Admin\Admin2FAController.php`
- `App\Models\AdminUser2fa.php`
- `App\Models\Admin2FACode.php`
- `database\migrations\*_create_admin_2fa_*.php`
- `resources\views\admin\2fa\*.blade.php`
# CAPTCHA 통합 가이드

## 개요

@jiny/admin은 봇 공격과 브루트포스 공격을 방지하기 위해 Google reCAPTCHA와 hCaptcha를 모두 지원합니다.

## 설정

### 1. 설정 파일 수정

`jiny/admin/config/setting.php` 파일에서 CAPTCHA 설정을 직접 수정하세요:

```php
'captcha' => [
    // CAPTCHA 기능 활성화 여부
    'enabled' => true,  // true로 변경하여 활성화
    
    // CAPTCHA 드라이버 (recaptcha, hcaptcha)
    'driver' => 'recaptcha',
    
    // CAPTCHA 표시 모드 (always: 항상, conditional: 조건부)
    'mode' => 'conditional',
    
    // 조건부 모드에서 CAPTCHA를 표시할 실패 시도 횟수
    'show_after_attempts' => 3,
    
    // Google reCAPTCHA 설정
    'recaptcha' => [
        'site_key' => 'your_site_key_here',  // 여기에 Site Key 입력
        'secret_key' => 'your_secret_key_here',  // 여기에 Secret Key 입력
        'version' => 'v2',  // v2 또는 v3
        'threshold' => 0.5,  // v3용 점수 임계값
    ],
    
    // hCaptcha 설정
    'hcaptcha' => [
        'site_key' => 'your_site_key_here',  // 여기에 Site Key 입력
        'secret_key' => 'your_secret_key_here',  // 여기에 Secret Key 입력
    ],
],
```

### 2. Google reCAPTCHA 키 발급

1. [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)에 접속
2. 새 사이트 등록
3. reCAPTCHA 버전 선택 (v2 또는 v3)
4. 도메인 추가
5. Site Key와 Secret Key 획득

### 3. hCaptcha 키 발급

1. [hCaptcha Dashboard](https://dashboard.hcaptcha.com)에 접속
2. 새 사이트 등록
3. Site Key와 Secret Key 획득

## 작동 방식

### 모드

1. **always**: 모든 로그인 시도에 CAPTCHA 표시
2. **conditional**: 설정된 실패 횟수 이후에만 CAPTCHA 표시

### 조건부 모드 동작

- 로그인 실패 횟수가 `show_after_attempts` 값에 도달하면 CAPTCHA 표시
- 이메일과 IP 주소 기반으로 실패 횟수 추적
- 성공적인 로그인 시 실패 횟수 초기화

## 커스터마이징

### 메시지 변경

`jiny/admin/config/setting.php`에서 CAPTCHA 메시지를 커스터마이징할 수 있습니다:

```php
'captcha' => [
    // ...
    'messages' => [
        'required' => 'CAPTCHA 인증이 필요합니다.',
        'failed' => 'CAPTCHA 인증에 실패했습니다. 다시 시도해주세요.',
        'expired' => 'CAPTCHA가 만료되었습니다. 페이지를 새로고침해주세요.',
        'not_configured' => 'CAPTCHA가 올바르게 설정되지 않았습니다.',
    ],
],
```

### 테마 설정

로그인 페이지에서 CAPTCHA 테마를 변경할 수 있습니다:

```php
$captchaDriver->render([
    'theme' => 'dark',  // 'light' 또는 'dark'
    'size' => 'normal', // 'normal' 또는 'compact'
]);
```

## 미들웨어 사용

특정 라우트에 CAPTCHA 미들웨어를 적용할 수 있습니다:

```php
Route::post('/admin/sensitive-action', [Controller::class, 'action'])
    ->middleware(['captcha']);
```

## 프로그래매틱 사용

### CAPTCHA 필요 여부 확인

```php
use Jiny\Admin\App\Services\Captcha\CaptchaManager;

$captchaManager = app(CaptchaManager::class);
$isRequired = $captchaManager->isRequired($email, $ip);
```

### CAPTCHA 검증

```php
$driver = $captchaManager->driver();
$isValid = $driver->verify($captchaResponse, $remoteIp);

if (!$isValid) {
    $error = $driver->getErrorMessage();
    // 에러 처리
}
```

### 실패 횟수 관리

```php
// 실패 횟수 증가
$captchaManager->incrementFailedAttempts($email, $ip);

// 실패 횟수 초기화
$captchaManager->resetFailedAttempts($email, $ip);
```

## 로그

CAPTCHA 관련 이벤트는 `admin_user_logs` 테이블에 기록됩니다:

- `captcha_missing`: CAPTCHA 응답 누락
- `captcha_failed`: CAPTCHA 검증 실패
- `captcha_success`: CAPTCHA 검증 성공

## 문제 해결

### CAPTCHA가 표시되지 않음

1. JavaScript 콘솔에서 에러 확인
2. Site Key가 올바르게 설정되었는지 확인
3. 도메인이 CAPTCHA 서비스에 등록되었는지 확인

### CAPTCHA 검증 실패

1. Secret Key가 올바른지 확인
2. 서버 시간이 정확한지 확인
3. IP 주소가 차단되지 않았는지 확인

### 개발 환경에서 테스트

`jiny/admin/config/setting.php`에 테스트 키를 설정하세요:

**Google reCAPTCHA 테스트 키:**
```php
'recaptcha' => [
    'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
    'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    'version' => 'v2',
],
```

**hCaptcha 테스트 키:**
```php
'hcaptcha' => [
    'site_key' => '10000000-ffff-ffff-ffff-000000000001',
    'secret_key' => '0x0000000000000000000000000000000000000000',
],
```

## 보안 고려사항

1. **Secret Key 보호**: Secret Key를 절대 클라이언트 사이드 코드에 노출하지 마세요
2. **HTTPS 사용**: 프로덕션 환경에서는 반드시 HTTPS를 사용하세요
3. **Rate Limiting**: CAPTCHA와 함께 Rate Limiting을 병행 사용하세요
4. **로그 모니터링**: CAPTCHA 실패 로그를 정기적으로 모니터링하세요
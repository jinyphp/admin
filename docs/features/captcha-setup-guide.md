# CAPTCHA 설정 완전 가이드

## 목차
1. [개요](#개요)
2. [Google reCAPTCHA 가입 및 키 발급](#google-recaptcha-가입-및-키-발급)
3. [hCaptcha 가입 및 키 발급](#hcaptcha-가입-및-키-발급)
4. [Laravel 프로젝트 설정](#laravel-프로젝트-설정)
5. [코드 동작 원리](#코드-동작-원리)
6. [문제 해결](#문제-해결)

## 개요

@jiny/admin 패키지는 두 가지 CAPTCHA 서비스를 지원합니다:
- **Google reCAPTCHA v2**: 가장 널리 사용되는 CAPTCHA 서비스
- **hCaptcha**: 개인정보 보호 중심의 대안 서비스

### CAPTCHA가 필요한 이유
- 무차별 대입 공격(Brute Force Attack) 방지
- 자동화된 봇 차단
- 관리자 계정 보안 강화

## Google reCAPTCHA 가입 및 키 발급

### 1단계: Google reCAPTCHA 콘솔 접속
1. 브라우저에서 https://www.google.com/recaptcha/admin/create 접속
2. Google 계정으로 로그인 (Gmail 계정 필요)

### 2단계: 새 사이트 등록

![reCAPTCHA 등록 화면]

#### 필수 입력 항목:

**1. Label (라벨)**
```
예시: My Admin Panel
설명: 사이트를 식별하기 위한 이름
```

**2. reCAPTCHA type (유형 선택)**
```
✅ reCAPTCHA v2 선택
   ✅ "I'm not a robot" Checkbox 선택
   
❌ reCAPTCHA v3 (현재 미지원)
❌ reCAPTCHA Enterprise (유료)
```

**3. Domains (도메인)**
```
개발 환경:
- localhost
- 127.0.0.1

운영 환경:
- yourdomain.com
- www.yourdomain.com
- admin.yourdomain.com

💡 팁: 한 줄에 하나씩 입력, 여러 도메인 추가 가능
```

**4. Owners (소유자)**
```
기본값: 현재 로그인한 Google 계정
추가: 팀원 이메일 추가 가능
```

**5. Terms of Service (약관)**
```
✅ Accept the reCAPTCHA Terms of Service
✅ Send alerts to owners (선택사항)
```

### 3단계: Submit 클릭

### 4단계: 키 확인 및 복사

등록 완료 후 두 개의 키가 표시됩니다:

```
Site Key (사이트 키):
6Lc_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
→ 프론트엔드에서 사용 (공개 가능)

Secret Key (비밀 키):
6Lc_YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY
→ 백엔드에서 사용 (절대 공개 금지!)
```

### 5단계: 추가 설정 (선택사항)

**Settings 탭에서 설정 가능한 항목:**
- Security Preference: 보안 수준 조정
- Domain Name Validation: 도메인 검증 활성화
- Analytics: 통계 확인

## hCaptcha 가입 및 키 발급

### 1단계: hCaptcha 가입
1. https://www.hcaptcha.com/ 접속
2. "Sign Up" 클릭
3. 이메일, 비밀번호 입력
4. 이메일 인증 완료

### 2단계: 대시보드 접속
1. 로그인 후 Dashboard 접속
2. "Sites" 메뉴 클릭
3. "New Site" 버튼 클릭

### 3단계: 사이트 정보 입력

```
Site Name: My Admin Panel
Hostnames:
- localhost (개발용)
- yourdomain.com (운영용)

Difficulty: Moderate (권장)
Passing Mode: Auto (권장)
```

### 4단계: 키 확인

```
Sitekey: 10000000-ffff-ffff-ffff-000000000001
Secret: 0x0000000000000000000000000000000000000000
```

### hCaptcha 장점
- 사용자 개인정보 보호 중심
- GDPR 준수
- 무료 플랜 제공
- 수익 공유 프로그램 (선택사항)

## Laravel 프로젝트 설정

### 1단계: 환경 변수 설정

`.env` 파일에 다음 내용 추가:

#### Google reCAPTCHA 사용 시:
```env
# CAPTCHA 기본 설정
ADMIN_CAPTCHA_ENABLED=true
ADMIN_CAPTCHA_DRIVER=recaptcha
ADMIN_CAPTCHA_MODE=always

# Google reCAPTCHA v2 키
RECAPTCHA_SITE_KEY=여기에_사이트_키_입력
RECAPTCHA_SECRET_KEY=여기에_비밀_키_입력
```

#### hCaptcha 사용 시:
```env
# CAPTCHA 기본 설정
ADMIN_CAPTCHA_ENABLED=true
ADMIN_CAPTCHA_DRIVER=hcaptcha
ADMIN_CAPTCHA_MODE=always

# hCaptcha 키
HCAPTCHA_SITE_KEY=여기에_사이트_키_입력
HCAPTCHA_SECRET_KEY=여기에_비밀_키_입력
```

#### 개발 환경 테스트용 키:
```env
# ⚠️ 개발 환경 전용 - 운영 환경 사용 금지!
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```

### 2단계: 캐시 초기화

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 3단계: CAPTCHA 모드 설정

`config/admin/setting.php` 또는 `.env` 파일에서 설정:

```php
'captcha' => [
    'enabled' => env('ADMIN_CAPTCHA_ENABLED', true),
    'driver' => env('ADMIN_CAPTCHA_DRIVER', 'recaptcha'),
    'mode' => env('ADMIN_CAPTCHA_MODE', 'conditional'),
    // ...
]
```

#### 모드 옵션:
- **`always`**: 항상 CAPTCHA 표시
- **`conditional`**: 3회 로그인 실패 후 표시
- **`ip_based`**: 의심스러운 IP에서만 표시

### 4단계: 확인

1. 브라우저에서 관리자 로그인 페이지 접속
2. CAPTCHA 위젯이 표시되는지 확인
3. 로그인 시도 시 CAPTCHA 검증 확인

## 코드 동작 원리

### 1. CAPTCHA Manager 아키텍처

```
┌─────────────────────────────────────┐
│         CaptchaManager              │
│  (메인 CAPTCHA 관리 클래스)          │
└──────────┬──────────────────────────┘
           │
    ┌──────┴──────┐
    │             │
┌───▼────┐  ┌────▼────┐
│Recaptcha│  │hCaptcha │
│ Driver  │  │ Driver  │
└─────────┘  └─────────┘
```

### 2. 로그인 플로우

```php
// AdminAuth.php - 로그인 프로세스
public function login(Request $request)
{
    // Step 1: CAPTCHA 필요 여부 확인
    $captchaManager = app(CaptchaManager::class);
    if ($captchaManager->isRequired($email, $ip)) {
        
        // Step 2: CAPTCHA 응답 검증
        $captchaResponse = $request->input('g-recaptcha-response');
        if (!$captchaManager->verify($captchaResponse, $ip)) {
            
            // Step 3: 실패 로그 기록
            AdminUserLog::log('captcha_failed', ...);
            return redirect()->back()->withErrors(['captcha' => '인증 실패']);
        }
        
        // Step 4: 성공 로그 기록
        AdminUserLog::log('captcha_success', ...);
    }
    
    // Step 5: 일반 로그인 프로세스 진행
    // ...
}
```

### 3. CAPTCHA 필요 여부 판단 로직

```php
// CaptchaManager.php
public function isRequired($email, $ip)
{
    switch ($this->mode) {
        case 'always':
            return true;
            
        case 'conditional':
            // 실패 횟수 확인
            $failures = $this->getFailedAttempts($email, $ip);
            return $failures >= 3;
            
        case 'ip_based':
            // IP 평판 확인
            return $this->isSuspiciousIp($ip);
            
        default:
            return false;
    }
}
```

### 4. 프론트엔드 통합

```blade
{{-- login.blade.php --}}
@php
    $captchaManager = app(\Jiny\Admin\App\Services\Captcha\CaptchaManager::class);
    $showCaptcha = $captchaManager->isRequired(old('email'), request()->ip());
@endphp

@if($showCaptcha && config('admin.setting.captcha.enabled'))
    <div class="captcha-container">
        {!! $captchaManager->driver()->render() !!}
    </div>
@endif
```

### 5. CAPTCHA 검증 프로세스

```php
// Google reCAPTCHA 검증
public function verify($response, $ip = null)
{
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    
    $data = [
        'secret' => $this->secretKey,
        'response' => $response,
        'remoteip' => $ip
    ];
    
    $result = Http::asForm()->post($verifyUrl, $data);
    $json = $result->json();
    
    return $json['success'] ?? false;
}
```

### 6. 로그 기록 시스템

```php
// CAPTCHA 이벤트 로깅
AdminUserLog::log('captcha_success', null, [
    'email' => $email,
    'ip_address' => $ip,
    'score' => $score,        // reCAPTCHA v3 점수
    'hostname' => $hostname,  // 검증된 호스트명
    'challenge_ts' => $ts,    // 챌린지 타임스탬프
]);
```

### 7. 데이터베이스 스키마

```sql
-- admin_user_logs 테이블
CREATE TABLE admin_user_logs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULL,
    email VARCHAR(255),
    action VARCHAR(50),  -- captcha_success, captcha_failed, captcha_missing
    details JSON,         -- CAPTCHA 상세 정보
    ip_address VARCHAR(45),
    user_agent TEXT,
    browser VARCHAR(50),
    platform VARCHAR(50),
    logged_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 문제 해결

### 1. CAPTCHA가 표시되지 않음

**증상:**
- 로그인 페이지에 CAPTCHA 위젯이 보이지 않음

**해결 방법:**
```bash
# 1. 환경 변수 확인
cat .env | grep CAPTCHA

# 2. 설정 캐시 초기화
php artisan config:clear

# 3. 브라우저 콘솔에서 JavaScript 오류 확인
# F12 → Console 탭
```

### 2. "Invalid site key" 오류

**원인:**
- 잘못된 사이트 키
- 도메인 불일치

**해결 방법:**
1. Google reCAPTCHA 콘솔에서 키 재확인
2. 도메인 설정 확인 (localhost 추가 여부)
3. `.env` 파일의 RECAPTCHA_SITE_KEY 확인

### 3. 419 Page Expired 오류

**원인:**
- CSRF 토큰 만료
- 세션 타임아웃

**해결 방법:**
```bash
# 세션 초기화
php artisan session:clear

# 캐시 초기화
php artisan cache:clear

# 브라우저 쿠키/캐시 삭제
```

### 4. CAPTCHA 검증 실패 (백엔드)

**원인:**
- 잘못된 Secret Key
- 네트워크 문제

**해결 방법:**
```php
// 디버그 로깅 활성화
Log::debug('CAPTCHA verification', [
    'response' => $captchaResponse,
    'ip' => $request->ip(),
    'result' => $verificationResult
]);
```

### 5. "Score too low" (reCAPTCHA v3)

**원인:**
- 봇으로 의심되는 행동 패턴

**해결 방법:**
```php
// config/admin/setting.php
'threshold' => [
    'score' => 0.3,  // 0.5 → 0.3으로 낮춤
],
```

### 6. 로컬 개발 환경 설정

**Docker/Vagrant 사용 시:**
```env
# 실제 호스트 IP 사용
RECAPTCHA_TRUSTED_IPS=192.168.1.0/24,10.0.0.0/8
```

### 7. 프록시/로드밸런서 환경

**Cloudflare, AWS ELB 사용 시:**
```php
// config/admin/setting.php
'trusted_proxies' => [
    '173.245.48.0/20',  // Cloudflare IP 범위
    '103.21.244.0/22',
],
```

## 보안 모범 사례

### 1. 키 관리
- ❌ 절대 Git에 실제 키 커밋 금지
- ✅ `.env` 파일 사용
- ✅ 운영/개발 환경 키 분리

### 2. 모니터링
```php
// CAPTCHA 실패율 모니터링
$failureRate = AdminUserLog::where('action', 'captcha_failed')
    ->where('logged_at', '>=', now()->subHour())
    ->count();

if ($failureRate > 100) {
    // 알림 발송
}
```

### 3. IP 차단 연동
```php
// 반복 실패 시 IP 자동 차단
if ($captchaFailures > 5) {
    IpBlacklist::add($ip, 'Too many CAPTCHA failures');
}
```

### 4. 로그 분석
```sql
-- 의심스러운 활동 탐지
SELECT ip_address, COUNT(*) as failures
FROM admin_user_logs
WHERE action = 'captcha_failed'
  AND logged_at > NOW() - INTERVAL 1 DAY
GROUP BY ip_address
HAVING failures > 10;
```

## 추가 리소스

- [Google reCAPTCHA 공식 문서](https://developers.google.com/recaptcha/docs/v2)
- [hCaptcha 개발자 가이드](https://docs.hcaptcha.com/)
- [Laravel CAPTCHA 패키지들](https://github.com/topics/laravel-captcha)
- [@jiny/admin 저장소](https://github.com/jinyphp/admin)

## 라이선스 및 비용

### Google reCAPTCHA
- **무료**: 월 100만 건 이하
- **유료**: reCAPTCHA Enterprise (월 $8부터)

### hCaptcha
- **무료**: 무제한
- **Pro**: 월 $99 (고급 기능)
- **Enterprise**: 맞춤 가격

## 지원 및 문의

문제가 지속되면 다음 채널로 문의:
- GitHub Issues: [프로젝트 저장소]/issues
- 이메일: admin@yourdomain.com
- 커뮤니티 포럼: [포럼 URL]
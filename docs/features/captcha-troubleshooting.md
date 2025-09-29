# CAPTCHA 트러블슈팅 가이드

## 일반적인 문제 해결

### 🔴 문제: CAPTCHA가 표시되지 않음

#### 증상
- 로그인 페이지에 CAPTCHA 위젯이 없음
- 빈 공간만 표시됨

#### 진단
```bash
# 1. 환경 변수 확인
php artisan tinker
>>> config('admin.setting.captcha.enabled')
>>> config('admin.setting.captcha.driver')
>>> env('RECAPTCHA_SITE_KEY')
```

#### 해결 방법
```bash
# 1. .env 파일 확인
cat .env | grep -E "CAPTCHA|RECAPTCHA"

# 2. 필수 설정 추가
echo "ADMIN_CAPTCHA_ENABLED=true" >> .env
echo "ADMIN_CAPTCHA_DRIVER=recaptcha" >> .env

# 3. 캐시 초기화
php artisan config:clear
php artisan view:clear

# 4. 브라우저 개발자 도구 확인
# F12 → Console → JavaScript 오류 확인
```

---

### 🔴 문제: 419 Page Expired 오류

#### 증상
- 로그인 시도 시 419 오류 페이지
- "Page Expired" 메시지

#### 원인
- CSRF 토큰 만료
- 세션 타임아웃
- 쿠키 문제

#### 해결 방법
```bash
# 1. 세션 초기화
php artisan session:clear

# 2. 캐시 초기화
php artisan cache:clear
php artisan config:clear

# 3. 새 CSRF 토큰 생성
php artisan key:generate

# 4. 브라우저 쿠키 삭제
# Chrome: 설정 → 개인정보 → 쿠키 삭제
```

#### 코드 수정 (필요시)
```php
// config/session.php
'lifetime' => 120,  // → 240 (분 단위 증가)
'expire_on_close' => false,
```

---

### 🔴 문제: Invalid Site Key 오류

#### 증상
- "ERROR for site owner: Invalid site key"
- CAPTCHA 위젯에 오류 메시지

#### 원인
- 잘못된 Site Key
- 도메인 불일치

#### 해결 방법

1. **키 재확인**
```bash
# .env 파일 확인
grep RECAPTCHA_SITE_KEY .env

# Google reCAPTCHA 콘솔에서 키 복사
# https://www.google.com/recaptcha/admin/sites
```

2. **도메인 설정 확인**
```
Google reCAPTCHA 콘솔 → Settings → Domains
추가해야 할 도메인:
- localhost
- 127.0.0.1
- yourdomain.com
```

3. **올바른 키 설정**
```env
# Site Key (프론트엔드용) - Secret Key와 혼동 주의!
RECAPTCHA_SITE_KEY=6LcXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

---

### 🔴 문제: CAPTCHA 검증 실패 (백엔드)

#### 증상
- CAPTCHA 체크 후에도 "인증 실패" 메시지
- 로그인 불가

#### 진단
```php
// 임시 디버그 코드 추가
// app/Http/Controllers/Web/Login/AdminAuth.php

Log::debug('CAPTCHA Debug', [
    'response' => $request->input('g-recaptcha-response'),
    'has_response' => !empty($request->input('g-recaptcha-response')),
    'secret_key' => substr(config('captcha.secret'), 0, 10) . '...',
]);
```

#### 해결 방법

1. **Secret Key 확인**
```bash
# Secret Key (백엔드용) 확인
grep RECAPTCHA_SECRET_KEY .env
```

2. **네트워크 연결 확인**
```bash
# Google API 연결 테스트
curl -X POST https://www.google.com/recaptcha/api/siteverify \
  -d "secret=YOUR_SECRET_KEY&response=test"
```

3. **서버 시간 동기화**
```bash
# 서버 시간 확인 (5분 이상 차이 시 문제)
date
timedatectl status

# NTP 동기화
sudo ntpdate -s time.nist.gov
```

---

### 🔴 문제: CAPTCHA 로그가 기록되지 않음

#### 증상
- `/admin/user/captcha/logs` 페이지가 비어있음
- CAPTCHA 시도 기록 없음

#### 진단
```bash
php artisan tinker
>>> \DB::table('admin_user_logs')->whereIn('action', ['captcha_success', 'captcha_failed'])->count()
>>> config('admin.setting.captcha.log.enabled')
```

#### 해결 방법

1. **로그 설정 활성화**
```php
// config/admin/setting.php
'captcha' => [
    'log' => [
        'enabled' => true,  // false → true
        'failed_only' => false,
    ],
],
```

2. **테이블 컬럼 확인**
```bash
php artisan tinker
>>> Schema::hasColumn('admin_user_logs', 'browser')
>>> Schema::hasColumn('admin_user_logs', 'platform')
```

3. **누락된 컬럼 추가**
```bash
php artisan make:migration add_browser_platform_to_admin_user_logs
```

```php
// migration 파일
public function up()
{
    Schema::table('admin_user_logs', function ($table) {
        $table->string('browser', 50)->nullable();
        $table->string('platform', 50)->nullable();
    });
}
```

---

### 🔴 문제: 모바일에서 CAPTCHA가 너무 작음

#### 증상
- 모바일 화면에서 CAPTCHA 체크박스가 작음
- 터치하기 어려움

#### 해결 방법

```blade
{{-- resources/views/Site/Login/login.blade.php --}}

{{-- 반응형 CAPTCHA 컨테이너 --}}
<div class="captcha-wrapper" style="transform: scale(1.0); transform-origin: 0 0;">
    @php
        $captchaDriver->render([
            'theme' => 'light',
            'size' => 'normal',  // 'compact' for mobile
        ]);
    @endphp
</div>

<style>
@media (max-width: 640px) {
    .captcha-wrapper {
        transform: scale(0.9) !important;
    }
}
</style>
```

---

### 🔴 문제: 다크 모드에서 CAPTCHA가 안 보임

#### 증상
- 다크 테마에서 CAPTCHA 위젯이 보이지 않음
- 흰색 배경에 흰색 텍스트

#### 해결 방법

```php
// 다크 모드 감지 및 테마 설정
$theme = request()->cookie('theme', 'light');

$captchaDriver->render([
    'theme' => $theme === 'dark' ? 'dark' : 'light',
    'size' => 'normal'
]);
```

---

## 디버깅 도구

### 1. CAPTCHA 상태 확인 스크립트

```php
// artisan 명령어 생성
// app/Console/Commands/CheckCaptcha.php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCaptcha extends Command
{
    protected $signature = 'captcha:check';
    protected $description = 'Check CAPTCHA configuration';

    public function handle()
    {
        $this->info('CAPTCHA Configuration Check');
        $this->line('----------------------------');
        
        // 기본 설정
        $enabled = config('admin.setting.captcha.enabled');
        $driver = config('admin.setting.captcha.driver');
        $mode = config('admin.setting.captcha.mode');
        
        $this->table(
            ['Setting', 'Value', 'Status'],
            [
                ['Enabled', $enabled ? 'Yes' : 'No', $enabled ? '✅' : '❌'],
                ['Driver', $driver, $driver ? '✅' : '❌'],
                ['Mode', $mode, $mode ? '✅' : '❌'],
            ]
        );
        
        // 키 설정
        $siteKey = env('RECAPTCHA_SITE_KEY');
        $secretKey = env('RECAPTCHA_SECRET_KEY');
        
        $this->line('');
        $this->info('API Keys:');
        $this->line('Site Key: ' . ($siteKey ? substr($siteKey, 0, 20) . '...' : 'NOT SET ❌'));
        $this->line('Secret Key: ' . ($secretKey ? 'SET ✅' : 'NOT SET ❌'));
        
        // 연결 테스트
        if ($siteKey && $secretKey) {
            $this->line('');
            $this->info('Testing API connection...');
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'secret' => $secretKey,
                'response' => 'test'
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $this->line('API Connection: OK ✅');
            } else {
                $this->error('API Connection: FAILED ❌');
            }
        }
        
        return 0;
    }
}
```

사용법:
```bash
php artisan captcha:check
```

### 2. 브라우저 콘솔 디버그

```javascript
// 브라우저 콘솔에서 실행 (F12)

// reCAPTCHA 상태 확인
if (typeof grecaptcha !== 'undefined') {
    console.log('✅ reCAPTCHA loaded');
    console.log('Version:', grecaptcha.getResponse() ? 'v2' : 'unknown');
} else {
    console.log('❌ reCAPTCHA not loaded');
}

// CAPTCHA 수동 리셋
if (typeof grecaptcha !== 'undefined') {
    grecaptcha.reset();
    console.log('CAPTCHA reset');
}
```

### 3. 로그 모니터링

```bash
# 실시간 로그 모니터링
tail -f storage/logs/laravel.log | grep -i captcha

# CAPTCHA 실패 통계
php artisan tinker
>>> DB::table('admin_user_logs')
...     ->where('action', 'captcha_failed')
...     ->where('logged_at', '>=', now()->subDay())
...     ->groupBy('ip_address')
...     ->select('ip_address', DB::raw('count(*) as failures'))
...     ->orderBy('failures', 'desc')
...     ->get();
```

---

## 긴급 복구

### CAPTCHA 완전 비활성화 (긴급 시)

```env
# .env 파일
ADMIN_CAPTCHA_ENABLED=false
```

```bash
php artisan config:clear
```

### 임시 우회 (개발용)

```php
// app/Http/Controllers/Web/Login/AdminAuth.php
private function verifyCaptcha(Request $request)
{
    // 임시 비활성화 (운영 환경 금지!)
    if (app()->environment('local')) {
        return null;
    }
    
    // 원래 코드...
}
```

---

## 지원 연락처

해결되지 않는 문제는 다음으로 문의:

- 📧 이메일: support@yourdomain.com
- 💬 Slack: #tech-support
- 🐛 GitHub Issues: [저장소]/issues
- 📞 긴급: 010-XXXX-XXXX (업무시간)
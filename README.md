# Jiny Admin Package

JinyPHP `Admin` 관리자 패키지입니다.
Laravel 기반의 강력한 관리자 백엔드 시스템을 제공하는 패키지로, 보안, 인증, 권한 관리 등 엔터프라이즈급 관리 기능을 포함합니다.

## 📋 주요 기능

### 🔐 보안 기능
- **2FA (Two-Factor Authentication)** - Google Authenticator 지원
- **IP 화이트리스트** - 특정 IP만 관리자 접근 허용
- **CAPTCHA 통합** - reCAPTCHA, hCAPTCHA 지원
- **세션 관리** - 동시 로그인 제한, 세션 추적
- **비밀번호 정책** - 주기적 변경, 복잡도 검증
- **로그인 시도 제한** - 무차별 공격 방지

### 👥 사용자 관리
- **계층적 권한 관리** - 관리자 타입별 권한 설정
- **사용자 활동 로그** - 모든 관리자 활동 추적
- **프로필 관리** - 아바타, 개인정보 설정
- **대량 사용자 관리** - CLI 명령어 지원

### 📧 알림 시스템
- **이메일 템플릿** - 커스터마이징 가능한 템플릿
- **SMS 통합** - 다중 SMS 제공자 지원
- **Webhook** - Slack, Discord 등 외부 서비스 연동
- **실시간 알림** - 브라우저 알림 지원

### 🛠 개발 도구
- **Artisan 명령어** - 관리자 CRUD 자동 생성
- **Livewire 컴포넌트** - 반응형 UI 컴포넌트
- **RESTful API** - API 엔드포인트 제공
- **다국어 지원** - i18n 지원

## 📁 디렉토리 구조

```
vendor/jiny/admin/
├── src/
│   ├── Console/          # Artisan 명령어
│   ├── Http/
│   │   ├── Controllers/  # 컨트롤러
│   │   ├── Middleware/   # 미들웨어
│   │   ├── Livewire/     # Livewire 컴포넌트
│   │   └── Trait/        # HTTP 트레이트
│   ├── Models/           # Eloquent 모델
│   ├── Services/         # 비즈니스 로직
│   │   ├── Captcha/      # CAPTCHA 서비스
│   │   ├── Email/        # 이메일 서비스
│   │   ├── Notification/ # 알림 서비스
│   │   ├── Security/     # 보안 서비스
│   │   └── SMS/          # SMS 서비스
│   ├── Traits/           # 재사용 가능한 트레이트
│   └── JinyAdminServiceProvider.php
├── config/               # 설정 파일
├── database/
│   ├── migrations/       # 마이그레이션
│   ├── seeders/          # 시더
│   └── factories/        # 팩토리
├── resources/
│   └── views/           # Blade 템플릿
├── routes/              # 라우트 정의
├── stubs/               # 코드 생성 템플릿
└── tests/               # 테스트 파일
```

## 🚀 설치

### 요구사항
- PHP 8.2+
- Laravel 12.x
- MySQL/PostgreSQL/SQLite
- Composer 2.x

### 설치 방법

1. Composer를 통한 패키지 설치:
```bash
composer require jiny/admin
```

2. 데이터베이스 마이그레이션 실행:
```bash
php artisan migrate
```

3. 설정 파일 발행 (선택사항):
```bash
php artisan vendor:publish --provider="Jiny\Admin\JinyAdminServiceProvider"
```

4. 초기 관리자 생성:
```bash
php artisan admin:user-create
```

## ⚙️ 설정

### 환경 변수 (.env)

```env
# 2FA 설정
ADMIN_2FA_ENABLED=true
ADMIN_2FA_ISSUER="Your App Name"

# CAPTCHA 설정
ADMIN_CAPTCHA_DRIVER=recaptcha
RECAPTCHA_SITE_KEY=your-site-key
RECAPTCHA_SECRET_KEY=your-secret-key

# IP 화이트리스트
ADMIN_IP_WHITELIST_ENABLED=true

# 세션 설정
ADMIN_SESSION_LIFETIME=120
ADMIN_CONCURRENT_SESSIONS=1

# 비밀번호 정책
ADMIN_PASSWORD_EXPIRY_DAYS=90
ADMIN_PASSWORD_MIN_LENGTH=8
```

## 📚 사용법

### 기본 라우트
- `/admin` - 관리자 대시보드
- `/admin/login` - 관리자 로그인
- `/admin/users` - 사용자 관리
- `/admin/settings` - 시스템 설정

### Artisan 명령어

```bash
# 관리자 CRUD 생성
php artisan admin:make ResourceName

# 사용자 관리
php artisan admin:user-create
php artisan admin:user-delete
php artisan admin:users --list

# 보안 관리
php artisan admin:ip-unblock
php artisan admin:unblock-password
php artisan admin:captcha-logs

# 유지보수
php artisan admin:ip-cleanup
php artisan admin:sync-usertype-count
```

### 미들웨어 사용

```php
// routes/web.php
Route::middleware(['admin', 'ip.whitelist', 'captcha'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
});
```

### Livewire 컴포넌트

```blade
{{-- 관리자 테이블 --}}
@livewire('jiny-admin::admin-table', ['model' => 'User'])

{{-- 관리자 폼 --}}
@livewire('jiny-admin::admin-create', ['model' => 'User'])
@livewire('jiny-admin::admin-edit', ['model' => 'User', 'id' => $id])
```

## 🔒 보안 기능 상세

### 2FA 구현
```php
use Jiny\Admin\Services\Security\TwoFactorService;

$twoFactor = new TwoFactorService();
$qrCode = $twoFactor->generateQRCode($user);
$verified = $twoFactor->verify($user, $code);
```

### CAPTCHA 통합
```php
use Jiny\Admin\Services\Captcha\CaptchaManager;

$captcha = app(CaptchaManager::class);
$verified = $captcha->verify($request->get('g-recaptcha-response'));
```

### IP 화이트리스트
```php
use Jiny\Admin\Models\AdminIpWhitelist;

AdminIpWhitelist::create([
    'ip_address' => '192.168.1.1',
    'description' => 'Office IP',
    'is_active' => true
]);
```

## 🧪 테스트

```bash
# 전체 테스트 실행
php artisan test

# 특정 테스트 실행
php artisan test --filter=AdminTest
```

## 📄 라이센스

이 패키지는 MIT 라이센스 하에 배포됩니다.

## 🤝 기여하기

버그 리포트, 기능 제안, 풀 리퀘스트는 언제나 환영합니다!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 지원

- 이슈: [GitHub Issues](https://github.com/jinyphp/admin/issues)
- 문서: [공식 문서](https://jinyphp.com/docs/admin)
- 이메일: support@jinyphy.com

## 🎯 로드맵

- [ ] GraphQL API 지원
- [ ] 다크 모드 지원
- [ ] 실시간 대시보드
- [ ] AI 기반 보안 감지
- [ ] 멀티 테넌시 지원

---

**Jiny Admin** - Enterprise-grade Admin Panel for Laravel
Made with ❤️ by JinyPHP Team
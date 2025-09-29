# CAPTCHA 설정 가이드

## 빠른 시작 (개발 환경)

1. `.env` 파일에 테스트 키 추가:
```bash
# Google reCAPTCHA v2 테스트 키
ADMIN_CAPTCHA_ENABLED=true
ADMIN_CAPTCHA_DRIVER=recaptcha
ADMIN_CAPTCHA_MODE=always
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```

2. 캐시 초기화:
```bash
php artisan config:clear
php artisan cache:clear
```

3. 로그인 페이지에서 CAPTCHA 확인:
- http://localhost:8000/admin/login
- CAPTCHA 체크박스가 표시되어야 함

## 운영 환경 설정

### Option 1: Google reCAPTCHA v2

1. [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin/create) 접속
2. 새 사이트 등록:
   - Label: 사이트 이름
   - Type: reCAPTCHA v2 → "I'm not a robot" Checkbox
   - Domains: 실제 도메인 추가
3. 발급된 키를 `.env`에 설정

### Option 2: hCaptcha

1. [hCaptcha](https://www.hcaptcha.com/) 가입
2. Dashboard → Sites → New Site
3. 발급된 키를 `.env`에 설정:
```bash
ADMIN_CAPTCHA_DRIVER=hcaptcha
HCAPTCHA_SITE_KEY=your_site_key
HCAPTCHA_SECRET_KEY=your_secret_key
```

## CAPTCHA 모드 설정

```bash
# always: 항상 표시
ADMIN_CAPTCHA_MODE=always

# conditional: 3회 실패 후 표시
ADMIN_CAPTCHA_MODE=conditional

# disabled: 비활성화
ADMIN_CAPTCHA_ENABLED=false
```

## 문제 해결

### CAPTCHA가 표시되지 않는 경우:
1. `.env` 파일의 키 확인
2. `php artisan config:clear` 실행
3. 브라우저 개발자 도구에서 JavaScript 오류 확인

### 419 오류 발생 시:
1. `php artisan session:clear` 실행
2. 브라우저 쿠키/캐시 삭제
3. CSRF 토큰 확인

### CAPTCHA 로그 확인:
- 관리자 대시보드 → CAPTCHA 로그
- URL: http://localhost:8000/admin/user/captcha/logs

## 보안 권장사항

1. **운영 환경에서는 반드시 실제 키 사용**
2. **키는 절대 Git에 커밋하지 않기**
3. **정기적으로 키 갱신**
4. **IP 화이트리스트와 함께 사용 권장**
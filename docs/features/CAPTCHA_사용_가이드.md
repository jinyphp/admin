# CAPTCHA 사용 가이드

## 📋 개요

@jiny/admin 패키지는 Google reCAPTCHA와 hCaptcha를 지원하여 로그인 보안을 강화합니다.

## 🚀 빠른 시작

### 1. CAPTCHA 활성화 상태 확인

현재 CAPTCHA는 **활성화**되어 있습니다:
- 설정 파일: `jiny/admin/config/setting.php`
- 활성화 상태: `'enabled' => true`
- 드라이버: `'driver' => 'recaptcha'` (Google reCAPTCHA v2)
- 모드: `'mode' => 'conditional'` (3회 로그인 실패 후 표시)

### 2. 테스트용 키 사용 중

현재 Google의 테스트용 reCAPTCHA 키가 설정되어 있습니다:
```php
'recaptcha' => [
    'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',  // 테스트용
    'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',  // 테스트용
],
```

⚠️ **주의**: 이 키들은 Google이 제공하는 테스트용 키로, 실제 보안을 제공하지 않습니다.

## 🔑 프로덕션 설정

### 1. Google reCAPTCHA 키 발급받기

1. [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin) 접속
2. Google 계정으로 로그인
3. "+" 버튼 클릭하여 새 사이트 등록
4. 설정 입력:
   - **Label**: 사이트 이름 (예: "My Admin Panel")
   - **reCAPTCHA type**: 
     - reCAPTCHA v2 > "I'm not a robot" Checkbox 선택
   - **Domains**: 사용할 도메인 입력
     - 개발: `localhost`
     - 프로덕션: `yourdomain.com`
5. 약관 동의 후 "Submit"
6. 발급된 Site Key와 Secret Key 복사

### 2. 설정 파일 업데이트

`jiny/admin/config/setting.php` 파일 수정:

```php
'captcha' => [
    'enabled' => true,
    'driver' => 'recaptcha',
    'mode' => 'conditional',  // 또는 'always'
    'show_after_attempts' => 3,  // 몇 번 실패 후 표시할지
    
    'recaptcha' => [
        'site_key' => 'YOUR_SITE_KEY_HERE',     // 실제 Site Key로 교체
        'secret_key' => 'YOUR_SECRET_KEY_HERE',  // 실제 Secret Key로 교체
        'version' => 'v2',  // v2 또는 v3
    ],
],
```

### 3. 캐시 초기화

설정 변경 후 캐시를 초기화합니다:

```bash
php artisan config:clear
php artisan cache:clear
```

## 📊 CAPTCHA 로그 모니터링

### 1. CAPTCHA 로그 조회

```bash
# 최근 7일간의 CAPTCHA 로그 조회 (기본)
php artisan admin:captcha-logs

# 최근 30일간의 로그 조회
php artisan admin:captcha-logs --days=30

# 실패한 CAPTCHA만 조회
php artisan admin:captcha-logs --type=failed

# 특정 이메일의 CAPTCHA 로그
php artisan admin:captcha-logs --email=user@example.com

# 특정 IP의 CAPTCHA 로그
php artisan admin:captcha-logs --ip=192.168.1.1

# CSV 파일로 내보내기
php artisan admin:captcha-logs --export=captcha_logs.csv
```

### 2. 로그 출력 예시

```
=== CAPTCHA 로그 분석 ===

📊 최근 7일 CAPTCHA 통계
+----------+------+-------+
| 항목     | 건수 | 비율  |
+----------+------+-------+
| 총 시도  | 150  | 100%  |
| 성공     | 120  | 80%   |
| 실패     | 20   | 13.3% |
| 미입력   | 10   | 6.7%  |
+----------+------+-------+

🕐 최근 CAPTCHA 로그
+-------------+----------+----------------------+-------------+------+-------+
| 시간        | 상태     | 이메일               | IP          | 점수 | 오류  |
+-------------+----------+----------------------+-------------+------+-------+
| 09-10 14:23 | ✅ 성공  | admin@example.com   | 192.168.1.1 | 0.9  | -     |
| 09-10 14:20 | ❌ 실패  | test@example.com    | 192.168.1.2 | 0.2  | -     |
+-------------+----------+----------------------+-------------+------+-------+

🌐 IP별 CAPTCHA 시도
+-------------+----------+------+------+--------+---------+
| IP 주소     | 총 시도  | 성공 | 실패 | 미입력 | 성공률  |
+-------------+----------+------+------+--------+---------+
| 192.168.1.1 | 25       | 23   | 1    | 1      | 92.0%   |
| 192.168.1.2 | 15       | 5    | 8    | 2      | 33.3%   |
+-------------+----------+------+------+--------+---------+

🚨 의심스러운 IP 감지:
  • 192.168.1.2 - 시도: 15, 실패: 8
```

## ⚙️ 고급 설정

### 1. CAPTCHA 모드 변경

#### always 모드 (항상 표시)
```php
'mode' => 'always',
```
- 모든 로그인 시도에서 CAPTCHA 표시

#### conditional 모드 (조건부 표시)
```php
'mode' => 'conditional',
'show_after_attempts' => 3,  // 3회 실패 후부터 표시
```
- 지정된 횟수만큼 로그인 실패 후 CAPTCHA 표시

### 2. hCaptcha로 변경

hCaptcha를 사용하려면:

1. [hCaptcha](https://www.hcaptcha.com/) 가입 및 키 발급
2. 설정 파일 수정:

```php
'driver' => 'hcaptcha',
'hcaptcha' => [
    'site_key' => 'YOUR_HCAPTCHA_SITE_KEY',
    'secret_key' => 'YOUR_HCAPTCHA_SECRET_KEY',
],
```

### 3. reCAPTCHA v3 사용

reCAPTCHA v3는 사용자 상호작용 없이 작동합니다:

```php
'recaptcha' => [
    'site_key' => 'YOUR_V3_SITE_KEY',
    'secret_key' => 'YOUR_V3_SECRET_KEY',
    'version' => 'v3',
    'threshold' => 0.5,  // 0.0 ~ 1.0 (높을수록 엄격)
],
```

## 🔒 보안 권장사항

### 1. 프로덕션 체크리스트

- [ ] 테스트용 키를 실제 키로 교체
- [ ] HTTPS 사용 (reCAPTCHA v3 필수)
- [ ] 적절한 threshold 값 설정 (v3)
- [ ] 정기적인 로그 모니터링
- [ ] 의심스러운 IP 차단

### 2. 모니터링 자동화

크론 작업으로 일일 리포트 생성:

```bash
# crontab -e
0 9 * * * php /path/to/artisan admin:captcha-logs --days=1 --export=/var/log/captcha_daily.csv
```

### 3. 실패율 임계값 설정

높은 실패율을 보이는 IP 자동 차단:

```php
// 향후 구현 예정
'auto_block' => [
    'enabled' => true,
    'threshold' => 10,  // 10회 이상 실패
    'period' => 60,     // 60분 내
    'block_duration' => 1440,  // 24시간 차단
],
```

## 🐛 문제 해결

### CAPTCHA가 표시되지 않음

1. 설정 확인:
```bash
php artisan tinker
>>> config('admin.setting.captcha.enabled')
```

2. 캐시 초기화:
```bash
php artisan config:clear
php artisan cache:clear
```

3. 로그인 실패 횟수 확인:
```bash
php artisan admin:captcha-logs --email=your@email.com
```

### CAPTCHA 검증 실패

1. Secret Key 확인
2. 도메인 설정 확인 (Google reCAPTCHA Admin)
3. 네트워크 연결 확인

### 테스트 환경에서 CAPTCHA 비활성화

개발/테스트 환경에서 비활성화:

```php
'enabled' => env('APP_ENV') === 'production',
```

## 📚 추가 자료

- [Google reCAPTCHA 문서](https://developers.google.com/recaptcha)
- [hCaptcha 문서](https://docs.hcaptcha.com/)
- [@jiny/admin 문서](../README.md)
- [관리자 콘솔 명령어](./관리자_콘솔.md)

## 💡 팁

1. **개발 환경**: 테스트용 키 사용으로 빠른 개발
2. **스테이징 환경**: 실제 키로 테스트
3. **프로덕션 환경**: 모니터링 및 자동 차단 활성화

## 🆘 지원

문제가 있으시면 다음 채널로 문의하세요:
- GitHub Issues: [@jiny/admin](https://github.com/jiny/admin/issues)
- Email: support@jiny.dev
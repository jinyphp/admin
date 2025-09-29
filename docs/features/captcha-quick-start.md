# CAPTCHA 빠른 시작 가이드

## 5분 안에 CAPTCHA 설정하기

### 1. 테스트 키로 즉시 시작 (개발용)

`.env` 파일에 추가:
```env
ADMIN_CAPTCHA_ENABLED=true
ADMIN_CAPTCHA_DRIVER=recaptcha
ADMIN_CAPTCHA_MODE=always
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```

캐시 초기화:
```bash
php artisan config:clear
```

✅ 완료! http://localhost:8000/admin/login 에서 CAPTCHA 확인

---

### 2. 실제 Google reCAPTCHA 키 발급 (운영용)

#### Step 1: Google reCAPTCHA 접속
👉 https://www.google.com/recaptcha/admin/create

#### Step 2: 사이트 등록
- **Label**: `My Admin`
- **Type**: `reCAPTCHA v2` → `"I'm not a robot" Checkbox`
- **Domains**: 
  - `localhost` (개발)
  - `yourdomain.com` (운영)

#### Step 3: 키 복사
```
Site Key: 6Lc_XXXXXXXXXXXXXXX
Secret Key: 6Lc_YYYYYYYYYYYYYYY
```

#### Step 4: `.env` 업데이트
```env
RECAPTCHA_SITE_KEY=6Lc_XXXXXXXXXXXXXXX
RECAPTCHA_SECRET_KEY=6Lc_YYYYYYYYYYYYYYY
```

---

### 3. CAPTCHA 모드 설정

| 모드 | 설명 | 사용 시기 |
|------|------|----------|
| `always` | 항상 표시 | 높은 보안 필요 |
| `conditional` | 3회 실패 후 표시 | 일반적 사용 (권장) |
| `disabled` | 비활성화 | 개발/테스트 |

```env
ADMIN_CAPTCHA_MODE=conditional  # 권장 설정
```

---

## 자주 묻는 질문 (FAQ)

### Q: CAPTCHA가 안 보여요
```bash
# 1. 키 확인
grep CAPTCHA .env

# 2. 캐시 초기화
php artisan config:clear

# 3. 브라우저 콘솔 확인 (F12)
```

### Q: 419 오류가 발생해요
```bash
# 세션 재시작
php artisan session:clear
php artisan cache:clear
```

### Q: "Invalid site key" 오류
- Google reCAPTCHA 콘솔에서 `localhost` 도메인 추가 확인
- Site Key 복사 오류 확인 (Secret Key와 혼동 주의)

### Q: 테스트 키 경고 메시지 제거
- 실제 키 발급 필요 (무료)
- 테스트 키는 개발 환경에서만 사용

---

## 운영 체크리스트

- [ ] 실제 reCAPTCHA 키 발급
- [ ] `.env` 파일에 실제 키 설정
- [ ] 운영 도메인 등록 확인
- [ ] CAPTCHA 모드를 `conditional`로 설정
- [ ] CAPTCHA 로그 모니터링 활성화
- [ ] IP 차단 기능 연동 (선택)

---

## 도움말

📚 [상세 설정 가이드](./captcha-setup-guide.md)
🔧 [트러블슈팅](./captcha-troubleshooting.md)
📊 [CAPTCHA 로그 분석](./captcha-logs.md)
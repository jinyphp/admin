# Jiny Admin 보안 시스템 문서

## 개요

Jiny Admin 패키지는 엔터프라이즈급 보안 기능을 제공하여 관리자 시스템을 안전하게 보호합니다. 이 문서는 구현된 모든 보안 기능에 대한 상세한 설명과 사용 가이드를 제공합니다.

## 목차

1. [메일 시스템과 알림](./email-system.md)
2. [로그인 시도 제한](./login-protection.md)
3. [2단계 인증 (2FA)](./two-factor-auth.md)
4. [IP 관리 시스템](./ip-management.md)
5. [감사 로그](./audit-logs.md)

## 빠른 시작

### 기본 설정

```php
// config/admin/setting.php

return [
    'security' => [
        'enable_2fa' => true,           // 2FA 활성화
        'enable_ip_whitelist' => true,   // IP 화이트리스트
        'enable_captcha' => true,        // CAPTCHA
        'max_login_attempts' => 5,       // 최대 로그인 시도
        'lockout_duration' => 30,        // 잠금 시간 (분)
    ]
];
```

## 주요 서비스

### 1. NotificationService
- **위치**: `App\Services\NotificationService.php`
- **역할**: 멀티채널 알림 발송 (이메일, SMS, 웹훅, 푸시)

### 2. IpTrackingService
- **위치**: `App\Services\IpTrackingService.php`
- **역할**: IP 기반 접근 제어 및 지역 차단

### 3. TwoFactorAuthService
- **위치**: `App\Services\TwoFactorAuthService.php`
- **역할**: 2단계 인증 관리

### 4. CaptchaManager
- **위치**: `App\Services\Captcha\CaptchaManager.php`
- **역할**: CAPTCHA 검증 및 관리

## 보안 체크리스트

- ✅ 2FA 활성화
- ✅ IP 화이트리스트 설정
- ✅ CAPTCHA 설정
- ✅ 비밀번호 정책 구성
- ✅ 감사 로그 모니터링
- ✅ 알림 채널 구성
- ✅ 백업 정책 수립

## 문제 해결

### 일반적인 문제

1. **로그인 차단**: IP 차단 또는 계정 잠금 확인
2. **2FA 문제**: 백업 코드 사용 또는 관리자 문의
3. **알림 미수신**: 채널 설정 및 이벤트 구독 확인

## 관련 문서

- [미들웨어 가이드](../middleware.md)
- [설정 가이드](../configuration.md)
- [API 레퍼런스](../api-reference.md)
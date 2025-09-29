# Jiny Admin

Laravel Admin CRUD 생성기 with Livewire 3 - Laravel 애플리케이션을 위한 포괄적인 관리자 패널 시스템

## 개요

Jiny Admin은 Livewire 3와 Tailwind CSS v4로 구축된 현대적이고 반응형 관리자 인터페이스와 함께 자동 CRUD 생성을 제공하는 강력한 Laravel 패키지입니다.

## 시스템 요구사항

- PHP 8.2 이상
- Laravel 11.0 이상 또는 12.0 이상
- Livewire 3.0 이상
- Tailwind CSS 4.0 이상

## 설치

### Composer를 통한 설치

```bash
composer require jinyerp/admin
```

패키지는 자동으로 다음 작업을 수행합니다:
- 데이터베이스 마이그레이션 실행
- 설정 파일 배포
- 에셋 파일 배포

### 수동 설치

로컬에서 개발하거나 모듈로 설치하려는 경우:

1. 패키지를 `jiny/admin` 디렉토리로 복사
2. `composer.json`에 추가:

```json
{
    "autoload": {
        "psr-4": {
            "Jiny\\Admin\\": "jiny/admin/"
        }
    }
}
```

3. composer dump-autoload 실행:
```bash
composer dump-autoload
```

4. 마이그레이션 실행:
```bash
php artisan migrate
```

## 빠른 시작

### 관리자 모듈 생성

```bash
# 완전한 관리자 모듈 생성 (모든 구성 요소)
php artisan admin:make shop product

# 특정 구성 요소만 생성
php artisan admin:make shop product --controller  # 컨트롤러 + JSON
php artisan admin:make shop product --view        # 뷰만
php artisan admin:make shop product --model       # 모델만
```

### 관리자 패널 접속

설치 후 다음 주소에서 관리자 패널에 접속할 수 있습니다:

```
http://your-domain.com/admin
```

## 주요 기능

- **자동 CRUD 생성** - 단일 명령으로 완전한 관리자 모듈 생성
- **Livewire 3 컴포넌트** - 페이지 새로고침 없는 동적이고 반응형 UI
- **고급 보안** - 2단계 인증, IP 화이트리스트/블랙리스트, 세션 관리
- **포괄적인 로깅** - 모든 관리자 활동과 사용자 작업 추적
- **SMS/이메일 통합** - 내장된 알림 지원
- **Tailwind CSS v4** - 현대적이고 반응형 디자인
- **Hook 시스템** - Hook을 통한 동작 커스터마이징
- **JSON 설정** - JSON 설정 파일을 통한 손쉬운 커스터마이징

## 문서

자세한 문서는 [docs/index.md](docs/index.md) 파일을 참조하세요.

## 지원

문제, 질문 또는 기여 사항은 [GitHub 저장소](https://github.com/jinyphp/admin)를 방문해 주세요.

## 라이선스

Jiny Admin 패키지는 [MIT 라이선스](LICENSE)에 따라 오픈 소스 소프트웨어로 제공됩니다.
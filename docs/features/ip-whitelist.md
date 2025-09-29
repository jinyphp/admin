# IP 접근 제한 기능

## 개요
관리자 페이지에 대한 IP 기반 접근 제어 시스템으로, 허가된 IP 주소에서만 관리자 페이지에 접근할 수 있도록 제한합니다.

## 구현 일자
2025-09-10

## 주요 기능

### 1. IP 타입 지원
- **단일 IP**: 개별 IP 주소 등록 (예: 192.168.1.100)
- **IP 범위**: 시작과 종료 IP 지정 (예: 192.168.1.1 ~ 192.168.1.255)
- **CIDR 표기법**: 서브넷 마스크 방식 (예: 192.168.1.0/24)

### 2. 관리 기능
- IP 화이트리스트 CRUD 관리
- 임시 허용 (만료일 설정)
- 활성화/비활성화 토글
- 접근 로그 및 통계 추적
- 마지막 접근 시간 및 접근 횟수 기록

### 3. 성능 최적화
- Redis/파일 캐싱을 통한 빠른 IP 검증
- 캐시 TTL 설정 가능 (기본 5분)
- 자동 캐시 무효화

## 시스템 구성

### 파일 구조
```
jiny/admin/
├── App/
│   ├── Http/
│   │   ├── Middleware/
│   │   │   └── IpWhitelistMiddleware.php      # IP 검증 미들웨어
│   │   └── Controllers/Admin/AdminIpWhitelist/
│   │       ├── AdminIpWhitelist.php           # 메인 컨트롤러
│   │       ├── AdminIpWhitelistCreate.php     # 생성 컨트롤러
│   │       ├── AdminIpWhitelistEdit.php       # 수정 컨트롤러
│   │       ├── AdminIpWhitelistDelete.php     # 삭제 컨트롤러
│   │       ├── AdminIpWhitelistShow.php       # 상세 컨트롤러
│   │       └── AdminIpWhitelist.json          # 설정 파일
│   └── Models/
│       └── AdminIpWhitelist.php               # Eloquent 모델
├── config/
│   └── setting.php                            # IP 화이트리스트 설정
├── database/migrations/
│   └── 2025_09_10_000000_create_admin_ip_whitelist_table.php
└── resources/views/admin/admin_ip_whitelist/
    ├── table.blade.php                        # 목록 뷰
    ├── search.blade.php                       # 검색 폼
    ├── create.blade.php                       # 생성 폼
    ├── edit.blade.php                         # 수정 폼
    └── show.blade.php                         # 상세 뷰
```

### 데이터베이스 스키마

#### admin_ip_whitelist 테이블
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | bigint | Primary Key |
| ip_address | varchar(45) | IP 주소 (IPv4/IPv6) |
| description | varchar(255) | 설명 |
| type | varchar(20) | IP 타입 (single/range/cidr) |
| ip_range_start | varchar(45) | IP 범위 시작 |
| ip_range_end | varchar(45) | IP 범위 끝 |
| cidr_prefix | integer | CIDR 프리픽스 |
| is_active | boolean | 활성화 여부 |
| added_by | varchar(255) | 추가한 관리자 |
| expires_at | datetime | 만료일시 |
| access_count | integer | 접근 횟수 |
| last_accessed_at | datetime | 마지막 접근 시간 |
| metadata | json | 추가 정보 |
| created_at | timestamp | 생성일시 |
| updated_at | timestamp | 수정일시 |

#### admin_ip_access_logs 테이블
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | bigint | Primary Key |
| ip_address | varchar(45) | 접근 IP |
| url | varchar(255) | 접근 URL |
| method | varchar(10) | HTTP 메소드 |
| user_agent | varchar(255) | User Agent |
| user_id | bigint | 사용자 ID |
| email | varchar(255) | 사용자 이메일 |
| is_allowed | boolean | 허용/차단 여부 |
| reason | varchar(255) | 차단 사유 |
| request_data | json | 요청 데이터 |
| created_at | timestamp | 생성일시 |
| updated_at | timestamp | 수정일시 |

## 설치 및 설정

### 1. 마이그레이션 실행
```bash
php artisan migrate
```

### 2. 환경 변수 설정 (.env)
```env
# IP 화이트리스트 활성화
ADMIN_IP_WHITELIST_ENABLED=true

# 동작 모드 (strict: 차단, log_only: 로그만)
ADMIN_IP_WHITELIST_MODE=strict

# 캐시 설정
ADMIN_IP_WHITELIST_CACHE_KEY=admin_ip_whitelist
ADMIN_IP_WHITELIST_CACHE_TTL=300

# 기본 허용 IP (개발용)
ADMIN_IP_WHITELIST_DEFAULT_ALLOWED="127.0.0.1,::1"
```

### 3. 설정 파일 (config/setting.php)
```php
'ip_whitelist' => [
    'enabled' => env('ADMIN_IP_WHITELIST_ENABLED', false),
    'mode' => env('ADMIN_IP_WHITELIST_MODE', 'strict'),
    'cache' => [
        'key' => env('ADMIN_IP_WHITELIST_CACHE_KEY', 'admin_ip_whitelist'),
        'ttl' => env('ADMIN_IP_WHITELIST_CACHE_TTL', 300),
    ],
    'default_allowed' => explode(',', env('ADMIN_IP_WHITELIST_DEFAULT_ALLOWED', '127.0.0.1,::1')),
    'trusted_proxies' => explode(',', env('ADMIN_IP_WHITELIST_TRUSTED_PROXIES', '')),
],
```

### 4. 미들웨어 등록
```php
// app/Http/Kernel.php 또는 bootstrap/app.php
protected $middlewareAliases = [
    // ...
    'ip.whitelist' => \Jiny\Admin\App\Http\Middleware\IpWhitelistMiddleware::class,
];
```

### 5. 라우트 적용
```php
// 특정 라우트 그룹에 적용
Route::middleware(['web', 'auth', 'ip.whitelist'])->prefix('admin')->group(function () {
    // 관리자 라우트들...
});

// 또는 개별 라우트에 적용
Route::get('/admin/sensitive', function () {
    // ...
})->middleware('ip.whitelist');
```

## 사용 방법

### 1. 관리 페이지 접속
- URL: `/admin/security/ip-whitelist`
- 메뉴: 관리자 > 보안 > IP 화이트리스트

### 2. IP 추가
1. "새 IP 추가" 버튼 클릭
2. IP 타입 선택 (단일/범위/CIDR)
3. IP 정보 입력
4. 설명 추가 (예: "본사 사무실")
5. 필요시 만료일 설정
6. 저장

### 3. IP 관리
- **활성화/비활성화**: 토글 스위치로 즉시 변경
- **수정**: 수정 아이콘 클릭
- **삭제**: 삭제 아이콘 클릭 (확인 후 삭제)
- **일괄 삭제**: 체크박스 선택 후 일괄 삭제

### 4. 접근 로그 확인
- 각 IP의 마지막 접근 시간 표시
- 접근 횟수 통계 제공
- 상세 보기에서 접근 이력 확인

## Hook 메소드

컨트롤러에서 다음 Hook 메소드들을 오버라이드하여 커스터마이징 가능:

### AdminIpWhitelistCreate
```php
public function hookCreating($wire, $form)
{
    // IP 생성 전 검증 또는 수정
    $form['added_by'] = Auth::user()->email;
    return $form;
}

public function hookStoring($wire, $form)
{
    // 저장 전 추가 처리
    return $form;
}

public function hookStored($wire, $form)
{
    // 저장 후 처리 (알림 발송 등)
    Cache::forget('admin_ip_whitelist');
}
```

### AdminIpWhitelistDelete
```php
public function hookDeleting($wire, $ids, $deleteType = 'single')
{
    // 삭제 전 검증
    // false 반환시 삭제 취소
    return true;
}

public function hookDeleted($wire, $ids)
{
    // 삭제 후 처리
    Cache::forget('admin_ip_whitelist');
}
```

## 보안 고려사항

1. **프록시 서버 사용시**
   - `ADMIN_IP_WHITELIST_TRUSTED_PROXIES` 설정 필요
   - X-Forwarded-For 헤더 신뢰 설정

2. **로컬 개발 환경**
   - 기본적으로 127.0.0.1과 ::1은 허용
   - 개발시 IP 화이트리스트 비활성화 가능

3. **로그 모니터링**
   - 차단된 접근 시도 정기적 확인
   - 비정상적인 패턴 감지시 조치

4. **캐시 관리**
   - IP 변경시 자동 캐시 무효화
   - 수동 캐시 클리어: `php artisan cache:clear`

## 문제 해결

### IP가 차단되는 경우
1. 현재 IP 확인: 생성 페이지에 현재 IP 힌트 표시
2. 로컬호스트 접속으로 IP 추가
3. 데이터베이스 직접 수정 (비상시)

### 캐시 문제
```bash
# 캐시 클리어
php artisan cache:clear

# 설정 캐시 재생성
php artisan config:cache
```

### 미들웨어가 작동하지 않는 경우
1. 미들웨어 등록 확인
2. 라우트에 미들웨어 적용 확인
3. 설정 파일 확인 (enabled = true)

## 관련 문서
- [컨트롤러 리소스 규칙](../컨트롤러_리소스.md)
- [미들웨어 가이드](https://laravel.com/docs/middleware)
- [Laravel 캐싱](https://laravel.com/docs/cache)
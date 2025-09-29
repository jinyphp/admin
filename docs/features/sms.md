# SMS 발송 관리 시스템

## 개요

@jiny/admin의 SMS 발송 관리 시스템은 Vonage(Nexmo) API를 통해 SMS 메시지를 발송하고 관리하는 통합 솔루션입니다. 메시지 생성, 즉시 발송, 발송 이력 관리, 재발송 등의 기능을 제공합니다.

## 주요 기능

### 1. SMS 메시지 관리
- SMS 메시지 생성, 수정, 삭제
- 발송 이력 관리 및 조회
- 발송 상태 추적 (대기중, 발송완료, 수신확인, 발송실패)
- 메시지 검색 및 필터링
- 발송일자 기준 최신순 정렬 (sent_at/created_at COALESCE)

### 2. SMS 발송 방법

#### 2.1 목록에서 개별 발송
- 대기중(pending) 상태의 메시지에 발송 버튼 표시
- 발송 버튼 클릭 시 확인 대화상자 후 즉시 SMS 전송
- 실패한 메시지는 재발송 버튼 제공
- AJAX를 통한 비동기 발송 처리

#### 2.2 생성 시 즉시 발송
- **SMS 발송** 버튼: 메시지 저장 후 입력한 번호로 즉시 발송
- **테스트 발송** 버튼: 메시지 저장 후 관리자 번호로 테스트 발송
  - 메시지 앞에 "[테스트]" 접두사 자동 추가
  - 관리자 번호는 `.env` 파일의 `ADMIN_TEST_PHONE` 설정
- HookCustom 메서드를 통한 처리로 컴포넌트 수정 최소화

### 3. SMS 제공업체 관리
- 여러 SMS 제공업체 등록 및 관리
- 제공업체별 API 키, API Secret, 발신번호 설정
- 우선순위(priority) 기반 자동 선택
- 발송 통계 (sent_count, failed_count) 자동 업데이트
- 실시간 잔액(balance) 관리 및 업데이트

## 기술 구조

### 컨트롤러 구조
```
AdminSmsSend/
├── AdminSmsSend.php          # 메인 목록 및 발송 처리
├── AdminSmsSendCreate.php    # SMS 생성 및 즉시 발송
├── AdminSmsSendEdit.php      # SMS 수정
├── AdminSmsSendShow.php      # SMS 상세 보기
├── AdminSmsSendDelete.php    # SMS 삭제
└── AdminSmsSend.json        # 설정 파일
```

### 서비스 클래스
#### VonageSmsService
Vonage(Nexmo) API와의 통합을 담당합니다:

```php
use Jiny\Admin\App\Services\VonageSmsService;

$smsService = new VonageSmsService($providerId);
$result = $smsService->sendSms(
    $toNumber,    // 수신번호
    $message,     // 메시지 내용
    $fromNumber   // 발신번호 (선택)
);
```

주요 기능:
- SMS 발송 및 결과 처리
- 한국 전화번호 자동 포맷 변환 (010-xxxx-xxxx → 82-10-xxxx-xxxx)
- 오류 코드 한글 매핑
- 잔액 조회

## 데이터베이스 구조

### admin_sms_sends 테이블
SMS 발송 이력을 저장합니다:

| 필드 | 타입 | 설명 |
|------|------|------|
| id | integer | 기본키 |
| provider_id | integer | 제공업체 ID |
| provider_name | varchar | 제공업체명 |
| to_number | varchar | 수신번호 |
| from_number | varchar | 발신번호 |
| message | text | 메시지 내용 |
| message_length | integer | 메시지 길이 |
| message_count | integer | 메시지 건수 |
| status | varchar | 발송 상태 (pending/sent/delivered/failed) |
| message_id | varchar | API 메시지 ID |
| cost | numeric | 발송 비용 |
| response_data | text | API 응답 데이터 (JSON) |
| retry_count | integer | 재시도 횟수 |
| sent_at | datetime | 발송 시간 |
| failed_at | datetime | 실패 시간 |
| error_code | varchar | 오류 코드 |
| error_message | text | 오류 메시지 |

### admin_sms_providers 테이블
SMS 제공업체 정보를 관리합니다:

| 필드 | 타입 | 설명 |
|------|------|------|
| id | integer | 기본키 |
| provider_name | varchar | 제공업체명 |
| provider_type | varchar | 제공업체 타입 |
| api_key | varchar | API 키 |
| api_secret | varchar | API 시크릿 |
| from_number | varchar | 기본 발신번호 |
| balance | numeric | 잔액 |
| sent_count | integer | 발송 성공 수 |
| failed_count | integer | 발송 실패 수 |
| is_active | boolean | 활성화 여부 |
| is_default | boolean | 기본 제공업체 여부 |
| priority | integer | 우선순위 |
| last_used_at | datetime | 마지막 사용 시간 |

## Hook 시스템

@jiny/admin의 Hook 패턴을 활용하여 SMS 발송 프로세스를 커스터마이징할 수 있습니다:

### hookStoring
저장 전 데이터 전처리:
```php
public function hookStoring($wire, $data)
{
    // 제공업체 자동 선택
    if (empty($data['provider_id'])) {
        $defaultProvider = DB::table('admin_sms_providers')
            ->where('is_active', 1)
            ->orderBy('priority', 'desc')
            ->first();
        $data['provider_id'] = $defaultProvider->id;
    }
    
    // 메시지 길이 계산
    $data['message_length'] = mb_strlen($data['message']);
    $data['message_count'] = $data['message_length'] <= 70 ? 1 : 
                            ceil($data['message_length'] / 67);
    
    return $data;
}
```

### hookStored
저장 후 즉시 발송 처리:
```php
public function hookStored($wire, $data)
{
    // 테스트 발송 여부 확인
    $isTestSend = $wire->testSendFlag ?? false;
    
    if ($isTestSend) {
        $adminPhone = env('ADMIN_TEST_PHONE');
        $data['to_number'] = $adminPhone;
        $data['message'] = '[테스트] ' . $data['message'];
    }
    
    // SMS API 호출
    $smsService = new VonageSmsService($data['provider_id']);
    $result = $smsService->sendSms(
        $data['to_number'],
        $data['message'],
        $data['from_number']
    );
    
    // 결과 업데이트
    if ($result['success']) {
        DB::table('admin_sms_sends')->where('id', $data['id'])
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'message_id' => $result['message_id']
            ]);
    }
}
```

### hookCustomSendSms / hookCustomTestSend
커스텀 액션 처리:
```php
public function hookCustomSendSms($wire, $params = [])
{
    // 일반 발송
    $wire->save(false);
    return true;
}

public function hookCustomTestSend($wire, $params = [])
{
    // 테스트 발송 플래그 설정
    $wire->testSendFlag = true;
    $wire->save(false);
    return true;
}
```

### hookCustomRows
목록 조회 커스터마이징:
```php
public function hookCustomRows($wire)
{
    $query = DB::table('admin_sms_sends');
    
    // sent_at이 NULL인 경우 created_at 사용
    if ($wire->sortField === 'sent_at') {
        $query->orderByRaw("COALESCE(sent_at, created_at) " . 
                          $wire->sortDirection);
    } else {
        $query->orderBy($wire->sortField, $wire->sortDirection);
    }
    
    return $query->paginate($wire->perPage);
}
```

## 설정

### 환경 변수 (.env)
```env
# 관리자 테스트 번호
ADMIN_TEST_PHONE=01039113106

# Vonage API 설정 (선택적)
VONAGE_API_KEY=your_api_key
VONAGE_API_SECRET=your_api_secret
```

### JSON 설정 (AdminSmsSend.json)
```json
{
    "table": {
        "name": "admin_sms_sends"
    },
    "index": {
        "sorting": {
            "default": "sent_at",
            "direction": "desc"
        },
        "features": {
            "enableCreate": true,
            "enableDelete": true,
            "enableEdit": true
        }
    }
}
```

## 라우트 구조
```
GET    /admin/sms/send           # 목록
GET    /admin/sms/send/create    # 생성 폼
POST   /admin/sms/send/{id}/send # 개별 발송
POST   /admin/sms/send/{id}/resend # 재발송
GET    /admin/sms/send/{id}      # 상세보기
GET    /admin/sms/send/{id}/edit # 수정 폼
DELETE /admin/sms/send/{id}      # 삭제
```

## 사용 예제

### 1. SMS 생성 및 즉시 발송
```php
// AdminSmsSendCreate 컨트롤러의 hookStored
$smsService = new VonageSmsService($data['provider_id']);
$result = $smsService->sendSms(
    $data['to_number'], 
    $data['message'], 
    $data['from_number']
);

if ($result['success']) {
    // 성공 처리
    DB::table('admin_sms_sends')->where('id', $data['id'])
        ->update([
            'status' => 'sent',
            'sent_at' => now(),
            'message_id' => $result['message_id'],
            'cost' => $result['message_price']
        ]);
}
```

### 2. 목록에서 개별 발송
```javascript
// table.blade.php
function sendSms(id) {
    if (confirm('이 SMS를 발송하시겠습니까?')) {
        fetch(`/admin/sms/send/${id}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                              .getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        });
    }
}
```

### 3. 테스트 발송 구현
```html
<!-- create.blade.php -->
<button type="button" 
        wire:click="callCustomAction('testSend')"
        wire:confirm="관리자 번호로 테스트 발송하시겠습니까?"
        class="btn btn-secondary">
    테스트 발송 (관리자 번호로)
</button>
```

## 주의사항

### 1. 비용 관리
- SMS 발송은 실제 비용이 발생합니다
- 테스트 시 테스트 발송 기능을 활용하세요
- 잔액을 주기적으로 확인하세요

### 2. 메시지 제한
- 한글 70자, 영문 160자 초과 시 장문(LMS) 요금 적용
- 메시지 건수가 자동으로 계산되어 표시됩니다
- Unicode 타입으로 발송되어 한글 지원

### 3. 법적 규제
- 광고성 메시지는 수신자 동의 필수
- 발신번호 사전 등록 필요 (한국)
- 야간 시간대 발송 제한 준수
- 정보통신망법 준수

### 4. 보안
- API 키는 환경 변수로 관리
- 발송 이력 및 IP 추적
- 관리자 권한 확인
- CSRF 토큰 검증

## 문제 해결

### 발송 실패 시
1. **API 키 확인**
   - admin_sms_providers 테이블에서 api_key, api_secret 확인
   - Vonage 대시보드에서 키 재확인

2. **발신번호 설정**
   - from_number가 비어있으면 'JINYPHP' 사용
   - 한국 번호는 사전 등록 필요

3. **잔액 확인**
   ```php
   $service = new VonageSmsService($providerId);
   $balance = $service->getBalance();
   ```

4. **오류 메시지 확인**
   - response_data 필드에서 상세 오류 확인
   - error_code별 의미:
     - 2: Missing from param (발신번호 누락)
     - 4: Invalid credentials (잘못된 인증)
     - 9: Quota exceeded (할당량 초과)

### 한국 번호 발송 오류
- 010으로 시작하는 번호는 자동으로 82로 변환됨
- 하이픈은 자동 제거됨
- 예: 010-1234-5678 → 821012345678

### 로그 확인
```bash
# Laravel 로그
tail -f storage/logs/laravel.log | grep -E "(hookStor|SMS|Vonage)"

# 데이터베이스 직접 확인
sqlite3 database/database.sqlite
SELECT id, to_number, status, error_message, sent_at 
FROM admin_sms_sends 
ORDER BY id DESC LIMIT 10;
```

## Vonage(Nexmo) 설정 가이드

### 1. 계정 생성
1. [Vonage Dashboard](https://dashboard.nexmo.com) 접속
2. 무료 계정 생성 (€2 크레딧 제공)
3. API Settings에서 API Key와 Secret 확인

### 2. 제공업체 등록
```sql
INSERT INTO admin_sms_providers (
    provider_name, 
    provider_type, 
    api_key, 
    api_secret,
    from_number,
    is_active,
    priority
) VALUES (
    'Vonage (Nexmo)',
    'vonage',
    'your_api_key',
    'your_api_secret',
    'JINYPHP',
    1,
    100
);
```

### 3. 오류 코드 참조
| 코드 | 의미 | 해결방법 |
|------|------|----------|
| 1 | 스로틀링 | 요청 속도 줄이기 |
| 2 | 누락된 매개변수 | 필수 파라미터 확인 |
| 3 | 잘못된 매개변수 | 파라미터 형식 확인 |
| 4 | 잘못된 자격 증명 | API 키 재확인 |
| 5 | 내부 오류 | 잠시 후 재시도 |
| 9 | 할당량 초과 | 잔액 충전 |
| 15 | 잘못된 수신 번호 | 번호 형식 확인 |

## 향후 개선 사항

### 단기 계획
- [ ] 대량 발송 기능 (CSV 업로드)
- [ ] 예약 발송 기능 (scheduled_at 활용)
- [ ] 발송 템플릿 관리
- [ ] 발송 통계 대시보드

### 장기 계획
- [ ] Twilio, AWS SNS 등 다중 제공업체 지원
- [ ] 페일오버 (실패 시 다른 제공업체로 자동 전환)
- [ ] Webhook을 통한 수신 확인 (delivered 상태)
- [ ] MMS(멀티미디어 메시지) 지원
- [ ] 국제 SMS 지원 확대
- [ ] 발송 로그 아카이빙
- [ ] API 엔드포인트 제공

## 라이선스

이 시스템은 @jiny/admin 패키지의 일부로 제공됩니다.

## 관련 문서
- [Laravel Livewire 문서](https://livewire.laravel.com)
- [Vonage SMS API 문서](https://developer.vonage.com/messaging/sms/overview)
- [@jiny/admin Hook 시스템](./hooks.md)
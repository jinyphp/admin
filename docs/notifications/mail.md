# 메일 시스템 완전 가이드

## 목차
1. [개요](#개요)
2. [이메일 설정](#이메일-설정)
3. [템플릿 구성](#템플릿-구성)
4. [이메일 발송](#이메일-발송)
5. [발송 로그 관리](#발송-로그-관리)
6. [고급 기능](#고급-기능)
7. [API 레퍼런스](#api-레퍼런스)
8. [문제 해결](#문제-해결)

---

## 개요

Jiny Admin의 메일 시스템은 엔터프라이즈급 이메일 관리 솔루션으로, 템플릿 기반 동적 이메일 발송, 이벤트 기반 자동 알림, 멀티채널 통합, 완벽한 발송 추적 및 관리 기능을 제공합니다.

### 시스템 접근 경로
- **메일 대시보드**: http://localhost:8000/admin/mail
- **이메일 템플릿**: http://localhost:8000/admin/mail/templates
- **발송 기록**: http://localhost:8000/admin/mail/logs
- **SMTP 설정**: http://localhost:8000/admin/settings/mail

### 핵심 기능
- **SMTP 설정 관리**: 다중 메일 서버 지원 (Gmail, AWS SES, Mailgun, Postmark 등)
- **템플릿 시스템**: WYSIWYG 에디터로 템플릿 작성, HTML/Text/Markdown 형식 지원
- **변수 치환**: 동적 콘텐츠 생성 (Mustache 문법 지원)
- **발송 관리**: 즉시 발송, 예약 발송, 대량 발송 (큐 시스템 활용)
- **로그 추적**: 발송 상태, 열람, 클릭 추적 (실시간 모니터링)
- **재발송 시스템**: 자동/수동 재발송 (실패 시 자동 재시도 3회)
- **A/B 테스팅**: 템플릿 성능 비교 분석
- **멀티채널 통합**: Email + SMS + Push 통합 발송

### 시스템 아키텍처
```
┌─────────────────────────────────────────────────────────┐
│                  메일 시스템 워크플로우                    │
└─────────────────────────────────────────────────────────┘
                            │
    ┌───────────┬───────────┼───────────┬───────────┐
    ▼           ▼           ▼           ▼           ▼
[1.SMTP설정] [2.템플릿작성] [3.발송처리] [4.로그기록] [5.추적관리]
    │           │           │           │           │
    │           │           │           │           │
[메일서버]   [템플릿DB]   [큐시스템]   [로그DB]    [통계분석]
    │           │           │           │           │
    ▼           ▼           ▼           ▼           ▼
  Gmail     admin_email   Redis/DB   admin_email  Dashboard
  AWS SES    _templates   Laravel Q     _logs     Analytics
  Mailgun                                         Reports
```

### 데이터베이스 구조
- **admin_email_templates**: 이메일 템플릿 저장
- **admin_email_logs**: 발송 기록 및 추적 정보
- **admin_email_attachments**: 첨부파일 관리
- **admin_email_blacklist**: 수신거부 목록

---

## 이메일 설정

### 1단계: SMTP 서버 설정

#### 환경 설정 (.env)
```bash
# 기본 메일 설정
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# 추가 옵션
MAIL_TIMEOUT=30
MAIL_AUTH_MODE=LOGIN
```

#### 관리자 패널 설정
1. **접속**: http://localhost:8000/admin/settings/mail
2. **설정 항목**:
   - 메일 드라이버 선택 (SMTP, Mailgun, SES, Postmark)
   - 호스트 및 포트 설정
   - 인증 정보 입력
   - 암호화 방식 선택 (TLS/SSL)
   - 발송자 정보 설정 (이름, 이메일)
   - Reply-To 주소 설정
   - 큐 드라이버 설정 (sync, database, redis)
   - 재시도 정책 설정 (횟수, 지연시간)

3. **권장 설정**:
   - **개발 환경**: Mailtrap 또는 log 드라이버
   - **운영 환경**: AWS SES 또는 Mailgun (대량 발송 시)
   - **큐 드라이버**: Redis (성능), Database (간편함)

#### 연결 테스트
```php
// 관리자 패널에서
1. "연결 테스트" 버튼 클릭
2. 연결 상태 확인
3. 오류 발생 시 디버그 정보 확인

// 프로그래밍 방식
use Jiny\Admin\App\Services\EmailService;

$emailService = new EmailService();
$result = $emailService->testConnection();

if ($result['success']) {
    echo "연결 성공!";
} else {
    echo "오류: " . $result['message'];
}
```

### 2단계: 발신자 설정

#### 기본 발신자
```php
// config/mail.php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'name' => env('MAIL_FROM_NAME', 'Example'),
],
```

#### 템플릿별 발신자
- 각 템플릿에서 개별 발신자 설정 가능
- Reply-To 주소 별도 지정 가능

### 3단계: 메일 서버별 설정 예시

#### Gmail
```bash
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
# 앱 비밀번호 사용 필수
```

#### AWS SES
```bash
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-key-id
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
```

#### Mailgun
```bash
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.example.com
MAILGUN_SECRET=your-mailgun-key
MAILGUN_ENDPOINT=api.mailgun.net
```

---

## 템플릿 구성

### 1단계: 템플릿 생성

#### 접속 경로
- **URL**: http://localhost:8000/admin/mail/templates
- **메뉴**: 메일 관리 > 이메일 템플릿

#### 템플릿 생성 과정
1. **"새 템플릿 생성" 클릭**
2. **기본 정보 입력**:
   ```
   이름: 회원가입 환영 메일
   슬러그: welcome_email (고유값, 영문/숫자/언더스코어만)
   카테고리: 회원관리
   우선순위: 높음 (0-100, 높을수록 우선)
   상태: 활성화
   타입: HTML (HTML/Text/Markdown 중 선택)
   ```

3. **제목 작성**:
   ```
   {{app_name}}에 오신 것을 환영합니다, {{user_name}}님!
   ```

4. **본문 작성** (WYSIWYG 에디터):
   ```html
   <h1>환영합니다!</h1>
   <p>안녕하세요 {{user_name}}님,</p>
   <p>{{app_name}}에 가입해 주셔서 감사합니다.</p>
   <p>계정을 활성화하려면 아래 버튼을 클릭하세요:</p>
   <a href="{{activation_link}}" class="button">계정 활성화</a>
   ```

5. **변수 정의**:
   ```json
   {
     "user_name": "사용자 이름",
     "app_name": "애플리케이션 이름",
     "activation_link": "활성화 링크"
   }
   ```

### 2단계: 변수 시스템 활용

#### 기본 변수 (시스템 제공)
```
{{user_name}} - 사용자 이름
{{user_email}} - 사용자 이메일
{{app_name}} - 앱 이름 (config('app.name'))
{{app_url}} - 앱 URL (config('app.url'))
{{current_date}} - 현재 날짜 (Y-m-d 형식)
{{current_time}} - 현재 시간 (H:i:s 형식)
{{current_year}} - 현재 연도
{{current_datetime}} - 현재 일시 (Y-m-d H:i:s)
{{unsubscribe_link}} - 수신거부 링크
```

#### 커스텀 변수
템플릿별로 필요한 변수를 정의하여 사용:
```json
{
  "order_id": "주문 번호",
  "product_name": "상품명",
  "total_price": "총 금액",
  "delivery_date": "배송 예정일"
}
```

#### 조건문
```html
{{#if is_premium}}
  <div class="premium-banner">
    프리미엄 회원 전용 혜택이 있습니다!
  </div>
{{else}}
  <div class="upgrade-banner">
    프리미엄으로 업그레이드하세요!
  </div>
{{/if}}
```

#### 반복문
```html
<h3>주문 상품 목록</h3>
<ul>
{{#each items}}
  <li>
    {{name}} - {{quantity}}개 - {{price}}원
  </li>
{{/each}}
</ul>
<p>총액: {{total_price}}원</p>
```

#### 필터
```html
{{price|number_format}} - 숫자 포맷
{{date|date_format:'Y-m-d'}} - 날짜 포맷
{{text|upper}} - 대문자 변환
{{html|strip_tags}} - HTML 태그 제거
```

### 3단계: 템플릿 미리보기 및 테스트

#### 미리보기
1. 템플릿 편집 화면에서 "미리보기" 탭 클릭
2. 테스트 데이터 입력:
   ```json
   {
     "user_name": "테스트 사용자",
     "app_name": "My App",
     "activation_link": "https://example.com/activate/123"
   }
   ```
3. 실시간 렌더링 결과 확인
4. **반응형 미리보기**: 데스크톱/태블릿/모바일 뷰 전환
5. **다크모드 미리보기**: 다크모드에서의 표시 확인
6. **이메일 클라이언트 미리보기**: Gmail, Outlook, Apple Mail 렌더링 시뮬레이션

#### 테스트 발송
1. "테스트 발송" 버튼 클릭
2. 수신자 이메일 입력
3. 테스트 데이터 확인/수정
4. "발송" 클릭
5. 이메일 수신 확인

### 4단계: 템플릿 관리

#### 템플릿 버전 관리
- 수정 시 자동으로 이전 버전 저장 (최대 10개 버전 보관)
- 버전 히스토리 확인 가능 (수정자, 수정시간, 변경내용)
- 이전 버전으로 롤백 가능 (원클릭 복원)
- 버전 간 비교 기능 (Diff 뷰 제공)
- 버전별 성능 메트릭 비교 (열람률, 클릭률)

#### 템플릿 복제
- 기존 템플릿을 기반으로 새 템플릿 생성
- 유사한 템플릿 빠르게 작성

#### 템플릿 내보내기/가져오기
- JSON 형식으로 내보내기
- 다른 시스템에서 가져오기

---

## 이메일 발송

### 1. 즉시 발송

#### 단일 발송
```php
use Jiny\Admin\App\Services\EmailService;
use Jiny\Admin\App\Models\AdminEmailTemplate;

$emailService = new EmailService();

// 템플릿 사용
$result = $emailService->sendWithTemplate(
    'welcome_email',  // 템플릿 슬러그
    'user@example.com',  // 수신자
    [
        'user_name' => '홍길동',
        'activation_link' => 'https://example.com/activate/123'
    ]
);

// 직접 발송
$result = $emailService->send(
    'user@example.com',
    '제목',
    '<h1>내용</h1>',
    'html'
);
```

#### 대량 발송
```php
// 수신자 목록 (CSV 파일에서 가져오기)
$csv = Reader::createFromPath('/path/to/recipients.csv', 'r');
$csv->setHeaderOffset(0);
$recipients = [];
foreach ($csv as $record) {
    $recipients[] = [
        'email' => $record['email'],
        'name' => $record['name'],
        'custom_data' => [
            'company' => $record['company'],
            'region' => $record['region']
        ]
    ];
}

// 일괄 발송 (청크 단위로 처리)
$emailService->sendBulk('newsletter_template', $recipients, [
    'campaign_name' => '2025년 1월 뉴스레터',
    'chunk_size' => 100,  // 100명씩 처리
    'delay' => 5  // 청크 간 5초 대기
]);

// 발송 진행 상황 모니터링
$progress = $emailService->getBulkProgress($campaignId);
echo "진행률: {$progress['percentage']}%";
echo "성공: {$progress['success']}, 실패: {$progress['failed']}";
```

### 2. 예약 발송

```php
use Jiny\Admin\App\Jobs\SendEmailJob;
use Carbon\Carbon;

// 10분 후 발송
SendEmailJob::dispatch($templateId, $recipient, $data)
    ->delay(now()->addMinutes(10));

// 특정 시간에 발송 (타임존 고려)
SendEmailJob::dispatch($templateId, $recipient, $data)
    ->delay(Carbon::parse('2025-01-20 09:00:00', 'Asia/Seoul'));

// 반복 발송 (매주 월요일 오전 9시)
Schedule::call(function() use ($templateId, $recipients) {
    foreach ($recipients as $recipient) {
        SendEmailJob::dispatch($templateId, $recipient['email'], $recipient['data']);
    }
})->weekly()->mondays()->at('09:00');

// 조건부 예약 발송
if ($user->subscription_expires_at->diffInDays(now()) == 7) {
    // 구독 만료 7일 전 알림
    SendEmailJob::dispatch('subscription_reminder', $user->email, [
        'expiry_date' => $user->subscription_expires_at->format('Y-m-d')
    ])->delay(now()->addDay());
}
```

### 3. 큐를 통한 비동기 발송

```php
// 큐에 추가
SendEmailJob::dispatch($templateId, $recipient, $data)
    ->onQueue('emails');

// 우선순위 설정
SendEmailJob::dispatch($templateId, $recipient, $data)
    ->onQueue('high-priority');
```

### 4. 조건부 발송

```php
// 이벤트 기반 발송
Event::listen('user.registered', function($user) {
    $emailService->sendWithTemplate('welcome_email', $user->email, [
        'user_name' => $user->name
    ]);
});

// 조건 확인 후 발송
if ($order->status === 'completed') {
    $emailService->sendWithTemplate('order_completed', $order->email, [
        'order_id' => $order->id,
        'total' => $order->total
    ]);
}
```

### 5. 첨부파일 포함 발송

```php
// 단일 첨부파일
$emailService->sendWithAttachment(
    'invoice_email',
    'customer@example.com',
    ['invoice_number' => 'INV-2025-001'],
    [
        'path' => storage_path('invoices/invoice.pdf'),
        'name' => 'Invoice.pdf',
        'mime' => 'application/pdf'
    ]
);

// 다중 첨부파일
$attachments = [
    [
        'path' => storage_path('invoices/invoice.pdf'),
        'name' => 'Invoice.pdf',
        'mime' => 'application/pdf'
    ],
    [
        'path' => storage_path('reports/monthly.xlsx'),
        'name' => 'Monthly_Report.xlsx',
        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ]
];

$emailService->sendWithAttachments(
    'monthly_report',
    'manager@example.com',
    ['month' => 'January 2025'],
    $attachments
);

// 인라인 이미지 포함
$emailService->sendWithInlineImages(
    'product_showcase',
    'customer@example.com',
    [
        'product_name' => 'New Product',
        'product_image' => 'cid:product_image_1'  // 본문에서 참조
    ],
    [
        'product_image_1' => storage_path('images/product.jpg')
    ]
);
```

---

## 발송 로그 관리

### 1. 로그 조회

#### 관리자 패널
- **URL**: http://localhost:8000/admin/mail/logs
- **메뉴**: 메일 관리 > 발송 기록

#### 로그 정보
```
┌─────────────────────────────────────────────┐
│              발송 로그 상세 정보              │
├─────────────────────────────────────────────┤
│ ID: 1234                                    │
│ 수신자: user@example.com                    │
│ 발신자: noreply@example.com                 │
│ 제목: 환영합니다!                           │
│ 템플릿: welcome_email                       │
│ 상태: sent (발송완료)                       │
│ 발송시간: 2025-01-13 10:30:00              │
│ 열람시간: 2025-01-13 10:35:00              │
│ 열람횟수: 5회                               │
│ 클릭: 3회                                   │
│ 클릭 링크: [구매하기] [자세히보기]           │
│ IP: 192.168.1.1                            │
│ 위치: Seoul, KR                            │
│ User Agent: Chrome/120.0                    │
│ 디바이스: Desktop                           │
│ OS: Windows 11                             │
│ 이메일 클라이언트: Gmail                     │
│ 발송 시도: 1회                              │
│ 오류 메시지: -                              │
│ 첨부파일: Invoice.pdf (124KB)               │
│ 메타데이터: {"campaign_id": "2025-01"}      │
└─────────────────────────────────────────────┘
```

### 2. 상태 관리

#### 상태 종류
| 상태 | 설명 | 액션 가능 |
|-----|------|----------|
| `pending` | 발송 대기 | 취소, 즉시발송 |
| `processing` | 발송 중 | - |
| `sent` | 발송 완료 | 재발송 |
| `failed` | 발송 실패 | 재발송, 디버그 |
| `bounced` | 반송됨 | 재발송, 이메일 검증 |
| `opened` | 열람됨 | - |
| `clicked` | 링크 클릭됨 | - |

#### 상태 전환 플로우
```
pending → processing → sent → opened → clicked
                   ↓
                failed → retry → sent
                   ↓
                bounced
```

### 3. 필터링 및 검색

#### 필터 옵션
- **날짜 범위**: 시작일 ~ 종료일
- **상태**: 전체, 성공, 실패, 대기중
- **템플릿**: 특정 템플릿 선택
- **수신자**: 이메일 주소 검색

#### 검색 예시
```sql
-- 최근 7일간 실패한 이메일
WHERE status = 'failed' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

-- 특정 템플릿의 열람률
SELECT COUNT(*) as total,
       SUM(CASE WHEN status = 'opened' THEN 1 ELSE 0 END) as opened
FROM admin_email_logs
WHERE template_id = 5
```

### 4. 재발송 관리

#### 수동 재발송
1. 발송 로그에서 실패한 이메일 선택
2. "재발송" 버튼 클릭
3. 재발송 확인

#### 자동 재발송 설정
```php
// config/mail.php
'retry' => [
    'enabled' => true,
    'max_attempts' => 3,
    'delay' => [5, 15, 60], // 분 단위
    'conditions' => [
        'status' => ['failed', 'bounced'],
        'exclude_errors' => ['invalid_email', 'unsubscribed']
    ]
]
```

#### 일괄 재발송
```php
// 실패한 이메일 일괄 재발송
$failedLogs = AdminEmailLog::where('status', 'failed')
    ->where('retry_count', '<', 3)
    ->where('created_at', '>', now()->subDay())
    ->get();

foreach ($failedLogs as $log) {
    RetryEmailJob::dispatch($log->id)
        ->delay(now()->addMinutes(5));
}
```

### 5. 통계 및 분석

#### 대시보드 메트릭

##### 실시간 메트릭 (오늘)
- **발송 성공률**: (성공 / 전체) × 100
- **열람률**: (열람 / 발송) × 100
- **클릭률**: (클릭 / 열람) × 100  
- **반송률**: (반송 / 발송) × 100
- **수신거부율**: (수신거부 / 발송) × 100

##### 시간대별 분석
```php
// 시간대별 발송 성과
$hourlyStats = AdminEmailLog::selectRaw('
    HOUR(created_at) as hour,
    COUNT(*) as total,
    AVG(CASE WHEN status = "opened" THEN 1 ELSE 0 END) * 100 as open_rate
')
->whereDate('created_at', today())
->groupBy('hour')
->get();

// 최적 발송 시간 찾기
$bestHour = $hourlyStats->sortByDesc('open_rate')->first();
echo "최적 발송 시간: {$bestHour->hour}시";
```

##### 템플릿별 성과
```php
// 템플릿별 성과 비교
$templateStats = AdminEmailTemplate::withCount([
    'logs',
    'logs as opened_count' => function($query) {
        $query->where('status', 'opened');
    },
    'logs as clicked_count' => function($query) {
        $query->where('status', 'clicked');
    }
])->get()->map(function($template) {
    return [
        'name' => $template->name,
        'sent' => $template->logs_count,
        'open_rate' => $template->logs_count ? 
            ($template->opened_count / $template->logs_count * 100) : 0,
        'click_rate' => $template->opened_count ? 
            ($template->clicked_count / $template->opened_count * 100) : 0
    ];
});
```

#### 리포트 생성
```php
$stats = AdminEmailLog::selectRaw('
    COUNT(*) as total,
    SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
    SUM(CASE WHEN status = "opened" THEN 1 ELSE 0 END) as opened,
    SUM(CASE WHEN status = "clicked" THEN 1 ELSE 0 END) as clicked,
    SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
')
->whereBetween('created_at', [now()->subDays(30), now()])
->first();
```

### 6. 로그 정리

#### 자동 정리 설정
```php
// 30일 이상 된 로그 자동 삭제
Schedule::command('email:cleanup-logs')
    ->daily()
    ->at('02:00');
```

#### 수동 정리
```bash
# Artisan 명령어
php artisan email:cleanup-logs --days=30

# 특정 상태만 정리
php artisan email:cleanup-logs --status=sent --days=90
```

---

## 고급 기능

### 이벤트 기반 자동 발송

#### 이벤트 정의
```php
// config/mail_events.php
return [
    'events' => [
        // 사용자 이벤트
        'user.registered' => [
            'template' => 'welcome_email',
            'delay' => 0,  // 즉시 발송
            'condition' => null
        ],
        'user.verified' => [
            'template' => 'verification_success',
            'delay' => 0
        ],
        'user.password_changed' => [
            'template' => 'password_changed_notification',
            'delay' => 0
        ],
        
        // 주문 이벤트
        'order.placed' => [
            'template' => 'order_confirmation',
            'delay' => 0
        ],
        'order.shipped' => [
            'template' => 'shipping_notification',
            'delay' => 0
        ],
        'order.delivered' => [
            'template' => 'delivery_confirmation',
            'delay' => 60,  // 1시간 후 발송
            'condition' => function($order) {
                return $order->total >= 50000;  // 5만원 이상 주문만
            }
        ],
        
        // 구독 이벤트
        'subscription.expiring' => [
            'template' => 'subscription_reminder',
            'delay' => 0,
            'days_before' => [7, 3, 1]  // 7일, 3일, 1일 전 알림
        ],
        'subscription.expired' => [
            'template' => 'subscription_expired',
            'delay' => 0
        ]
    ]
];

// 자동 이벤트 리스너 등록
foreach (config('mail_events.events') as $event => $config) {
    Event::listen($event, function($data) use ($config) {
        if ($config['condition'] && !$config['condition']($data)) {
            return;
        }
        
        SendEmailJob::dispatch(
            $config['template'],
            $data->email ?? $data->user->email,
            $data->toArray()
        )->delay($config['delay'] ?? 0);
    });
}
```

#### Hook 시스템
```php
// 발송 전 Hook
EmailService::beforeSend(function($data) {
    // 블랙리스트 체크
    if (Blacklist::contains($data['email'])) {
        return false; // 발송 취소
    }
    return true;
});

// 발송 후 Hook
EmailService::afterSend(function($result, $data) {
    // 통계 업데이트
    Statistics::increment('emails_sent');
    
    // Slack 알림
    if ($data['priority'] === 'high') {
        Slack::notify("중요 메일 발송: {$data['subject']}");
    }
});
```

### A/B 테스팅

```php
use Jiny\Admin\App\Services\ABTestService;

// A/B 테스트 생성 (관리자 패널에서도 가능)
$abTest = ABTestService::create([
    'name' => '제목 테스트 - 이모지 vs 일반',
    'hypothesis' => '제목에 이모지를 넣으면 열람률이 10% 상승할 것이다',
    'variants' => [
        'A' => [
            'template' => 'welcome_v1',
            'subject' => '환영합니다! 가입을 축하드립니다',
            'description' => '일반 텍스트 제목'
        ],
        'B' => [
            'template' => 'welcome_v2', 
            'subject' => '🎉 환영합니다! 가입을 축하드립니다 🎊',
            'description' => '이모지 포함 제목'
        ]
    ],
    'sample_size' => 1000,  // 각 변형당 500명
    'metric' => 'open_rate',  // 측정 지표
    'confidence_level' => 95,  // 신뢰수준
    'minimum_detectable_effect' => 5  // 최소 감지 효과 (5%)
]);

// 테스트 실행
$abTest->run($recipients, [
    'split_method' => 'random',  // random, sequential, hash
    'duration' => 48  // 48시간 동안 테스트
]);

// 실시간 모니터링
$stats = $abTest->getRealtimeStats();
foreach ($stats['variants'] as $variant => $data) {
    echo "변형 {$variant}:\n";
    echo "  발송: {$data['sent']}\n";
    echo "  열람률: {$data['open_rate']}%\n";
    echo "  클릭률: {$data['click_rate']}%\n";
    echo "  통계적 유의성: " . ($data['significant'] ? '있음' : '없음') . "\n";
}

// 승자 결정 (자동)
if ($abTest->isComplete()) {
    $winner = $abTest->determineWinner();
    echo "승자: 변형 {$winner['variant']}\n";
    echo "개선율: {$winner['improvement']}%\n";
    echo "신뢰구간: [{$winner['ci_lower']}%, {$winner['ci_upper']}%]\n";
    
    // 승자 템플릿을 기본으로 설정
    $abTest->applyWinner();
}

// 상세 리포트 생성
$report = $abTest->generateReport();
// PDF 또는 Excel로 내보내기
$abTest->exportReport('pdf', storage_path('reports/ab_test_001.pdf'));
```

### 멀티채널 통합

```php
use Jiny\Admin\App\Services\MultiChannelNotificationService;

$notification = new MultiChannelNotificationService();

// 채널별 우선순위 설정
$notification->setPriority([
    'urgent' => ['sms', 'push', 'email'],  // 긴급: SMS 우선
    'normal' => ['email', 'push'],  // 일반: 이메일 우선
    'promotional' => ['email']  // 프로모션: 이메일만
]);

// 이메일 + SMS + 푸시 동시 발송
$result = $notification->send('order_completed', $user, [
    'channels' => ['email', 'sms', 'push'],
    'priority' => 'urgent',
    'data' => [
        'order_id' => $order->id,
        'amount' => number_format($order->total),
        'delivery_date' => $order->delivery_date->format('m월 d일')
    ],
    // 채널별 커스터마이징
    'channel_config' => [
        'email' => [
            'template' => 'order_completed_detailed',
            'attachments' => ['invoice.pdf']
        ],
        'sms' => [
            'template' => 'order_completed_short',
            'sender' => '1588-0000'
        ],
        'push' => [
            'title' => '주문 완료',
            'body' => '주문번호 ' . $order->id . ' 처리완료',
            'icon' => 'order_success',
            'action' => 'app://orders/' . $order->id
        ]
    ]
]);

// 채널별 발송 결과 확인
foreach ($result['channels'] as $channel => $status) {
    echo "{$channel}: {$status['status']} - {$status['message']}\n";
}

// 사용자 선호 채널 기반 발송
$userPreferences = $user->notification_preferences;
$notification->sendToPreferredChannels('weekly_newsletter', $user, $data, $userPreferences);

// 폴백 채널 설정 (실패 시 다음 채널로)
$notification->withFallback([
    'primary' => 'push',
    'fallback1' => 'sms',
    'fallback2' => 'email'
])->send('urgent_alert', $user, $data);
```

---

## API 레퍼런스

### EmailService

```php
class EmailService {
    // 템플릿으로 발송
    public function sendWithTemplate(
        string $templateSlug,
        string $recipient,
        array $data = []
    ): array;
    
    // 직접 발송
    public function send(
        string $to,
        string $subject,
        string $body,
        string $type = 'html'
    ): array;
    
    // 대량 발송
    public function sendBulk(
        string $templateSlug,
        array $recipients,
        array $commonData = []
    ): array;
    
    // 연결 테스트
    public function testConnection(): array;
    
    // 템플릿 미리보기
    public function preview(
        string $templateSlug,
        array $data = []
    ): string;
}
```

### EmailTemplate 모델

```php
class AdminEmailTemplate extends Model {
    // 관계
    public function logs();
    
    // 스코프
    public function scopeActive($query);
    public function scopeByCategory($query, $category);
    
    // 메서드
    public function render(array $data = []): array;
    public static function findBySlug(string $slug);
}
```

### EmailLog 모델

```php
class AdminEmailLog extends Model {
    // 관계
    public function template();
    public function user();
    
    // 스코프
    public function scopeFailed($query);
    public function scopeSent($query);
    public function scopePending($query);
    
    // 메서드
    public function canResend(): bool;
    public function markAsSent(): void;
    public function markAsFailed(string $error = null): void;
    public function markAsOpened(): void;
    public function markAsClicked(string $url = null): void;
}
```

---

## 문제 해결

### 일반적인 문제와 해결방법

#### 1. 이메일이 발송되지 않음
1. **SMTP 설정 확인**
   ```bash
   php artisan tinker
   >>> Mail::raw('Test', function($m) { 
       $m->to('test@example.com')->subject('Test'); 
   });
   ```

2. **큐 워커 실행 확인**
   ```bash
   ps aux | grep queue:work
   php artisan queue:work
   ```

3. **로그 확인**
   ```bash
   tail -f storage/logs/laravel.log
   ```

#### 2. 템플릿 변수가 치환되지 않음

**확인사항:**
- 변수명 대소문자 확인 (case-sensitive)
- 데이터 타입 확인 (scalar 타입만 지원)
- 템플릿 문법 확인 (`{{variable}}`)
- 중괄호 사이 공백 확인 (`{{ variable }}` 아님)

**디버깅:**
```php
// 템플릿 렌더링 테스트
$template = AdminEmailTemplate::find(1);
$data = ['user_name' => 'John Doe'];
$rendered = $template->render($data);
dd($rendered);  // 렌더링 결과 확인

// 변수 목록 확인
dd($template->variables);  // 정의된 변수 목록
```

#### 3. 메일이 스팸으로 분류됨

**DNS 설정:**
```
# SPF 레코드
TXT  @  "v=spf1 include:_spf.google.com include:amazonses.com ~all"

# DKIM (AWS SES 예시)
CNAME  example._domainkey  example.dkim.amazonses.com

# DMARC
TXT  _dmarc  "v=DMARC1; p=quarantine; rua=mailto:dmarc@example.com"
```

**콘텐츠 최적화:**
- 이미지/텍스트 비율 유지 (40:60)
- 스팸 트리거 단어 회피 (무료, 보장, 클릭 등)
- 수신거부 링크 필수 포함
- 실제 발신자 주소 사용

**평판 관리:**
```php
// 반송률 모니터링
$bounceRate = AdminEmailLog::where('status', 'bounced')
    ->where('created_at', '>', now()->subDays(7))
    ->count() / AdminEmailLog::count() * 100;

if ($bounceRate > 5) {
    // 경고 알림 발송
    Alert::warning("높은 반송률 감지: {$bounceRate}%");
}
```

#### 4. 대량 발송 시 속도 문제

**큐 최적화:**
```bash
# Redis 큐 설정 (.env)
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis

# 워커 프로세스 실행 (Supervisor 설정)
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8  # 8개 워커 프로세스
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

**발송 속도 제어:**
```php
// Rate Limiting 적용
RateLimiter::for('emails', function (Request $request) {
    return Limit::perMinute(100);  // 분당 100개
});

// 청크 단위 처리
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        SendEmailJob::dispatch($user)
            ->onQueue('bulk-email');
    }
    sleep(1);  // 청크 간 대기
});

// 배치 처리
Bus::batch($jobs)
    ->then(function (Batch $batch) {
        // 완료 시 처리
    })
    ->catch(function (Batch $batch, Throwable $e) {
        // 실패 시 처리  
    })
    ->finally(function (Batch $batch) {
        // 항상 실행
    })
    ->dispatch();
```

### 디버깅 도구 및 팁

```php
// 메일 발송 디버그
Mail::pretend(); // 실제 발송하지 않고 로그만 기록

// 상세 로그
\Log::channel('mail')->info('Email sent', [
    'to' => $recipient,
    'template' => $template,
    'data' => $data
]);

// SQL 쿼리 로그
\DB::enableQueryLog();
// ... 작업 수행
dd(\DB::getQueryLog());
```

---

## 보안 고려사항

### 1. 이메일 주소 검증
```php
use Jiny\Admin\App\Services\EmailValidationService;

$validator = new EmailValidationService();

// 기본 검증
if (!$validator->isValid($email)) {
    throw new InvalidEmailException("Invalid email: {$email}");
}

// 고급 검증 (DNS, MX 레코드 확인)
$validation = $validator->validate($email, [
    'checkDNS' => true,
    'checkMX' => true,
    'checkDisposable' => true,  // 일회용 이메일 차단
    'checkRole' => true  // role 계정 차단 (admin@, info@ 등)
]);

if (!$validation['valid']) {
    Log::warning("Email validation failed", $validation);
}
```

### 2. Rate Limiting
```php
// 사용자별 제한
RateLimiter::for('user-emails', function ($request) {
    return [
        Limit::perMinute(5)->by($request->user()->id),
        Limit::perDay(50)->by($request->user()->id)
    ];
});

// IP별 제한
RateLimiter::for('ip-emails', function ($request) {
    return Limit::perMinute(10)->by($request->ip());
});
```

### 3. 템플릿 인젝션 방지
```php
// 자동 이스케이프
$data = [
    'user_input' => e($request->input('message')),  // HTML 이스케이프
    'user_name' => strip_tags($request->input('name'))  // 태그 제거
];

// CSP 헤더 설정
header("Content-Security-Policy: default-src 'self'");
```

### 4. 발신자 인증
```bash
# SPF 레코드
TXT @ "v=spf1 include:_spf.google.com ~all"

# DKIM 설정 (Laravel)
MAIL_DKIM_DOMAIN=example.com
MAIL_DKIM_PRIVATE_KEY=/path/to/private.key
MAIL_DKIM_SELECTOR=default

# DMARC 정책
TXT _dmarc "v=DMARC1; p=reject; rua=mailto:dmarc@example.com"
```

### 5. 로그 암호화
```php
// 민감한 정보 암호화
AdminEmailLog::create([
    'to_email' => Crypt::encryptString($email),
    'subject' => $subject,
    'body' => Crypt::encryptString($body),
    'metadata' => encrypt($metadata)
]);

// 복호화
$email = Crypt::decryptString($log->to_email);
```

### 6. 추가 보안 조치
- **2FA 인증**: 관리자 패널 접근 시
- **감사 로그**: 모든 이메일 관련 작업 기록
- **백업**: 템플릿 및 로그 정기 백업
- **접근 제어**: 역할 기반 권한 관리

---

## 성능 최적화 가이드

1. **캐싱 활용**
   ```php
   Cache::remember('template:'.$slug, 3600, function() use ($slug) {
       return AdminEmailTemplate::where('slug', $slug)->first();
   });
   ```

2. **청크 처리**
   ```php
   User::chunk(100, function($users) {
       // 100명씩 처리
   });
   ```

3. **인덱스 최적화**
   ```sql
   CREATE INDEX idx_email_logs_status ON admin_email_logs(status);
   CREATE INDEX idx_email_logs_created ON admin_email_logs(created_at);
   ```

---

## 참고 자료

### 관련 파일
- 서비스: `jiny/admin/App/Services/EmailService.php`
- 모델: `jiny/admin/App/Models/AdminEmailTemplate.php`
- 컨트롤러: `jiny/admin/App/Http/Controllers/Admin/AdminEmailtemplates/`
- 마이그레이션: `jiny/admin/database/migrations/*email*.php`
- 설정: `config/mail.php`, `config/mail_events.php`
- 뷰: `jiny/admin/resources/views/admin/emailtemplates/`

### CLI 명령어
```bash
# 이메일 관련 Artisan 명령어
php artisan email:test {email}  # 테스트 이메일 발송
php artisan email:cleanup-logs --days=30  # 로그 정리
php artisan email:stats --period=week  # 통계 보기
php artisan email:validate-templates  # 템플릿 검증
php artisan email:export-templates  # 템플릿 내보내기
php artisan email:import-templates {file}  # 템플릿 가져오기
```

### 외부 문서
- [Laravel Mail 문서](https://laravel.com/docs/mail)
- [이메일 전송 모범 사례](https://sendgrid.com/resource/email-deliverability/)
- [이메일 디자인 가이드](https://www.campaignmonitor.com/dev-resources/)
- [MJML - 반응형 이메일 프레임워크](https://mjml.io/)
- [Can I Email - 이메일 클라이언트 호환성](https://www.caniemail.com/)

### 유용한 도구
- [Mail Tester](https://www.mail-tester.com/) - 스팸 점수 테스트
- [Litmus](https://litmus.com/) - 이메일 클라이언트 테스트
- [Mailtrap](https://mailtrap.io/) - 개발 환경 이메일 테스트
- [SendGrid](https://sendgrid.com/) - 이메일 전송 서비스
- [Postmark](https://postmarkapp.com/) - 트랜잭션 이메일 서비스

---

## 부록: 자주 사용하는 템플릿 예시

### 회원가입 환영 이메일
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{app_name}}에 오신 것을 환영합니다</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #333;">환영합니다, {{user_name}}님! 🎉</h1>
        <p>{{app_name}}의 회원이 되신 것을 진심으로 환영합니다.</p>
        <a href="{{activation_link}}" style="display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">계정 활성화</a>
    </div>
</body>
</html>
```

### 비밀번호 재설정
```html
<h2>비밀번호 재설정 요청</h2>
<p>{{user_name}}님, 비밀번호 재설정을 요청하셨습니다.</p>
<p>아래 버튼을 클릭하여 새 비밀번호를 설정하세요:</p>
<a href="{{reset_link}}">비밀번호 재설정</a>
<p>이 링크는 {{expiry_hours}}시간 후 만료됩니다.</p>
<p>본인이 요청하지 않으셨다면 이 이메일을 무시하세요.</p>
```

### 주문 확인
```html
<h2>주문이 확인되었습니다</h2>
<p>주문번호: #{{order_id}}</p>
<table>
    {{#each items}}
    <tr>
        <td>{{name}}</td>
        <td>{{quantity}}개</td>
        <td>{{price}}원</td>
    </tr>
    {{/each}}
</table>
<p><strong>총액: {{total_price}}원</strong></p>
<p>배송 예정일: {{delivery_date}}</p>
```

---

*마지막 업데이트: 2025년 9월 13일*
*문서 버전: 2.0.0*
*작성자: Jiny Admin Team*
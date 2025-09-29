
## 개요

Jiny Admin은 Laravel Livewire 컴포넌트와 컨트롤러 간의 유연한 상호작용을 위해 Hook 시스템을 제공합니다. Hook을 통해 CRUD 작업의 각 단계에서 커스텀 로직을 실행할 수 있습니다.

## ⚠️ 중요: Livewire 메소드명 통일

### 메소드명 규칙
Jiny Admin 시스템에서는 일관성을 위해 다음과 같은 명명 규칙을 사용합니다:
- **통일된 메소드명**: `hookCustom` (소문자로 시작하는 camelCase)
- **Hook 메소드 패턴**: `hookCustom{ActionName}()` 형식 사용

### 이전 문제점 해결
- ~~`HookCustom`과 같은 대문자로 시작하는 메소드명~~ → `hookCustom` 사용
- 중복 선언 방지를 위해 각 Livewire 컴포넌트에서 `hookCustom` 메소드를 한 번만 정의

### 권장 사항
- 커스텀 Hook 호출 시 `hookCustom` 메소드 사용
- 컨트롤러에서 `hookCustom{ActionName}()` 패턴으로 구현
- Blade 템플릿에서 `wire:click="hookCustom('actionName', params)"` 형식 사용

## Hook의 종류

Jiny Admin의 Hook 시스템은 크게 3가지 종류로 분류됩니다:

### 1. 라이프사이클 Hook (Lifecycle Hooks)
CRUD 작업에서 데이터를 처리하는 특정 시점에 자동으로 호출되는 Hook입니다.

- **특징**: 컴포넌트의 생명주기와 연동되어 자동 호출
- **명명 규칙**: `hook{Action}()` (예: `hookStoring`, `hookUpdating`)
- **호출 방식**: 시스템에 의해 자동 호출
- **용도**: 데이터 검증, 변환, 전처리/후처리 작업

### 2. 폼 이벤트 Hook (Form Event Hooks)
컴포넌트에서 폼 필드의 값이 변경될 때 Livewire의 `updated` 이벤트와 연동되어 호출되는 Hook입니다.

- **특징**: 실시간 폼 유효성 검사 및 동적 처리
- **명명 규칙**: `hookForm{FieldName}()` (예: `hookFormEmail`, `hookFormPassword`)
- **호출 방식**: 필드 값 변경 시 자동 호출 (wire:model)
- **용도**: 실시간 유효성 검사, 연관 필드 자동 업데이트, 동적 UI 변경

### 3. 커스텀 Hook (Custom Hooks)
특정 도메인 로직이나 비즈니스 요구사항을 처리하기 위해 명시적으로 호출하는 Hook입니다.

- **특징**: 개발자가 정의한 특정 액션 수행
- **명명 규칙**: `hookCustom{ActionName}()` (예: `hookCustomActivate`, `hookCustomSendEmail`)
- **호출 방식**: 템플릿이나 컴포넌트에서 명시적으로 호출
- **용도**: 도메인 특화 기능, 일괄 처리, 외부 API 연동 등

## Hook 시스템 아키텍처

### 1. 컨트롤러 등록 방식

Hook을 사용하기 위해서는 **반드시 jsonData에 컨트롤러 정보를 설정**해야 합니다. 다음 두 가지 방법으로 등록할 수 있습니다:

#### 방법 1: JSON 설정 파일에 등록 (권장) ⭐

각 Admin 모듈의 JSON 설정 파일에 `controllerClass`를 추가합니다:

```json
// 예: jiny/admin/App/Http/Controllers/Admin/AdminUsers/AdminUsers.json
{
    "controllerClass": "\\Jiny\\Admin\\App\\Http\\Controllers\\Admin\\AdminUsers\\AdminUsers",
    "table": {
        "name": "users"
    },
    // ... 기타 설정
}
```

**중요**: 컨트롤러 클래스 경로는 반드시 전체 네임스페이스를 포함해야 합니다.

#### 방법 2: 컨트롤러에서 직접 전달

컨트롤러의 각 메소드(index, create, edit 등)에서 jsonData를 준비할 때 추가:

```php
// AdminUsers 컨트롤러의 index 메소드
public function index()
{
    // JSON 파일 로드
    $this->jsonData = $this->loadJsonData();
    
    // 컨트롤러 클래스 추가 (중요!)
    $this->jsonData['controllerClass'] = self::class;
    
    // Livewire 컴포넌트에 전달
    return view('jiny-admin::crud.index', [
        'jsonData' => $this->jsonData
    ]);
}

// create 메소드
public function create()
{
    $this->jsonData = $this->loadJsonData();
    $this->jsonData['controllerClass'] = self::class;  // 반드시 추가!
    
    return view('jiny-admin::crud.create', [
        'jsonData' => $this->jsonData
    ]);
}

// edit 메소드
public function edit($id)
{
    $this->jsonData = $this->loadJsonData();
    $this->jsonData['controllerClass'] = self::class;  // 반드시 추가!
    
    // 데이터 조회
    $data = DB::table($this->jsonData['table']['name'])
        ->where('id', $id)
        ->first();
    
    return view('jiny-admin::crud.edit', [
        'jsonData' => $this->jsonData,
        'id' => $id,
        'data' => (array)$data
    ]);
}
```

### 2. 컨트롤러 설정 확인 방법

Hook이 제대로 동작하지 않을 때 다음을 확인하세요:

1. **Laravel 로그 확인**
```bash
tail -f storage/logs/laravel.log
```

2. **로그 메시지 확인**
- 성공: `AdminCreate: Controller loaded successfully`
- 실패: `AdminCreate: Controller class not found`
- 미설정: `AdminCreate: No controller class specified in JSON data`

### 3. 컨트롤러 설정 프로세스

```php
public function mount($jsonData = null, ..., $controllerClass = null)
{
    $this->jsonData = $jsonData;
    
    // 컨트롤러 클래스 설정
    if ($controllerClass) {
        $this->controllerClass = $controllerClass;
        $this->setupController();
    } elseif (isset($this->jsonData['controllerClass'])) {
        $this->controllerClass = $this->jsonData['controllerClass'];
        $this->setupController();
    }
}

protected function setupController()
{
    if ($this->controllerClass && class_exists($this->controllerClass)) {
        $this->controller = new $this->controllerClass();
    }
}
```

## Hook 호출 시점 및 흐름

### 라이프사이클 Hook 호출 흐름

```
[목록 표시 - AdminTable]
mount() → setupController() → hookIndexing() → 데이터 조회 → hookIndexed() → render()

[데이터 생성 - AdminCreate]
mount() → setupController() → hookCreating() → 폼 표시 → save() → hookStoring() → DB 저장 → hookStored()

[데이터 수정 - AdminEdit]
mount() → setupController() → hookEditing() → 폼 표시 → save() → hookUpdating() → DB 업데이트 → hookUpdated()

[데이터 상세 - AdminShow]
mount() → setupController() → hookShowing() → render()

[데이터 삭제 - AdminDelete]
mount() → setupController() → executeDelete() → hookDeleting() → DB 삭제 → hookDeleted()
```

### 폼 이벤트 Hook 호출 흐름

```
사용자 입력 → wire:model → Livewire updated() → hookForm{FieldName}() → 유효성 검사/처리
```

### 커스텀 Hook 호출 흐름

```
사용자 액션 (클릭/이벤트) → wire:click="hookCustom('actionName', params)" → hookCustom{ActionName}() → 비즈니스 로직 처리
```

## 컴포넌트별 Hook 메소드 상세

### AdminTable (목록 표시)

#### 라이프사이클 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookIndexing()` | Lifecycle | 데이터 조회 전 | 쿼리 조건 수정, 필터 설정 | `$livewire` | - |
| `hookIndexed()` | Lifecycle | 데이터 조회 후 | 조회된 데이터 가공 | `$livewire, $rows` | `$rows` (가공된 데이터) |

#### 커스텀 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookTerminateSession()` | Custom | 세션 종료 버튼 클릭 | 세션 종료 로직 커스터마이징 | `$livewire, $id` | - |
| `hookRegenerateSession()` | Custom | 세션 재발급 버튼 클릭 | 세션 재생성 로직 커스터마이징 | `$livewire, $id` | - |
| `hookCustom{Name}()` | Custom | 명시적 호출 | 사용자 정의 액션 | `$livewire, $params` | - |

### AdminCreate (데이터 생성)

#### 라이프사이클 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookCreating()` | Lifecycle | mount() 시 | 폼 기본값 설정 | `$livewire, $form` | `array` (수정된 폼) or `null` |
| `hookStoring()` | Lifecycle | save() 호출, DB 저장 전 | 데이터 유효성 검사 및 수정 | `$livewire, $insertData` | `array` (성공), `string` (에러), `false` (취소) |
| `hookStored()` | Lifecycle | DB 저장 후 | 추가 작업 수행 (이메일 발송 등) | `$livewire, $savedData` | - |

#### 폼 이벤트 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookForm{Field}()` | Form Event | 필드 값 변경 시 | 실시간 유효성 검사 | `$livewire, $value, $fieldName` | `false` (거부) or 기타 (허용) |

#### 커스텀 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookCustom{Name}()` | Custom | 명시적 호출 | 사용자 정의 액션 | `$livewire, $params` | - |

### AdminEdit (데이터 수정)

#### 라이프사이클 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookEditing()` | Lifecycle | mount() 시 | 데이터 표시 전 가공 | `$livewire, $form` | `array` (수정된 폼) or `null` |
| `hookUpdating()` | Lifecycle | save() 호출, DB 업데이트 전 | 데이터 유효성 검사 및 수정 | `$livewire, $updateData` | `array` (성공), `string` (에러), `false` (취소) |
| `hookUpdated()` | Lifecycle | DB 업데이트 후 | 추가 작업 수행 | `$livewire, $updatedData` | - |

#### 폼 이벤트 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookForm{Field}()` | Form Event | 필드 값 변경 시 | 실시간 유효성 검사 | `$livewire, $value, $fieldName` | `false` (거부) or 기타 (허용) |

#### 커스텀 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookCustom{Name}()` | Custom | 명시적 호출 | 사용자 정의 액션 | `$livewire, $params` | - |

### AdminShow (데이터 상세보기)

#### 라이프사이클 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookShowing()` | Lifecycle | mount() 시 | 표시할 데이터 가공 | `$livewire, $data` | `array` (수정된 데이터) or `null` |

#### 커스텀 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookCustom{Name}()` | Custom | 명시적 호출 | 사용자 정의 액션 | `$livewire, $params` | - |

### AdminDelete (데이터 삭제)

#### 라이프사이클 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookDeleting()` | Lifecycle | executeDelete() 호출, DB 삭제 전 | 삭제 가능 여부 확인 | `$livewire, $ids, $type` | `false` (취소), `string` (에러) or 기타 (진행) |
| `hookDeleted()` | Lifecycle | DB 삭제 후 | 추가 정리 작업 (파일 삭제 등) | `$livewire, $ids, $deletedCount` | - |

#### 커스텀 Hook
| Hook 메소드 | Hook 종류 | 호출 시점 | 용도 | 파라미터 | 반환값 |
|------------|-----------|----------|------|----------|--------|
| `hookCustom{Name}()` | Custom | 명시적 호출 | 사용자 정의 액션 | `$livewire, $params` | - |

## Hook 구현 예제

### 1. 라이프사이클 Hook 구현

```php
namespace Jiny\Admin\App\Http\Controllers\Admin\AdminUsers;

class AdminUsers
{
    /**
     * 데이터 저장 전 처리
     */
    public function hookStoring($livewire, $data)
    {
        // 비밀번호 암호화
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        
        // 타임스탬프 추가
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        return $data; // 수정된 데이터 반환
    }
    
    /**
     * 유효성 검사 실패 시
     */
    public function hookUpdating($livewire, $data)
    {
        // 이메일 중복 체크
        if ($this->emailExists($data['email'])) {
            return "이미 사용중인 이메일입니다."; // 에러 메시지 반환
        }
        
        return $data;
    }
}
```

### 2. 폼 이벤트 Hook 구현

```php
class AdminUsers
{
    /**
     * 이메일 필드 변경 시
     */
    public function hookFormEmail($livewire, $value, $fieldName)
    {
        // 실시간 이메일 유효성 검사
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $livewire->addError('form.email', '유효한 이메일 형식이 아닙니다.');
            return false;
        }
        
        // 중복 체크
        if ($this->emailExists($value)) {
            $livewire->addError('form.email', '이미 사용중인 이메일입니다.');
            return false;
        }
    }
    
    /**
     * 비밀번호 확인 필드 변경 시
     */
    public function hookFormPasswordConfirmation($livewire, $value, $fieldName)
    {
        if ($livewire->form['password'] !== $value) {
            $livewire->addError('form.password_confirmation', '비밀번호가 일치하지 않습니다.');
            return false;
        }
    }
}
```

### 3. 커스텀 Hook 구현

```php
class AdminUsers
{
    /**
     * 사용자 활성화 커스텀 Hook
     */
    public function hookCustomActivate($livewire, $params)
    {
        $userId = $params['id'] ?? null;
        
        if (!$userId) {
            session()->flash('error', '사용자 ID가 필요합니다.');
            return;
        }
        
        // 사용자 활성화
        DB::table('users')
            ->where('id', $userId)
            ->update(['is_active' => true, 'activated_at' => now()]);
            
        session()->flash('success', '사용자가 활성화되었습니다.');
    }
    
    /**
     * 비밀번호 리셋 이메일 발송
     */
    public function hookCustomSendResetEmail($livewire, $params)
    {
        $user = User::find($params['id']);
        
        if ($user) {
            // 비밀번호 리셋 토큰 생성 및 이메일 발송
            $user->sendPasswordResetNotification($token);
            session()->flash('success', '비밀번호 재설정 이메일을 발송했습니다.');
        }
    }
}
```

## Hook 호출 방법

### 1. 라이프사이클 Hook (자동 호출)

라이프사이클 Hook은 시스템이 자동으로 호출하므로 별도의 호출 코드가 필요없습니다. 컨트롤러에 메소드만 정의하면 됩니다.

```php
// 컨트롤러에 정의만 하면 자동 호출
public function hookStoring($livewire, $data) { ... }
public function hookIndexing($livewire) { ... }
```

### 2. 폼 이벤트 Hook (wire:model 연동)

폼 필드에 `wire:model`을 설정하면 자동으로 Hook이 호출됩니다.

```blade
{{-- 입력 시 자동으로 hookFormEmail() 호출 --}}
<input type="email" wire:model="form.email" class="form-control">

{{-- 선택 변경 시 자동으로 hookFormStatus() 호출 --}}
<select wire:model="form.status" class="form-select">
    <option value="active">활성</option>
    <option value="inactive">비활성</option>
</select>
```

### 3. 커스텀 Hook (명시적 호출)

#### Blade 템플릿에서 호출 (hookCustom 사용)

```blade
{{-- 버튼 클릭으로 커스텀 Hook 호출 --}}
<button wire:click="hookCustom('activate', {'id': {{ $item->id }}})" 
        class="btn btn-success">
    활성화
</button>

<button wire:click="hookCustom('sendResetEmail', {'id': {{ $item->id }}})" 
        class="btn btn-info">
    비밀번호 리셋 이메일 발송
</button>

{{-- 파라미터가 복잡한 경우 JavaScript 객체 문법 사용 --}}
<button wire:click="hookCustom('terminate', {'id': {{ $data->id }}, 'reason': 'manual'})" 
        class="btn btn-danger">
    세션 종료
</button>
```

#### JavaScript에서 호출

```javascript
// Livewire 3 방식 (hookCustom 사용)
$wire.hookCustom('processPayment', { orderId: 123, amount: 10000 });

// Alpine.js와 함께 사용
<div x-data="{ processing: false }">
    <button @click="
        processing = true;
        $wire.hookCustom('bulkProcess', { ids: selectedIds })
            .then(() => processing = false)
    ">
        일괄 처리
    </button>
</div>
```

## Hook 반환값 처리

### 1. 데이터 수정 Hook (hookStoring, hookUpdating 등)

- **배열 반환**: 수정된 데이터로 처리 계속
- **문자열 반환**: 에러 메시지로 처리하고 작업 중단
- **false 반환**: 작업 취소
- **null 또는 반환 없음**: 원본 데이터로 처리 계속

### 2. 유효성 검사 Hook (hookForm{Field})

- **false 반환**: 값 변경 거부
- **기타**: 값 변경 허용

### 3. 액션 Hook (hookDeleting, hookCustom{Name})

- **false 반환**: 액션 취소
- **문자열 반환**: 에러 메시지 표시
- **기타**: 액션 계속

## 디버깅 및 로깅

모든 Hook 호출은 Laravel 로그에 기록됩니다:

```php
// 성공적인 Hook 호출
Log::info('AdminCreate: Controller loaded successfully', [
    'class' => $this->controllerClass
]);

// Hook 메소드 호출
Log::info('AdminCreate: Calling hookStoring', [
    'controller' => get_class($this->controller),
    'data_before' => array_keys($insertData)
]);

// Hook이 없는 경우
Log::warning('AdminCreate: Hook method not found', [
    'hookMethod' => $hookMethod,
    'controller' => $this->controller ? get_class($this->controller) : 'null'
]);
```

## 베스트 프랙티스

1. **Hook 메소드 명명 규칙**
   - 라이프사이클 Hook: `hook{Action}` (예: `hookStoring`, `hookUpdating`)
   - 필드별 Hook: `hookForm{FieldName}` (예: `hookFormEmail`)
   - 커스텀 Hook: `hookCustom{ActionName}` (예: `hookCustomActivate`)

2. **에러 처리**
   - Hook 내에서 예외가 발생할 수 있는 코드는 try-catch로 감싸기
   - 사용자 친화적인 에러 메시지 제공
   - 중요한 작업은 로그 남기기

3. **성능 고려사항**
   - Hook 내에서 무거운 작업은 큐로 처리
   - 데이터베이스 쿼리 최적화
   - 캐싱 활용

4. **보안**
   - Hook 내에서 권한 체크 수행
   - 입력 데이터 검증
   - SQL 인젝션 방지

## 중요: Livewire AJAX 요청 시 컨트롤러 상태 유지

### Livewire와 컨트롤러 인스턴스 문제

Livewire는 각 AJAX 요청마다 컴포넌트를 재생성합니다. 이 때 `protected` 속성인 `$controller`는 유지되지 않습니다.

#### 문제 상황
```php
// ❌ 잘못된 구현 - AJAX 요청 시 controller가 null이 됨
class AdminShow extends Component
{
    protected $controller = null;  // AJAX 요청 시 유지되지 않음
    protected $controllerClass;    // protected도 유지되지 않음
    
    public function hookCustom($hookName, $params = [])
    {
        // $this->controller가 null이므로 Hook이 실행되지 않음
        if (!$this->controller) {
            // 에러 발생: Controller not set
        }
    }
}
```

#### 해결 방법
```php
// ✅ 올바른 구현 - controllerClass를 public으로 저장하고 재초기화
class AdminTable extends Component
{
    public $controllerClass = null;  // public 속성은 요청 간 유지됨
    protected $controller = null;
    
    public function boot()
    {
        // 매 요청마다 컨트롤러 재초기화
        if ($this->controllerClass && !$this->controller) {
            if (class_exists($this->controllerClass)) {
                $this->controller = new $this->controllerClass;
            }
        }
    }
    
    public function hookCustom($actionName, $params = [])
    {
        // 컨트롤러 재설정 (필요시)
        if (!$this->controller && $this->controllerClass) {
            if (class_exists($this->controllerClass)) {
                $this->controller = new $this->controllerClass;
            }
        }
        
        // Hook 메소드 호출
        $methodName = 'hookCustom' . ucfirst($actionName);
        if ($this->controller && method_exists($this->controller, $methodName)) {
            $this->controller->$methodName($this, $params);
        }
    }
}
```

### View에서 JavaScript 문법 사용

#### 문제: Alpine Expression Error
```blade
{{-- ❌ 잘못된 방법 - PHP 배열 문법 --}}
<button wire:click="hookCustom('actionName', ['id' => {{ $data['id'] }}])">
{{-- Alpine.js 에러: Malformed arrow function parameter list --}}
```

#### 해결: JavaScript 객체 문법 사용
```blade
{{-- ✅ 올바른 방법 - JavaScript 객체 문법 --}}
<button wire:click="hookCustom('actionName', {'id': {{ $data['id'] }}})">
```

**이유:**
- Livewire는 Alpine.js를 사용하여 JavaScript 표현식을 파싱
- `['id' => 11]`에서 `=>`를 arrow function으로 해석하려 함
- JavaScript 객체는 `{ key: value }` 형식 사용

## 트러블슈팅

### 1. Hook이 호출되지 않는 경우

**체크리스트:**
1. ✅ JSON 파일에 `controllerClass`가 설정되어 있는가?
2. ✅ 컨트롤러 클래스 경로가 올바른가? (전체 네임스페이스 포함)
3. ✅ Hook 메소드명이 정확한가? (대소문자 구분)
4. ✅ Hook 메소드가 public으로 선언되어 있는가?

```php
// 디버깅 코드
Log::info('Controller class: ' . ($this->controllerClass ?? 'not set'));
Log::info('Controller instance: ' . ($this->controller ? 'exists' : 'null'));

// 메소드 존재 확인
if ($this->controller && method_exists($this->controller, $hookMethod)) {
    Log::info("Method exists: {$hookMethod}");
} else {
    Log::error("Method not found: {$hookMethod}");
}
```

### 2. Livewire 상태 문제

```php
// 컨트롤러 재설정 (Livewire 요청마다 필요)
if (!$this->controller && $this->controllerClass) {
    $this->setupController();
}
```

### 3. 데이터 새로고침

```php
// Hook 실행 후 데이터 새로고침
$this->refreshData();

// 또는 특정 컴포넌트 리프레시
$this->dispatch('refresh-table');
```

### 4. 일반적인 문제 해결

| 문제 | 원인 | 해결 방법 |
|------|------|-----------|
| Hook이 전혀 동작하지 않음 | controllerClass 미설정 | JSON 파일에 `controllerClass` 추가 |
| "Controller class not found" 에러 | 잘못된 네임스페이스 | 전체 네임스페이스 경로 확인 |
| Hook 메소드가 호출되지 않음 | 메소드명 오타 | 메소드명과 대소문자 확인 |
| Hook에서 에러 발생 | Hook 내부 로직 문제 | try-catch로 에러 처리 추가 |

## 참고 사항

- 모든 Hook 메소드는 선택적입니다. 필요한 Hook만 구현하면 됩니다.
- Hook 메소드는 public으로 선언해야 합니다.
- Livewire 컴포넌트 인스턴스(`$livewire`)를 통해 컴포넌트의 모든 public 속성과 메소드에 접근할 수 있습니다.
- Hook 내에서 세션, 이벤트, 알림 등 Laravel의 모든 기능을 사용할 수 있습니다.

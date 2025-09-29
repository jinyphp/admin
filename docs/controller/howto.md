# How-To Guide: @jiny/admin 사용법

이 문서는 @jiny/admin 패키지를 사용하여 관리자 CRUD 페이지를 구축하는 방법을 설명합니다.

## 목차
1. [아키텍처 이해하기](#아키텍처-이해하기)
2. [컨트롤러 구조](#컨트롤러-구조)
3. [Livewire 컴포넌트](#livewire-컴포넌트)
4. [페이지별 구현](#페이지별-구현)
5. [커스터마이징](#커스터마이징)

## 아키텍처 이해하기

@jiny/admin은 다음과 같은 아키텍처로 구성됩니다:

```
사용자 요청
    ↓
Single Action Controller (라우팅)
    ↓
Blade View (레이아웃)
    ↓
Livewire Component (로직)
    ↓
Hook Methods (커스터마이징)
```

### 핵심 원칙

1. **Single Action Controller**: AI 코드 분석 최적화를 위해 각 액션마다 독립된 컨트롤러 사용
2. **Livewire 기반 로직**: 실제 비즈니스 로직은 재사용 가능한 Livewire 컴포넌트에서 처리
3. **Hook 시스템**: 표준 동작을 커스터마이징하기 위한 Hook 메서드 제공
4. **JSON 설정**: 동적 동작을 위한 JSON 기반 설정

## 컨트롤러 구조

### 디렉토리 구조
```
jiny/{module}/App/Http/Controllers/Admin/{Feature}/
├── Admin{Feature}.php           # 목록 페이지
├── Admin{Feature}Create.php     # 생성 페이지
├── Admin{Feature}Edit.php       # 수정 페이지
├── Admin{Feature}Delete.php     # 삭제 처리
├── Admin{Feature}Show.php       # 상세보기
└── Admin{Feature}.json          # 설정 파일
```

### 컨트롤러 예시

```php
// AdminUsertype.php (목록 페이지)
namespace Jiny\Admin\App\Http\Controllers\Admin\AdminUsertype;

class AdminUsertype extends Controller
{
    private $viewPath = "jiny-admin::admin.admin_usertype.index";
    
    public function __invoke(Request $request)
    {
        $jsonData = $this->loadJson(__DIR__.'/AdminUsertype.json');
        
        return view($this->viewPath, [
            'jsonData' => $jsonData
        ]);
    }
    
    // Hook 메서드 (선택사항)
    public function hookIndexing($wire)
    {
        // 목록 조회 전 처리
    }
}
```

## Livewire 컴포넌트

### 공통 컴포넌트

#### 1. AdminTable
목록 테이블을 관리하는 컴포넌트

**주요 기능:**
- 데이터 조회 및 페이지네이션
- 정렬 기능
- 체크박스 선택
- 이벤트 수신 (검색, 필터)

**사용법:**
```blade
@livewire('jiny-admin::admin-table', ['jsonData' => $jsonData])
```

#### 2. AdminSearch
검색과 필터링을 담당하는 컴포넌트

**주요 기능:**
- 실시간 검색
- 상태 필터
- 정렬 옵션
- 페이지당 개수 선택

**사용법:**
```blade
@livewire('jiny-admin::admin-search', ['jsonData' => $jsonData])
```

#### 3. AdminCreate
새 데이터 생성을 처리하는 컴포넌트

**주요 기능:**
- 폼 데이터 관리
- 유효성 검증
- DB 저장
- Hook 호출

#### 4. AdminEdit
데이터 수정을 처리하는 컴포넌트

**주요 기능:**
- 기존 데이터 로드
- 폼 데이터 관리
- DB 업데이트
- Hook 호출

#### 5. AdminDelete
삭제 확인 및 처리 컴포넌트

**주요 기능:**
- 삭제 확인 모달
- 난수 확인 (실수 방지)
- 단일/다중 삭제
- Hook 호출

### 컴포넌트 간 통신

컴포넌트들은 Livewire 이벤트를 통해 통신합니다:

```php
// 이벤트 발생 (AdminSearch)
$this->dispatch('search-updated', search: $value);

// 이벤트 수신 (AdminTable)
protected $listeners = [
    'search-updated' => 'updateSearch'
];

public function updateSearch($search)
{
    $this->search = $search;
    $this->resetPage();
}
```

## 페이지별 구현

### 1. 목록 페이지

**구성 요소:**
- AdminSearch: 검색 및 필터
- AdminTable: 데이터 테이블
- AdminDelete: 삭제 모달

**페이지 흐름:**
1. 사용자가 검색어 입력
2. AdminSearch가 `search-updated` 이벤트 발생
3. AdminTable이 이벤트 수신하여 데이터 필터링
4. 삭제 버튼 클릭 시 AdminDelete 모달 표시

**Blade 템플릿 구조:**
```blade
<div>
    <!-- 검색 영역 -->
    @livewire('jiny-admin::admin-search', ['jsonData' => $jsonData])
    
    <!-- 테이블 영역 -->
    @livewire('jiny-admin::admin-table', ['jsonData' => $jsonData])
    
    <!-- 삭제 모달 -->
    @livewire('jiny-admin::admin-delete', ['jsonData' => $jsonData])
</div>
```

### 2. 생성 페이지

**페이지 흐름:**
1. 폼 표시 (초기값은 `hookCreating`에서 설정 가능)
2. 사용자 입력
3. 저장 버튼 클릭
4. `hookStoring` 호출 (데이터 가공)
5. DB 저장
6. `hookStored` 호출 (후처리)
7. 목록 페이지로 리다이렉트

**폼 구조 예시:**
```blade
<div class="space-y-6">
    <div>
        <label>타입 코드</label>
        <input wire:model="form.code" />
        @error('form.code') <span>{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label>이름</label>
        <input wire:model="form.name" />
        @error('form.name') <span>{{ $message }}</span> @enderror
    </div>
    
    <button wire:click="save">저장</button>
</div>
```

### 3. 수정 페이지

**페이지 흐름:**
1. 기존 데이터 로드
2. `hookEditing` 호출 (데이터 전처리)
3. 폼에 데이터 표시
4. 사용자 수정
5. 저장 시 `hookUpdating` 호출
6. DB 업데이트
7. `hookUpdated` 호출 (후처리)

### 4. 상세보기 페이지

**페이지 구성:**
- 읽기 전용 데이터 표시
- 수정/삭제 버튼
- 목록으로 돌아가기

### 5. 삭제 처리

**삭제 프로세스:**
1. 삭제 버튼 클릭
2. 확인 모달 표시
3. 10자리 난수 생성 및 표시
4. 사용자가 난수 입력 (복사 버튼 제공)
5. 난수 일치 시 삭제 버튼 활성화
6. `hookDeleting` 호출
7. DB에서 삭제
8. `hookDeleted` 호출

## 커스터마이징

### JSON 설정 파일

각 기능은 JSON 파일로 설정을 관리합니다:

```json
{
    "title": "사용자 관리",
    "subtitle": "시스템 사용자를 관리합니다",
    
    "table": {
        "name": "users",
        "model": "\\App\\Models\\User",
        "primaryKey": "id",
        "timestamps": true
    },
    
    "index": {
        "tablePath": "admin.users.table",
        "searchPath": "admin.users.search",
        "pagination": {
            "perPage": 20
        },
        "searchable": ["name", "email"],
        "sortable": ["id", "name", "created_at"],
        "features": {
            "enableCreate": true,
            "enableDelete": true,
            "enableEdit": true,
            "enableSearch": true
        }
    },
    
    "create": {
        "formPath": "admin.users.create",
        "fillable": ["name", "email", "password", "role"]
    },
    
    "edit": {
        "formPath": "admin.users.edit", 
        "fillable": ["name", "email", "role"]
    },
    
    "validation": {
        "rules": {
            "name": "required|string|max:255",
            "email": "required|email|unique:users,email"
        },
        "messages": {
            "name.required": "이름은 필수입니다",
            "email.unique": "이미 사용 중인 이메일입니다"
        }
    }
}
```

### Hook 활용

Hook을 통해 표준 동작을 커스터마이징할 수 있습니다:

```php
class AdminProduct extends Controller
{
    // 목록 조회 전
    public function hookIndexing($wire)
    {
        // 권한 체크
        if (!auth()->user()->can('view-products')) {
            abort(403);
        }
        
        // 조건 추가
        $wire->actions['where'] = [
            'status' => 'active'
        ];
    }
    
    // 저장 전 데이터 가공
    public function hookStoring($wire, $form)
    {
        // SKU 자동 생성
        if (empty($form['sku'])) {
            $form['sku'] = 'PRD-' . time();
        }
        
        // 슬러그 생성
        $form['slug'] = Str::slug($form['name']);
        
        return $form;
    }
    
    // 삭제 전 체크
    public function hookDeleting($wire, $row)
    {
        // 주문이 있는 상품은 삭제 불가
        $orderCount = Order::where('product_id', $row['id'])->count();
        if ($orderCount > 0) {
            session()->flash('error', '주문이 있는 상품은 삭제할 수 없습니다.');
            return false;
        }
        
        return $row;
    }
}
```

### 뷰 커스터마이징

각 페이지의 뷰 파일을 커스터마이징할 수 있습니다:

```
resources/views/admin/admin_{feature}/
├── table.blade.php    # 테이블 레이아웃
├── create.blade.php   # 생성 폼
├── edit.blade.php     # 수정 폼
├── show.blade.php     # 상세보기
└── search.blade.php   # 검색 폼
```

**예시 - table.blade.php:**
```blade
<table class="min-w-full divide-y divide-gray-200">
    <thead>
        <tr>
            <th><input type="checkbox" wire:model="selectedAll"></th>
            <th wire:click="sortBy('code')">코드</th>
            <th wire:click="sortBy('name')">이름</th>
            <th>액션</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td><input type="checkbox" wire:model="selected" value="{{ $row->id }}"></td>
            <td>{{ $row->code }}</td>
            <td>{{ $row->name }}</td>
            <td>
                <a href="/admin/{{ $feature }}/{{ $row->id }}/edit">수정</a>
                <button wire:click="requestDelete({{ $row->id }})">삭제</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

## 실전 예제

### 상품 관리 시스템 구축

1. **CRUD 생성**
```bash
php artisan admin:make shop product
```

2. **마이그레이션 수정**
```php
Schema::create('admin_products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2);
    $table->integer('stock')->default(0);
    $table->text('description')->nullable();
    $table->boolean('enable')->default(true);
    $table->timestamps();
});
```

3. **Hook 구현**
```php
public function hookStoring($wire, $form)
{
    // SKU 자동 생성
    if (empty($form['sku'])) {
        $form['sku'] = 'PRD-' . strtoupper(Str::random(8));
    }
    
    // 가격 검증
    if ($form['price'] < 0) {
        session()->flash('error', '가격은 0 이상이어야 합니다.');
        return false;
    }
    
    return $form;
}
```

4. **뷰 커스터마이징**
```blade
<!-- create.blade.php -->
<div class="grid grid-cols-2 gap-4">
    <div>
        <label>상품명</label>
        <input wire:model="form.name" required>
    </div>
    <div>
        <label>SKU</label>
        <input wire:model="form.sku" placeholder="비워두면 자동 생성">
    </div>
    <div>
        <label>가격</label>
        <input type="number" wire:model="form.price" min="0" step="0.01">
    </div>
    <div>
        <label>재고</label>
        <input type="number" wire:model="form.stock" min="0">
    </div>
</div>
```

## 디버깅 팁

### Hook 실행 확인
```php
public function hookIndexing($wire)
{
    \Log::debug('Hook executed', [
        'method' => __METHOD__,
        'user' => auth()->id()
    ]);
}
```

### Livewire 디버깅
```php
// Livewire 컴포넌트에서
public function mount()
{
    dd($this->jsonData); // 데이터 확인
}
```

### 이벤트 디버깅
브라우저 콘솔에서:
```javascript
Livewire.on('search-updated', (data) => {
    console.log('Search updated:', data);
});
```

## 성능 최적화

1. **페이지네이션 활용**: 큰 데이터셋은 적절한 페이지 크기 설정
2. **인덱스 추가**: 검색/정렬 컬럼에 DB 인덱스 추가
3. **캐싱**: 자주 조회되는 데이터는 캐싱 활용
4. **Eager Loading**: N+1 쿼리 문제 방지

```php
public function hookIndexed($wire, $rows)
{
    // Eager Loading 예시
    $rows->load(['category', 'tags']);
    return $rows;
}
```

## 보안 고려사항

1. **권한 체크**: Hook에서 권한 검증
2. **입력 검증**: Validation rules 철저히 설정
3. **XSS 방지**: Blade의 {{ }} 사용 (자동 이스케이프)
4. **CSRF 보호**: Laravel 기본 제공

## 마무리

@jiny/admin은 반복적인 CRUD 작업을 자동화하면서도 Hook 시스템을 통해 유연한 커스터마이징을 제공합니다. 이 가이드를 참고하여 효율적인 관리자 시스템을 구축하세요.
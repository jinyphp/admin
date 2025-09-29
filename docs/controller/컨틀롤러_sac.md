# Admin 컨트롤러 생성 규칙 및 구조

## 📌 개요

@jiny/admin 패키지의 컨트롤러는 Laravel의 Single Action Controller 패턴을 기반으로 하며, JSON 설정과 Hook 시스템을 통해 유연한 커스터마이징을 제공합니다.

## 🎯 핵심 설계 원칙

### 1. Single Action Controller
모든 컨트롤러는 `__invoke()` 메소드만을 가지는 Single Action Controller로 구현됩니다.

```php
class AdminUsers extends Controller
{
    public function __invoke(Request $request)
    {
        // 단일 책임을 가진 컨트롤러
        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData
        ]);
    }
}
```

**장점:**
- 단일 책임 원칙(SRP) 준수
- 라우트 정의가 명확
- 테스트가 용이
- 컨트롤러 분리로 유지보수 향상

### 2. JSON 설정 분리
컨트롤러의 동작 설정을 하드코딩하지 않고 별도의 JSON 파일로 분리합니다.

```json
// AdminUsers.json
{
    "model": "User",
    "route": {
        "name": "admin.users",
        "prefix": "admin/users"
    },
    "template": {
        "index": "template.index",
        "create": "template.create",
        "edit": "template.edit"
    },
    "index": {
        "table": {
            "columns": {
                "id": { "label": "ID", "sortable": true },
                "email": { "label": "이메일", "sortable": true },
                "name": { "label": "이름", "sortable": true }
            }
        },
        "pagination": {
            "perPage": 10,
            "perPageOptions": [10, 25, 50, 100]
        }
    }
}
```

**장점:**
- 코드 수정 없이 동작 변경 가능
- 설정 재사용 가능
- 버전 관리 용이

### 3. Template and Include
관리자 페이지는 대부분 유사한 구조를 가지므로 템플릿을 사용합니다.

```blade
{{-- template/index.blade.php --}}
<x-admin-layout>
    {{-- 공통 헤더 --}}
    @livewire('admin-header-with-settings')
    
    {{-- 페이지별 컨텐츠 --}}
    @include($jsonData['view']['index'] ?? 'admin.admin_users.table')
    
    {{-- 공통 푸터 --}}
    @livewire('admin-footer')
</x-admin-layout>
```

## 🔧 Livewire3 컴포넌트 통합

공통된 기능을 Livewire3 컴포넌트로 분리하여 재사용성을 높입니다.

### 주요 Livewire 컴포넌트

```php
// AdminTable.php - 테이블 표시 컴포넌트
class AdminTable extends Component
{
    public function render()
    {
        $rows = $this->getRows();
        
        // 컨트롤러 Hook 호출
        if ($this->controller && method_exists($this->controller, 'hookIndexed')) {
            $rows = $this->controller->hookIndexed($this, $rows);
        }
        
        return view('livewire.admin-table', [
            'rows' => $rows
        ]);
    }
}
```

## 🔨 컨트롤러 생성 명령어

### 1. 전체 모듈 생성 (컨트롤러 + 모델 + 마이그레이션 + 뷰)
```bash
php artisan admin:make {module} {feature}
```

예시:
```bash
php artisan admin:make shop product
```

### 2. 컨트롤러만 생성
```bash
php artisan admin:make-controller {module} {controller} [--force]
```

예시:
```bash
# 새 컨트롤러 생성
php artisan admin:make-controller shop product

# 기존 컨트롤러 덮어쓰기
php artisan admin:make-controller shop product --force
```

생성되는 파일:
- `AdminProduct.php` - 메인 컨트롤러 (목록)
- `AdminProductCreate.php` - 생성 컨트롤러
- `AdminProductEdit.php` - 수정 컨트롤러
- `AdminProductDelete.php` - 삭제 컨트롤러
- `AdminProductShow.php` - 상세 보기 컨트롤러

### 3. JSON 설정 파일 생성
```bash
php artisan admin:make-json {module} {controller} [--force]
```

예시:
```bash
php artisan admin:make-json shop product
```

### 4. 라우트 등록
```bash
php artisan admin:route-add {module} {feature}
```

## 📁 디렉토리 구조

```
jiny/
└── {module}/
    └── App/
        └── Http/
            └── Controllers/
                └── Admin/
                    └── Admin{Feature}/
                        ├── Admin{Feature}.php         # 메인 컨트롤러 (목록)
                        ├── Admin{Feature}Create.php   # 생성
                        ├── Admin{Feature}Edit.php     # 수정
                        ├── Admin{Feature}Delete.php   # 삭제
                        ├── Admin{Feature}Show.php     # 상세
                        └── Admin{Feature}.json        # 설정 파일
```

## 🪝 Hook 시스템

각 컨트롤러는 특정 시점에 호출되는 Hook 메소드를 구현할 수 있습니다.

### 목록 컨트롤러 (Admin{Feature}.php)
- `hookIndexing($wire)` - 데이터 조회 전
- `hookIndexed($wire, $rows)` - 데이터 조회 후
- `hookTableHeader($wire)` - 테이블 헤더 설정
- `hookPagination($wire)` - 페이지네이션 설정
- `hookSorting($wire)` - 정렬 설정
- `hookSearch($wire)` - 검색 설정
- `hookFilters($wire)` - 필터 설정

### 생성 컨트롤러 (Admin{Feature}Create.php)
- `hookCreating($wire, $value)` - 폼 초기화
- `hookStoring($wire, $form)` - 저장 전 처리
- `hookStored($wire, $form)` - 저장 후 처리
- `hookForm{FieldName}($wire, $value, $fieldName)` - 필드별 실시간 검증

### 수정 컨트롤러 (Admin{Feature}Edit.php)
- `hookEditing($wire, $form)` - 수정 폼 초기화
- `hookUpdating($wire, $form)` - 업데이트 전 처리
- `hookUpdated($wire, $form)` - 업데이트 후 처리
- `hookForm{FieldName}($wire, $value, $fieldName)` - 필드별 실시간 검증

### 삭제 컨트롤러 (Admin{Feature}Delete.php)
- `hookDeleting($wire, $id)` - 삭제 전 처리
- `hookDeleted($wire, $id)` - 삭제 후 처리

### 상세 컨트롤러 (Admin{Feature}Show.php)
- `hookShowing($wire, $data)` - 표시 전 데이터 가공
- `hookRelatedData($wire, $id)` - 관련 데이터 로드

## 💡 구현 예시

### 사용자 관리 모듈 생성

```bash
# 1. 컨트롤러 생성
php artisan admin:make-controller admin users

# 2. JSON 설정 파일 생성
php artisan admin:make-json admin users

# 3. JSON 파일 커스터마이징
# /jiny/admin/App/Http/Controllers/Admin/AdminUsers/AdminUsers.json 편집

# 4. 라우트 등록
php artisan admin:route-add admin users

# 5. 마이그레이션 실행
php artisan migrate

# 6. 테스트
# http://localhost:8000/admin/users
```

### Hook 구현 예시

```php
// AdminUsersCreate.php
class AdminUsersCreate extends Controller
{
    // 이메일 필드 실시간 검증
    public function hookFormEmail($wire, $value, $fieldName = 'email')
    {
        // 이메일 형식 검증
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $wire->addError('form.email', '올바른 이메일 형식이 아닙니다.');
            return;
        }

        // 중복 체크
        $exists = DB::table('users')->where('email', $value)->exists();
        if ($exists) {
            $wire->addError('form.email', '이미 등록된 이메일입니다.');
        } else {
            $wire->resetErrorBag('form.email');
        }
    }

    // 저장 전 데이터 가공
    public function hookStoring($wire, $form)
    {
        // 패스워드 해싱
        if (isset($form['password'])) {
            $form['password'] = Hash::make($form['password']);
        }

        // 타임스탬프 추가
        $form['created_at'] = now();
        $form['updated_at'] = now();

        return $form;
    }
}
```

## ✅ 체크리스트

새 Admin 컨트롤러 생성 시:
- [ ] `admin:make-controller` 명령으로 컨트롤러 생성
- [ ] `admin:make-json` 명령으로 JSON 설정 파일 생성
- [ ] JSON 파일을 요구사항에 맞게 커스터마이징
- [ ] 필요한 Hook 메소드 구현
- [ ] `admin:route-add` 명령으로 라우트 등록
- [ ] 마이그레이션 실행
- [ ] 뷰 파일 생성 (필요시)
- [ ] 기능 테스트

## 🚀 모범 사례

1. **단일 책임 원칙 준수**: 각 컨트롤러는 하나의 작업만 수행
2. **JSON 설정 활용**: 코드 변경 없이 동작 변경 가능하도록 설정 분리
3. **Hook 적절히 사용**: 필요한 시점에만 Hook 구현
4. **Livewire 컴포넌트 재사용**: 공통 기능은 Livewire 컴포넌트로 분리
5. **에러 처리**: 모든 Hook에서 적절한 에러 처리
6. **트랜잭션 사용**: 데이터 변경 시 트랜잭션으로 일관성 보장

## 🔒 보안 고려사항

1. **권한 검사**: 각 작업에 대한 권한 검사 필수
2. **입력 검증**: 모든 사용자 입력 검증
3. **SQL 인젝션 방지**: Query Builder 또는 Eloquent 사용
4. **XSS 방지**: Blade 템플릿의 자동 이스케이핑 활용
5. **CSRF 보호**: Laravel의 CSRF 토큰 사용
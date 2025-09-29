# Jiny Admin 컨트롤러 JSON 설정 가이드

## 개요

Jiny Admin 시스템은 JSON 설정 파일을 통해 관리자 인터페이스를 자동으로 생성합니다. 각 관리 모듈은 해당 컨트롤러 디렉토리에 위치한 JSON 파일로 설정됩니다.

## JSON 파일 자동 생성

### 명령어
```bash
php artisan admin:make-json {module} {controller} [--force]
```

### 예시
```bash
# 새 JSON 파일 생성
php artisan admin:make-json shop product

# 기존 파일 덮어쓰기
php artisan admin:make-json shop product --force
```

이 명령은 `/jiny/{module}/App/Http/Controllers/Admin/Admin{Controller}/Admin{Controller}.json` 파일을 생성합니다.

## 기본 구조

모든 JSON 설정 파일은 다음과 같은 기본 구조를 따릅니다:

```json
{
    "title": "모듈 제목",
    "subtitle": "모듈 부제목",
    "description": "모듈 상세 설명",
    "route": {},
    "template": {},
    "table": {},
    "index": {},
    "show": {},
    "create": {},
    "edit": {},
    "store": {},
    "update": {},
    "destroy": {}
}
```

> **중요**: `controllerClass` 필드는 JSON에서 직접 설정하지 않습니다. 컨트롤러 클래스명은 디렉토리 구조와 파일명을 기반으로 자동으로 결정되어 동적으로 주입됩니다.

## JSON 설정 파일 관리 시스템

### JsonConfigService - JSON 설정 서비스 객체

Jiny Admin은 JSON 설정 파일을 읽고 쓰기 위한 전용 서비스 객체 `JsonConfigService`를 제공합니다. 이 서비스는 설정 파일의 로드, 파싱, 캐싱, 저장을 담당합니다.

#### 주요 기능

1. **설정 파일 로드**
   - 컨트롤러 디렉토리 기반 자동 탐색
   - JSON 파일 존재 여부 검증
   - JSON 구문 오류 체크

2. **설정 캐싱**
   - 한 번 로드된 설정은 메모리에 캐싱
   - 파일 변경 감지 시 자동 리로드
   - 성능 최적화

3. **설정 쓰기**
   - 런타임에 설정 변경 가능
   - JSON 포맷 유지 및 들여쓰기 보존
   - 백업 생성 옵션

4. **설정 병합**
   - 기본 설정과 사용자 설정 병합
   - 계층적 설정 오버라이드
   - 부분 업데이트 지원

#### 컨트롤러에서 사용하는 방법

##### 1. 컨트롤러 디렉토리 기반 자동 로드 (권장)

컨트롤러의 `__DIR__` 매직 상수를 사용하여 같은 디렉토리의 JSON 파일을 자동으로 찾아 로드합니다:

```php
use Jiny\Admin\App\Services\JsonConfigService;

class AdminUsers extends Controller
{
    private $jsonData;
    
    public function __construct()
    {
        // __DIR__을 전달하면 같은 디렉토리의 {ClassName}.json 파일을 자동으로 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
        // 자동으로 /AdminUsers/AdminUsers.json 파일을 찾아서 로드
    }
    
    public function index()
    {
        // JSON 설정 사용
        $title = $this->jsonData['title'] ?? 'Default Title';
        $perPage = $this->jsonData['index']['pagination']['perPage'] ?? 20;
        
        return view($this->jsonData['template']['index']);
    }
}
```

##### 2. 전체 경로로 로드

특정 경로의 JSON 파일을 직접 로드:

```php
$jsonConfigService = new JsonConfigService;
$config = $jsonConfigService->loadFromPath('/path/to/config.json');
```

#### loadFromControllerPath 메소드의 장점

1. **모듈 독립성**: 각 컨트롤러가 자신의 디렉토리에서 JSON을 로드하므로 모듈간 독립성 보장
2. **경로 자동 결정**: `__DIR__`을 사용하여 컨트롤러 위치와 상관없이 올바른 JSON 파일 탐색
3. **리팩토링 안전**: 디렉토리 이동 시에도 `__DIR__`이 자동으로 업데이트
4. **네이밍 규칙 준수**: 클래스명과 JSON 파일명이 자동으로 매칭


## 컨트롤러 타입 분류

### 1. CRUD 컨트롤러
- **특징**: 전체 CRUD 작업 지원
- **메소드**: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
- **예시**: AdminUsers, AdminUsertype

### 2. 읽기 전용 컨트롤러
- **특징**: 조회 기능만 제공
- **메소드**: `index()`, `show()`, 일부 커스텀 액션
- **예시**: AdminPasswordLogs, AdminUserLogs


## 컨트롤러 클래스 자동 주입 메커니즘

### 동적 클래스 결정 규칙

JSON 파일이 위치한 디렉토리 구조를 기반으로 컨트롤러 클래스가 자동으로 결정됩니다:

```
디렉토리: /Admin/AdminUsers/AdminUsers.json
↓
JSON 서비스가 파일 경로 분석
↓
자동 생성 클래스: \Jiny\Admin\App\Http\Controllers\Admin\AdminUsers\AdminUsers
```

### 동작 원리

1. **디렉토리 스캔**: `App/Http/Controllers/Admin/` 하위의 각 모듈 디렉토리를 스캔
2. **JSON 파일 감지**: 각 디렉토리에서 `{ModuleName}.json` 파일을 찾음
3. **서비스 객체 로드**: `JsonConfigService`가 JSON 파일을 로드하고 파싱
4. **클래스명 생성**: 디렉토리 경로를 네임스페이스로 변환
5. **동적 주입**: 런타임에 해당 클래스를 자동으로 로드하고 인스턴스화

### 예시

```
파일 위치: jiny/admin/App/Http/Controllers/Admin/AdminUsers/AdminUsers.json
자동 결정: \Jiny\Admin\App\Http\Controllers\Admin\AdminUsers\AdminUsers

파일 위치: jiny/admin/App/Http/Controllers/Admin/AdminPasswordLogs/AdminPasswordLogs.json
자동 결정: \Jiny\Admin\App\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogs
```

### AdminBaseController와 서비스 통합

`AdminBaseController`는 JSON 설정 파일을 자동으로 로드하고 관리합니다:

```php
// AdminBaseController 상속으로 모든 기능 자동 제공
namespace Jiny\Admin\App\Http\Controllers\Admin\AdminUsers;

use Jiny\Admin\App\Http\Controllers\AdminBaseController;
use Illuminate\Http\Request;

class AdminUsers extends AdminBaseController
{
    // 생성자 불필요 - 부모 클래스가 자동 처리
    // - JSON 설정 자동 로드
    // - 동적 모델 자동 생성
    // - 설정 경로 자동 결정
    
    public function index(Request $request)
    {
        // $this->config 자동으로 사용 가능
        $perPage = $this->getConfig('index.pagination.perPage', 20);
        $features = $this->getConfig('index.features');
        
        // 동적 설정 변경
        if ($request->has('per_page')) {
            $this->setConfig('index.pagination.perPage', $request->per_page);
            $this->saveConfig(); // JSON 파일에 저장
        }
        
        // 헬퍼 메소드 활용
        if ($this->isFeatureEnabled('enableSearch')) {
            // 검색 기능 활성화 시 처리
        }
        
        return view($this->getViewPath('index'));
    }
}
```

### 장점

- **설정 단순화**: JSON에서 클래스 경로를 하드코딩할 필요 없음
- **일관성 보장**: 디렉토리 구조와 클래스명이 항상 일치
- **리팩토링 용이**: 디렉토리 이동 시 자동으로 클래스 경로 업데이트
- **오류 감소**: 오타나 잘못된 경로 입력 방지
- **캐싱 지원**: 서비스 객체가 설정을 캐싱하여 성능 향상
- **동적 설정**: 런타임에 설정 변경 및 저장 가능

## 핵심 설정 섹션

### 1. 기본 정보 (필수)

```json
{
    "title": "사용자 관리",
    "subtitle": "시스템 사용자 관리",
    "description": "사용자 계정을 생성, 수정, 삭제할 수 있습니다."
}
```

> **참고**: `controllerClass`는 시스템이 자동으로 처리하므로 JSON에 포함시키지 않습니다.

### 2. 라우트 설정

```json
"route": {
    "name": "admin.users",
    "prefix": "admin/users",
    "actions": {
        "index": "index",
        "create": "create",
        "store": "store",
        "show": "show",
        "edit": "edit",
        "update": "update",
        "destroy": "destroy"
    }
}
```

### 3. 템플릿 설정
컨트롤러에서 crud 페이지의 일관적인 화면 유지를 위하여 기본 템플릿을 호출합니다. 
구체적인 기능들은 livewire3 컴포넌트로 처리합니다.
```json
"template": {
    "layout": "jiny-admin::layouts.admin",
    "index": "jiny-admin::template.index",
    "create": "jiny-admin::template.create",
    "edit": "jiny-admin::template.edit",
    "show": "jiny-admin::template.show"
}
```

별도의 레이아웃이 필요한 경우 json의 템플릿 설정을 분리 하여 적용합니다.

### 4. 데이터베이스 테이블 설정

#### 동적 모델 바인딩
컨트롤러와 Livewire 컴포넌트는 모델이나 테이블명을 하드코딩하지 않습니다. 대신 JSON 설정을 읽어 런타임에 동적으로 모델 객체를 생성하여 사용합니다. 이를 통해 동일한 컨트롤러 코드로 다양한 테이블을 관리할 수 있습니다.

#### 설정 구조
```json
"table": {
    "name": "admin_users",
    "model": "\\Jiny\\Admin\\App\\Models\\AdminUser",
    "primaryKey": "id",
    "timestamps": true,
    "softDeletes": false,
    "fillable": [],
    "guarded": ["id", "created_at", "updated_at"],
    "hidden": ["password", "remember_token"],
    "casts": {
        "email_verified_at": "datetime",
        "created_at": "datetime:Y-m-d H:i:s",
        "updated_at": "datetime:Y-m-d H:i:s"
    },
    "where": {
        "default": [],
        "conditions": []
    }
}
```

#### AdminBaseController 사용

Jiny Admin은 공통 기능을 제공하는 `AdminBaseController`를 제공합니다:

```php
namespace Jiny\Admin\App\Http\Controllers;

abstract class AdminBaseController extends Controller
{
    protected $config;      // JSON 설정 객체
    protected $moduleName;  // 모듈 이름
    protected $model;       // 동적 생성된 모델
    
    public function __construct()
    {
        // 자동으로 JSON 설정 로드
        // 자동으로 동적 모델 생성
    }
    
    // 헬퍼 메소드들
    protected function getConfig(string $key, $default = null);
    protected function setConfig(string $key, $value): void;
    protected function saveConfig(): bool;
    protected function getModel();
    protected function getViewPath(string $view): string;
    protected function getPerPage(Request $request): int;
    protected function getSorting(Request $request): array;
    protected function applySearch($query, Request $request);
    protected function applyFilters($query, Request $request);
    protected function getValidationRules(string $action): array;
    protected function getValidationMessages(string $action): array;
    protected function isFeatureEnabled(string $feature): bool;
}
```

#### 컨트롤러 구현 예시

```php
namespace Jiny\Admin\App\Http\Controllers\Admin\AdminUsers;

use Jiny\Admin\App\Http\Controllers\AdminBaseController;
use Illuminate\Http\Request;

class AdminUsers extends AdminBaseController
{
    public function index(Request $request)
    {
        // 모델과 쿼리 자동 처리
        $model = $this->getModel();
        $query = $model->query();
        
        // 기본 조건, 검색, 필터, 정렬 자동 적용
        $query = $this->applyDefaultConditions($query);
        $query = $this->applySearch($query, $request);
        $query = $this->applyFilters($query, $request);
        
        $sorting = $this->getSorting($request);
        $query->orderBy($sorting['column'], $sorting['direction']);
        
        // 페이지네이션
        $data = $query->paginate($this->getPerPage($request));
        
        // 뷰 경로 자동 결정
        return view($this->getViewPath('index'), [
            'data' => $data,
            'config' => $this->config
        ]);
    }
    
    public function store(Request $request)
    {
        // 검증 규칙 자동 로드
        $request->validate(
            $this->getValidationRules('store'),
            $this->getValidationMessages('store')
        );
        
        try {
            $model = $this->getModel();
            $model->fill($request->all());
            $model->save();
            
            return redirect()
                ->route($this->getConfig('route.name') . '.index')
                ->with('success', $this->getSuccessMessage('store'));
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $this->getErrorMessage('store', $e));
        }
    }
}
```

#### 주요 설정 항목

- **name**: 실제 데이터베이스 테이블명
- **model**: 사용할 Eloquent 모델 클래스 (선택사항, 없으면 동적 생성)
- **primaryKey**: 기본 키 필드명 (기본값: 'id')
- **timestamps**: created_at, updated_at 자동 관리 여부
- **softDeletes**: 소프트 삭제 기능 사용 여부
- **fillable**: Mass Assignment 허용 필드 (빈 배열이면 guarded 사용)
- **guarded**: Mass Assignment 보호 필드
- **hidden**: JSON 직렬화 시 숨길 필드
- **casts**: 타입 캐스팅 설정
- **where**: 기본 쿼리 조건 설정

## CRUD 작업 설정

### 1. 목록 페이지 (index)

```json
"index": {
    "heading": {
        "title": "사용자 목록",
        "description": "등록된 모든 사용자를 확인할 수 있습니다."
    },
    "features": {
        "enableCreate": true,
        "enableDelete": true,
        "enableEdit": true,
        "enableSearch": true,
        "enableSort": true,
        "enablePagination": true,
        "enableBulkActions": false,
        "enableExport": false,
        "enableImport": false,
        "enableStatusToggle": true,
        "enableSettingsDrawer": true
    },
    "tableLayoutPath": "jiny-admin::template.livewire.admin-table",
    "tablePath": "jiny-admin::admin.users.table",
    "searchLayoutPath": "jiny-admin::template.livewire.admin-search",
    "searchFormPath": "jiny-admin::admin.users.search",
    "searchable": ["name", "email", "role"],
    "sortable": ["name", "email", "created_at", "status"],
    "filterable": ["status", "role"],
    "pagination": {
        "perPage": 20,
        "options": [10, 25, 50, 100]
    },
    "sorting": {
        "default": "created_at",
        "direction": "desc"
    },
    "search": {
        "placeholder": "이름, 이메일로 검색...",
        "debounce": 300
    },
    "filters": {
        "status": {
            "label": "상태",
            "type": "select",
            "options": {
                "": "전체",
                "active": "활성",
                "inactive": "비활성"
            }
        }
    },
    "table": {
        "columns": {
            "id": {
                "label": "ID",
                "visible": true,
                "sortable": true,
                "searchable": false,
                "width": "80px",
                "responsive": "md"
            },
            "name": {
                "label": "이름",
                "visible": true,
                "sortable": true,
                "searchable": true,
                "linkRoute": "admin.users.show",
                "linkParam": "id"
            },
            "email": {
                "label": "이메일",
                "visible": true,
                "sortable": true,
                "searchable": true,
                "truncate": 30
            },
            "status": {
                "label": "상태",
                "visible": true,
                "sortable": true,
                "type": "badge",
                "badgeColors": {
                    "active": "success",
                    "inactive": "danger",
                    "pending": "warning"
                },
                "badgeLabels": {
                    "active": "활성",
                    "inactive": "비활성",
                    "pending": "대기중"
                }
            },
            "created_at": {
                "label": "등록일",
                "visible": true,
                "sortable": true,
                "format": "Y-m-d H:i:s",
                "responsive": "lg"
            }
        }
    },
    "actions": {
        "edit": {
            "label": "수정",
            "icon": "edit",
            "class": "text-blue-600 hover:text-blue-800"
        },
        "delete": {
            "label": "삭제",
            "icon": "trash",
            "class": "text-red-600 hover:text-red-800",
            "confirmation": {
                "title": "삭제 확인",
                "message": "정말로 삭제하시겠습니까?",
                "confirmText": "삭제",
                "cancelText": "취소"
            }
        }
    },
    "emptyState": {
        "title": "데이터가 없습니다",
        "description": "아직 등록된 항목이 없습니다.",
        "createText": "새로 만들기"
    }
}
```

### 2. 생성 폼 (create)

```json
"create": {
    "heading": {
        "title": "새 사용자 등록",
        "description": "새로운 사용자를 등록합니다."
    },
    "formLayoutPath": "jiny-admin::template.livewire.admin-create",
    "formPath": "jiny-admin::admin.users.create",
    "fields": {
        "name": {
            "label": "이름",
            "type": "text",
            "placeholder": "사용자 이름을 입력하세요",
            "required": true,
            "helpText": "실명을 입력해주세요"
        },
        "email": {
            "label": "이메일",
            "type": "email",
            "placeholder": "email@example.com",
            "required": true
        },
        "password": {
            "label": "비밀번호",
            "type": "password",
            "required": true,
            "minLength": 8
        },
        "role": {
            "label": "역할",
            "type": "select",
            "options": {
                "user": "일반 사용자",
                "admin": "관리자",
                "super": "슈퍼 관리자"
            },
            "default": "user"
        },
        "status": {
            "label": "상태",
            "type": "radio",
            "options": {
                "active": "활성",
                "inactive": "비활성"
            },
            "default": "active"
        }
    },
    "buttons": {
        "submit": {
            "label": "저장",
            "class": "btn-primary"
        },
        "submitAndContinue": {
            "label": "저장 후 계속",
            "class": "btn-secondary"
        },
        "cancel": {
            "label": "취소",
            "class": "btn-ghost"
        }
    }
}
```

### 3. 저장 작업 (store)

```json
"store": {
    "validation": {
        "rules": {
            "name": "required|string|max:255",
            "email": "required|email|unique:admin_users,email",
            "password": "required|min:8|confirmed",
            "role": "required|in:user,admin,super",
            "status": "required|in:active,inactive"
        },
        "messages": {
            "name.required": "이름은 필수입니다.",
            "email.required": "이메일은 필수입니다.",
            "email.unique": "이미 사용중인 이메일입니다.",
            "password.min": "비밀번호는 최소 8자 이상이어야 합니다."
        }
    },
    "messages": {
        "success": "사용자가 성공적으로 등록되었습니다.",
        "error": "사용자 등록 중 오류가 발생했습니다: %s",
        "continueSuccess": "사용자가 등록되었습니다. 계속해서 추가할 수 있습니다."
    },
    "transaction": true,
    "hooks": {
        "beforeStore": "\\App\\Hooks\\BeforeUserStore",
        "afterStore": "\\App\\Hooks\\AfterUserStore"
    }
}
```

### 4. 수정 폼 (edit)

```json
"edit": {
    "heading": {
        "title": "사용자 정보 수정",
        "description": "사용자 정보를 수정합니다."
    },
    "formLayoutPath": "jiny-admin::template.livewire.admin-edit",
    "formPath": "jiny-admin::admin.users.edit",
    "fields": {
        // create와 유사하지만 password는 선택사항
        "password": {
            "label": "비밀번호 변경",
            "type": "password",
            "required": false,
            "placeholder": "변경할 경우만 입력",
            "minLength": 8
        }
    }
}
```

### 5. 업데이트 작업 (update)

```json
"update": {
    "validation": {
        "rules": {
            "name": "required|string|max:255",
            "email": "required|email|unique:admin_users,email,{id}",
            "password": "nullable|min:8|confirmed",
            "role": "required|in:user,admin,super"
        }
    },
    "messages": {
        "success": "사용자 정보가 수정되었습니다.",
        "error": "수정 중 오류가 발생했습니다: %s"
    },
    "transaction": true
}
```

### 6. 상세 보기 (show)

상세보기 페이지의 레이아웃과 액션 버튼을 제어합니다.

#### 기본 설정

```json
"show": {
    "heading": {
        "title": "사용자 상세 정보",
        "description": "사용자의 상세 정보를 확인합니다."
    },
    "showLayoutPath": "jiny-admin::template.livewire.admin-show",
    "showPath": "jiny-admin::admin.users.show",
    
    // 액션 버튼 표시 제어 (중요!)
    "enableEdit": true,        // 수정 버튼 표시 여부 (기본값: true)
    "enableDelete": true,      // 삭제 버튼 표시 여부 (기본값: true)
    "enableListButton": true,  // 목록 버튼 표시 여부 (기본값: true)
    "enableSettingsDrawer": true, // 설정 서랍 표시 여부
    
    // 커스텀 액션 버튼
    "enableResend": true,      // 재발송 등 커스텀 버튼
    
    "display": {
        "datetimeFormat": "Y-m-d H:i:s",
        "sections": [
            {
                "title": "기본 정보",
                "fields": ["id", "name", "email", "status"]
            },
            {
                "title": "시간 정보",
                "fields": ["created_at", "updated_at"]
            }
        ]
    }
}
```

#### 액션 버튼 제어

> **⚠️ 중요**: 액션 버튼(수정, 삭제, 목록)은 **절대로** `show.blade.php` 파일에 직접 구현하지 않습니다!
> 
> - 모든 액션 버튼은 `admin-show.blade.php` 컴포넌트 레이아웃에서 자동 처리됩니다
> - JSON 설정의 `enableEdit`, `enableDelete` 값으로 표시 여부를 제어합니다
> - `show.blade.php`는 오직 데이터 표시만 담당합니다

##### 버튼 표시 제어 예시

```json
// 수정 버튼만 표시하고 삭제 버튼 숨기기
"show": {
    "enableEdit": true,
    "enableDelete": false
}

// 모든 액션 버튼 숨기기 (읽기 전용)
"show": {
    "enableEdit": false,
    "enableDelete": false,
    "enableListButton": false
}
```

#### 설정 서랍 (Settings Drawer)

설정 서랍을 통해 런타임에 show 섹션 설정을 수정할 수 있습니다:

```json
"show": {
    "enableSettingsDrawer": true,  // 설정 서랍 활성화
    "settingsDrawer": {
        "enableFieldToggle": true,      // 필드 토글 옵션 표시
        "enableDateFormat": true,       // 날짜 형식 옵션 표시
        "enableSectionToggle": true     // 섹션 토글 옵션 표시
    }
}
```

##### 설정 서랍에서 변경 가능한 항목

1. **액션 버튼 제어**
   - `enableEdit`: 수정 버튼 표시/숨김
   - `enableDelete`: 삭제 버튼 표시/숨김
   - `enableListButton`: 목록 버튼 표시/숨김

2. **표시 옵션**
   - `dateFormat`: 날짜/시간 표시 형식
   - `booleanLabels`: true/false 값의 한글 레이블
   - `enableFieldToggle`: 필드별 표시/숨김 토글
   - `enableSectionToggle`: 섹션별 접기/펼치기

3. **저장 위치**
   - 모든 변경사항은 해당 모듈의 JSON 파일에 자동 저장됨
   - 페이지 새로고침 시 적용됨

#### 커스텀 액션 추가

특정 모듈에 필요한 커스텀 액션을 추가할 수 있습니다:

```json
"show": {
    "customActions": {
        "resend": {
            "enabled": true,
            "label": "재발송",
            "icon": "arrow-path",
            "class": "btn-warning",
            "condition": "status:failed,bounced"  // 조건부 표시
        },
        "print": {
            "enabled": true,
            "label": "인쇄",
            "icon": "printer",
            "class": "btn-secondary"
        }
    }
}
```

### 7. 삭제 작업 (destroy)

```json
"destroy": {
    "confirmation": {
        "title": "삭제 확인",
        "message": "이 사용자를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.",
        "confirmText": "삭제",
        "cancelText": "취소",
        "confirmClass": "btn-danger"
    },
    "messages": {
        "success": "사용자가 삭제되었습니다.",
        "error": "삭제 중 오류가 발생했습니다: %s"
    },
    "softDelete": false,
    "transaction": true,
    "hooks": {
        "beforeDestroy": "\\App\\Hooks\\BeforeUserDestroy",
        "afterDestroy": "\\App\\Hooks\\AfterUserDestroy"
    }
}
```

### 8. 파일 업로드 설정 (upload)

파일 업로드 기능이 필요한 모듈의 경우 upload 섹션을 추가하여 파일 저장 경로, 검증 규칙, 폴더 구조 전략 등을 설정할 수 있습니다.

```json
"upload": {
    "path": "avatars",
    "disk": "public",
    "folderStrategy": "hash",
    "maxSize": 2048,
    "mimeTypes": ["image/jpeg", "image/png", "image/gif", "image/webp"],
    "deleteOldFile": true,
    "defaultImage": "/images/default-avatar.png",
    "multiple": false,
    "validation": {
        "rules": "required|image|max:2048",
        "messages": {
            "required": "파일을 선택해주세요.",
            "image": "이미지 파일만 업로드 가능합니다.",
            "max": "파일 크기는 2MB를 초과할 수 없습니다."
        }
    }
}
```

#### 업로드 설정 옵션

##### 기본 설정
- **path**: 파일이 저장될 기본 경로 (예: `avatars`, `documents`, `uploads`)
- **disk**: Laravel Storage 디스크 이름 (기본값: `public`)
- **multiple**: 다중 파일 업로드 허용 여부 (기본값: `false`)
- **defaultImage**: 이미지가 없을 때 표시할 기본 이미지 경로

##### 파일 검증
- **maxSize**: 최대 파일 크기 (KB 단위, 예: 2048 = 2MB)
- **mimeTypes**: 허용되는 MIME 타입 배열
  - 이미지: `["image/jpeg", "image/png", "image/gif", "image/webp"]`
  - 문서: `["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"]`
  - 엑셀: `["application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]`

##### 폴더 구조 전략 (folderStrategy)

파일 시스템 부하를 방지하기 위해 여러 폴더 구조 전략을 제공합니다:

1. **none**: 모든 파일을 하나의 폴더에 저장
   ```
   avatars/file1.jpg
   avatars/file2.jpg
   ```

2. **date**: 날짜별 폴더 구조 (년/월/일)
   ```
   avatars/2025/09/07/file.jpg
   ```

3. **year-month**: 년-월 폴더 구조
   ```
   avatars/2025-09/file.jpg
   ```

4. **hash**: 파일명 해시 기반 3단계 폴더 구조
   ```
   avatars/a/b/c/file.jpg
   ```

5. **user-id**: 사용자 ID 기반 폴더 구조 (1000 단위로 그룹화)
   ```
   avatars/1000/1234/file.jpg  # 사용자 ID 1234
   avatars/2000/2456/file.jpg  # 사용자 ID 2456
   ```

##### 파일 관리
- **deleteOldFile**: 새 파일 업로드 시 기존 파일 자동 삭제 (기본값: `true`)
- **keepOriginalName**: 원본 파일명 유지 여부 (기본값: `false`)
- **fileNaming**: 파일명 생성 전략
  - `uuid`: UUID 기반 파일명 (기본값)
  - `timestamp`: 타임스탬프 기반 파일명
  - `original`: 원본 파일명 유지
  - `slug`: 원본 파일명을 slug 형태로 변환

#### 실제 사용 예시

##### 아바타 이미지 업로드 설정
```json
"upload": {
    "path": "avatars",
    "disk": "public",
    "folderStrategy": "year-month",
    "maxSize": 2048,
    "mimeTypes": ["image/jpeg", "image/png", "image/gif", "image/webp"],
    "deleteOldFile": true,
    "defaultImage": "/images/default-avatar.png"
}
```

##### 문서 업로드 설정
```json
"upload": {
    "path": "documents",
    "disk": "private",
    "folderStrategy": "date",
    "maxSize": 10240,
    "mimeTypes": ["application/pdf", "application/msword"],
    "deleteOldFile": false,
    "multiple": true,
    "keepOriginalName": true
}
```

##### 제품 이미지 갤러리 설정
```json
"upload": {
    "path": "products",
    "disk": "public",
    "folderStrategy": "hash",
    "maxSize": 5120,
    "mimeTypes": ["image/jpeg", "image/png"],
    "deleteOldFile": false,
    "multiple": true,
    "thumbnail": {
        "enabled": true,
        "width": 300,
        "height": 300,
        "quality": 80
    }
}
```

#### Livewire 컴포넌트에서의 처리

AdminCreate와 AdminEdit Livewire 컴포넌트는 upload 설정을 자동으로 읽어 파일 업로드를 처리합니다:

```php
// AdminEdit.php 내부 처리 예시
protected function getUploadPath()
{
    $basePath = $this->jsonData['upload']['path'] ?? 'uploads';
    $strategy = $this->jsonData['upload']['folderStrategy'] ?? 'date';
    
    switch ($strategy) {
        case 'hash':
            $hash = md5(uniqid());
            $subPath = substr($hash, 0, 1) . '/' . 
                      substr($hash, 1, 1) . '/' . 
                      substr($hash, 2, 1);
            break;
        case 'year-month':
            $subPath = date('Y-m');
            break;
        case 'date':
            $subPath = date('Y/m/d');
            break;
        default:
            return $basePath;
    }
    
    return $basePath . '/' . $subPath;
}
```

#### 파일 삭제 시 자동 처리

AdminDelete 컴포넌트는 레코드 삭제 시 관련 파일도 자동으로 삭제합니다:
- upload 설정이 있는 경우 자동으로 파일 필드 감지
- 데이터베이스 레코드와 함께 Storage에서 파일 삭제
- 기본 이미지는 삭제하지 않음





## 컬럼 타입별 설정

### 1. 텍스트 컬럼

```json
{
    "label": "제목",
    "visible": true,
    "sortable": true,
    "searchable": true,
    "truncate": 50,
    "tooltip": true,
    "copyable": true
}
```

### 2. 날짜/시간 컬럼

```json
{
    "label": "생성일",
    "visible": true,
    "sortable": true,
    "format": "Y-m-d H:i:s",
    "timezone": "Asia/Seoul",
    "relative": true
}
```

### 3. 이미지 컬럼

```json
{
    "label": "프로필 사진",
    "type": "image",
    "visible": true,
    "width": "50px",
    "height": "50px",
    "rounded": true,
    "defaultImage": "/images/default-avatar.png"
}
```

### 4. 배지 컬럼

```json
{
    "label": "상태",
    "type": "badge",
    "visible": true,
    "badgeColors": {
        "active": "success",
        "inactive": "danger",
        "pending": "warning"
    },
    "badgeLabels": {
        "active": "활성",
        "inactive": "비활성",
        "pending": "대기중"
    }
}
```

### 5. 토글 컬럼

```json
{
    "label": "활성화",
    "type": "toggle",
    "visible": true,
    "toggleable": true,
    "confirmToggle": true,
    "toggleRoute": "admin.users.toggle-status"
}
```

### 6. 링크 컬럼

```json
{
    "label": "이름",
    "visible": true,
    "linkRoute": "admin.users.show",
    "linkParam": "id",
    "linkTarget": "_self",
    "linkClass": "text-blue-600 hover:underline"
}
```

## 검증 규칙

### 기본 Laravel 검증 규칙

- `required` - 필수 필드
- `string` - 문자열
- `email` - 이메일 형식
- `unique:{table},{column},{except}` - 중복 체크
- `min:{value}` - 최소값
- `max:{value}` - 최대값
- `in:{values}` - 특정 값들 중 하나
- `confirmed` - 확인 필드와 일치
- `nullable` - null 허용

### 커스텀 검증 메시지

```json
"validation": {
    "rules": {
        "email": "required|email|unique:users"
    },
    "messages": {
        "email.required": "이메일 주소는 필수입니다.",
        "email.email": "올바른 이메일 형식이 아닙니다.",
        "email.unique": "이미 사용중인 이메일입니다."
    },
    "attributes": {
        "email": "이메일 주소"
    }
}
```

## 네이밍 규칙

### 1. 파일 및 디렉토리

- JSON 파일: `{ModuleName}.json` (예: `AdminUsers.json`)
- 컨트롤러 디렉토리: `Admin{Module}/` (예: `AdminUsers/`)
- 컨트롤러 클래스: 디렉토리명과 동일 (자동 결정)
- 뷰 디렉토리: `admin/{module}/` (예: `admin/users/`)

> **중요**: 컨트롤러 클래스명은 반드시 디렉토리명과 일치해야 자동 주입이 정상 작동합니다.

### 2. 라우트

- 기본 패턴: `admin.{module}.{action}`
- 예시:
  - `admin.users.index`
  - `admin.users.create`
  - `admin.users.show`

### 3. 모델 및 테이블

- 모델: `Admin{Model}` (예: `AdminUser`)
- 테이블: `admin_{models}` (예: `admin_users`)

### 4. 템플릿 경로

- 레이아웃: `jiny-admin::layouts.{name}`
- 컴포넌트: `jiny-admin::template.livewire.{component}`
- 뷰: `jiny-admin::admin.{module}.{view}`

## 모범 사례

### 1. 일관성 유지

- 모든 모듈에서 동일한 구조와 네이밍 규칙 사용
- 공통 기능은 기본 설정으로 통일

### 2. 재사용성

- 공통 컴포넌트와 템플릿 활용
- 중복 코드 최소화

### 3. 확장성

- 후크와 이벤트를 통한 커스터마이징
- 커스텀 액션으로 특수 기능 추가

### 4. 보안

- 적절한 권한 설정
- 검증 규칙 철저히 적용
- 트랜잭션 사용으로 데이터 무결성 보장

### 5. 성능

- 필요한 기능만 활성화
- 페이지네이션 적절히 설정
- 검색 디바운스 설정으로 서버 부하 감소

## 디렉토리 구조 예시

올바른 모듈 구조 예시:

```
jiny/admin/App/Http/Controllers/Admin/
├── AdminUsers/                 # CRUD 컨트롤러
│   ├── AdminUsers.php          # 메인 컨트롤러 (index 메소드 포함)
│   ├── AdminUsersCreate.php    # 생성 컨트롤러
│   ├── AdminUsersEdit.php      # 수정 컨트롤러
│   ├── AdminUsersDelete.php    # 삭제 컨트롤러
│   ├── AdminUsersShow.php      # 상세 컨트롤러
│   └── AdminUsers.json         # 설정 파일 (컨트롤러 클래스 자동 결정)
└── AdminPasswordLogs/          # 읽기 전용 컨트롤러
    ├── AdminPasswordLogs.php   # 메인 컨트롤러 (index 메소드 포함)
    └── AdminPasswordLogs.json  # 설정 파일 (컨트롤러 클래스 자동 결정)
```

각 JSON 파일에서 `controllerClass`를 명시하지 않아도, 시스템이 디렉토리 구조를 분석하여 자동으로 다음과 같이 결정합니다:

- `AdminUsers/AdminUsers.json` → `\Jiny\Admin\App\Http\Controllers\Admin\AdminUsers\AdminUsers`
- `AdminPasswordLogs/AdminPasswordLogs.json` → `\Jiny\Admin\App\Http\Controllers\Admin\AdminPasswordLogs\AdminPasswordLogs`

## JsonConfigService 서비스

### 개요

`JsonConfigService`는 JSON 설정 파일을 읽고 쓰는 작업을 담당하는 핵심 서비스입니다.

### 위치
```
/jiny/admin/App/Services/JsonConfigService.php
```

### 주요 메서드

#### 1. loadFromPath($path)
JSON 파일을 읽어 배열로 반환합니다.

```php
$service = new JsonConfigService();
$config = $service->loadFromPath($jsonPath);
```

#### 2. save($path, $data)
배열 데이터를 JSON 파일로 저장합니다.

```php
$service->save($jsonPath, $settings);
// JSON_PRETTY_PRINT와 JSON_UNESCAPED_UNICODE 옵션 자동 적용
```

#### 3. 컨트롤러에서의 사용

```php
class AdminUsers extends Controller
{
    private $jsonConfigService;
    
    public function __construct()
    {
        $this->jsonConfigService = new JsonConfigService();
        $this->loadJsonConfig();
    }
    
    private function loadJsonConfig()
    {
        $jsonPath = $this->getJsonPath();
        $this->jsonData = $this->jsonConfigService->loadFromPath($jsonPath);
    }
}
```

### 특징

- **자동 경로 해석**: 상대 경로와 절대 경로 모두 지원
- **오류 처리**: 파일이 없거나 잘못된 JSON일 경우 안전한 처리
- **유니코드 보존**: 한글 등 다국어 문자 그대로 저장
- **가독성**: Pretty Print로 들여쓰기된 형식으로 저장

## Settings Drawer 컴포넌트

### 개요

Settings Drawer는 런타임에 JSON 설정을 수정할 수 있는 Livewire 컴포넌트입니다. 각 CRUD 페이지별로 전용 설정 서랍이 있습니다.

### 컴포넌트 목록

#### 1. ShowSettingsDrawer
**위치**: `/jiny/admin/App/Http/Livewire/Settings/ShowSettingsDrawer.php`
**뷰**: `/jiny/admin/resources/views/template/settings/show-settings-drawer.blade.php`

**기능**:
- 상세보기 페이지 설정 관리
- 날짜 형식, 불린 레이블 설정
- 액션 버튼(수정/삭제/목록) 표시 여부 제어
- 필드 토글, 섹션 토글 옵션

**설정 가능 항목**:
```json
{
    "show": {
        "enableEdit": true,
        "enableDelete": true,
        "enableListButton": true,
        "enableSettingsDrawer": true,
        "display": {
            "dateFormat": "Y-m-d H:i:s",
            "booleanLabels": {
                "true": "활성화",
                "false": "비활성화"
            }
        },
        "settingsDrawer": {
            "enableFieldToggle": true,
            "enableDateFormat": true,
            "enableSectionToggle": true
        }
    }
}
```

#### 2. CreateSettingsDrawer
**위치**: `/jiny/admin/App/Http/Livewire/Settings/CreateSettingsDrawer.php`
**뷰**: `/jiny/admin/resources/views/template/settings/create-settings-drawer.blade.php`

**기능**:
- 생성 폼 필드 설정
- 필수 필드 지정
- 기본값 설정
- 유효성 검사 규칙 설정

**설정 가능 항목**:
```json
{
    "create": {
        "enableTemplateSelector": true,
        "enableSaveDraft": true,
        "enableDirectSend": true,
        "fields": {
            "fieldName": {
                "required": true,
                "default": "",
                "placeholder": "",
                "validation": "required|string"
            }
        }
    }
}
```

#### 3. EditSettingsDrawer
**위치**: `/jiny/admin/App/Http/Livewire/Settings/EditSettingsDrawer.php`
**뷰**: `/jiny/admin/resources/views/template/settings/edit-settings-drawer.blade.php`

**기능**:
- 수정 폼 필드 설정
- 읽기 전용 필드 지정
- 조건부 편집 가능 설정

**설정 가능 항목**:
```json
{
    "edit": {
        "onlyEditableStatuses": ["pending"],
        "enableDirectSend": true,
        "fields": {
            "fieldName": {
                "editable": "status:pending",
                "readonly": false
            }
        }
    }
}
```

#### 4. DetailSettingsDrawer (IndexSettingsDrawer)
**위치**: `/jiny/admin/App/Http/Livewire/Settings/DetailSettingsDrawer.php`
**뷰**: `/jiny/admin/resources/views/template/settings/detail-settings-drawer.blade.php`

**기능**:
- 목록 페이지 테이블 설정
- 컬럼 표시/숨김
- 정렬 옵션
- 페이지당 항목 수

**설정 가능 항목**:
```json
{
    "index": {
        "pagination": {
            "perPage": 20
        },
        "sorting": {
            "default": "created_at",
            "direction": "desc"
        },
        "table": {
            "columns": {
                "columnName": {
                    "visible": true,
                    "sortable": true
                }
            }
        }
    }
}
```

### Settings Drawer 사용 방법

#### 1. 컨트롤러에서 활성화

```php
class AdminEmailLogs extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'jsonPath' => $this->getJsonPath(),
            'enableSettingsDrawer' => true
        ]);
    }
}
```

#### 2. Blade 뷰에서 호출

```blade
{{-- 설정 버튼 --}}
<button wire:click="$dispatch('openSettings', { jsonPath: '{{ $jsonPath }}' })">
    설정
</button>

{{-- Settings Drawer 컴포넌트 포함 --}}
@livewire('jiny-admin::settings.show-settings-drawer', ['jsonPath' => $jsonPath])
```

#### 3. 이벤트 처리

```php
// Livewire 컴포넌트에서
protected $listeners = [
    'settingsUpdated' => 'refreshSettings'
];

public function refreshSettings()
{
    // 설정 새로고침 로직
    $this->loadJsonConfig();
}
```

## 헤더 컴포넌트

### 개요

각 CRUD 페이지의 상단 헤더를 담당하는 Blade 컴포넌트입니다.

### 컴포넌트 목록

#### 1. admin-header-index
**위치**: `/jiny/admin/resources/views/template/livewire/admin-header-index.blade.php`

**기능**:
- 목록 페이지 헤더
- 생성 버튼
- 설정 버튼
- 검색 토글

#### 2. admin-header-create
**위치**: `/jiny/admin/resources/views/template/livewire/admin-header-create.blade.php`

**기능**:
- 생성 페이지 헤더
- 목록으로 돌아가기 버튼
- 설정 버튼

#### 3. admin-header-edit
**위치**: `/jiny/admin/resources/views/template/livewire/admin-header-edit.blade.php`

**기능**:
- 수정 페이지 헤더
- 목록으로 돌아가기 버튼
- 설정 버튼

#### 4. admin-header-show
**위치**: `/jiny/admin/resources/views/template/livewire/admin-header-show.blade.php`

**기능**:
- 상세보기 페이지 헤더
- 목록, 수정, 삭제 버튼
- 설정 버튼
- JSON 설정에 따른 버튼 표시/숨김

### JSON 설정과 컴포넌트 연동

#### 자동 설정 로드

모든 컴포넌트는 자동으로 JSON 설정을 로드하여 UI를 구성합니다:

```php
// 컨트롤러
public function loadJsonData()
{
    $jsonPath = $this->getJsonPath();
    $this->jsonData = json_decode(file_get_contents($jsonPath), true);
    
    // View에 전달
    return view('admin.show', [
        'jsonData' => $this->jsonData,
        'data' => $model
    ]);
}
```

```blade
{{-- Blade 뷰에서 자동 적용 --}}
@if($jsonData['show']['enableEdit'] ?? true)
    <button>수정</button>
@endif
```

#### 실시간 설정 변경

Settings Drawer를 통해 변경된 설정은 즉시 JSON 파일에 저장되고, 페이지 새로고침 시 적용됩니다:

```javascript
// 자동 새로고침
Livewire.on('refresh-page', () => {
    setTimeout(() => {
        window.location.reload();
    }, 500);
});
```

## 모범 사례

### 1. JSON 파일 구조 일관성

모든 JSON 파일은 동일한 기본 구조를 유지해야 합니다:

```json
{
    "title": "",
    "route": {},
    "table": {},
    "index": {},
    "create": {},
    "edit": {},
    "show": {},
    "destroy": {}
}
```

### 2. Settings Drawer 활용

- 개발 중: 설정을 자주 변경하며 최적값 찾기
- 운영 중: 필요시 UI 조정 가능
- 권한 제어: 관리자만 설정 변경 가능하도록 제한

### 3. 다국어 지원

모든 레이블과 메시지는 JSON에서 관리:

```json
{
    "messages": {
        "success": "성공적으로 저장되었습니다.",
        "error": "오류가 발생했습니다."
    },
    "labels": {
        "save": "저장",
        "cancel": "취소"
    }
}
```

## 결론

Jiny Admin의 JSON 설정 시스템은 일관되고 확장 가능한 관리자 인터페이스를 빠르게 구축할 수 있도록 설계되었습니다. 특히 컨트롤러 클래스의 자동 주입 메커니즘은 설정을 단순화하고 오류를 줄이며, 디렉토리 구조와 코드의 일관성을 보장합니다. 

JsonConfigService와 Settings Drawer 컴포넌트들의 조합으로 런타임에도 유연한 설정 변경이 가능하며, 이는 개발 생산성과 운영 효율성을 크게 향상시킵니다. 이 가이드의 규칙과 패턴을 따르면 유지보수가 쉽고 확장 가능한 관리자 시스템을 구현할 수 있습니다.

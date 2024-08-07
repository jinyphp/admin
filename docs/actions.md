# actions
`MVC`패턴을 활용하는 프레임워크에서 전체적인 로직은 Controller에서 처리됩니다.
컨트롤러는 처리과정에서 View와 강력한 결합관계가 형성이 됩니다.

## Actions 이란?
`Actions`은 지니PHP의 고유한 기능으로 기존 MVC 패턴에서 controller를 좀더 유연하게 동작 처리를 하기 위한 외부 파라미터 입니다.
> Actions은 파라미터 코딩을 지원합니다.

보통 컨트롤러는 처리된 결과 로직을 화면으로 반환하기 위하여 View와 강력한 결합을 유지하게 됩니다. 또한, 동작 로직도 외부 변수로 흐름을 변경할 수 있도록 하는 역할도 수행합니다.
> `actions`의 데이터 타입은 배열 입니다..

## 외부 설정파일
actions는 `resources/actions`폴더안에 json 파일로 저장됩니다. 
> 파일명은 uri이름으로 작성이 됩니다.

### 자동로드
지니PHP는 기존 라라벨의 controller를 확장한 여러 종류의 컨트롤러를 제공합니다. 이 컨트롤러는 uri를 분석하여 이와 매칭되는 actions의  json 설정값을 자동으로 로드 합니다.

```php
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class SitePostTable extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## actions 기본설정 동작처리
        $this->setActions();
    }

}
```

현재의 actions값을 확인하고 싶으면 `dd($this->actions)` 코드를 삽입하여 디버깅해볼수 있습니다.

### 기본값 설정
자동 로드된 `actions`에 추가 설정 기본값을 주입할 수 있습니다. 다음은 기본설정을 위하여 추가한 메소드 입 예제 입니다.

```php
private function setActions()
{
    ## 테이블 정보
    $actions['table'] = "site_posts";

    $actions['title'] = "포스트";
    $actions['subtitle'] = "작성된 포스트를 관리합니다.";

    $actions['view']['list'] = "jiny-site-board::site.post.list";
    $actions['view']['form'] = "jiny-site-board::site.post.form";

    // 레이아웃을 커스텀 변경합니다.
    $actions['view']['layout'] = "jiny-site-board::site.post.layout";

    // 테이블을 커스텀 변경합니다.
    $actions['view']['table'] = "jiny-site-board::site.post.table";

    $this->setReflectActions($actions);
}
```

`$this->setReflectActions($actions);` 메소드는 자동 로드된 actions 값을 우선으로 적용하여, 값이 없는 경우에만 지정한 값이 병합됩니다.

## ui 설정
actions을 보다 쉽게 설정 및 변경할 수 있도록 라이브와이어 컴포넌트를 제공합니다.

```php
@livewire('setActionRule', [
    'actions'=>$actions
])
```

actions의 json을 팝업 형태로 수정할 수 있습니다.


## 동적 페이지에 대한 actions
actions은 컨트롤러에 전달된 값을 기반으로 로직을 처리하는 파리미터 코딩 기법입니다. 하지만, 동적으로 처리되는 페이지에는 컨트롤러가 존재하지 않습니다. 이런경우 파라미터는 json 데이터로 페이지의 변수값으로 활용이 될 수 있습니다.
> actions 값을 주입하기 위해서는 화면 리소스가 blade 타입이어야 합니다.

이렇게 주입된 파라미터는 `$actions['key']`형태로 페이지에 삽입을 할 수 있습니다.

### 커스텀 json 데이터 생성
동적 페이지는 정해진 규격의 json 데이터가 아닌 자유로운 형식으로 파라미터를 생성하여 전달 할 수 있습니다.

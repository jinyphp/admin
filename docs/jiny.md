# jiny 커멘드
jinyPHP의 유지보수 및 개발을 위한 몇가지 artisan 명령을 제공합니다.

## 패키지 관리
jinyPHP는 대부분의 기능들의 확장 페키지로 개발되어 있습니다. 또한, 확장 페키지는 컴포저로 설치되며, 위치는`/vendor/jin/*` 안에 존재합니다. 

### 페키지 깃으로 유지보수 변경하기
`/vendor/jin/*` 있는 페키지를 삭제 또는 다른 이름으로 변경하고, 지니PHP 깃허브 저장소를 바로 clone 할 수도 있습니다.

```php
mv 페키지 _페키지
git clone https://github.com/jinyphp/페키지.git
```

### 자동 git pull
`/vendor/jin/*` 안에 있는 페키지들이 git으로 clone이 되어 있는 경우, 한번에 pull 명령을 실행할 수 있습니다.
> git 기능을 사용하기 위해서는 `composer require czproject/git-php` 설치가 필요합니다.

```bash
# 페키지 전체를 pull 합니다.
php artisan jiny:pull
```

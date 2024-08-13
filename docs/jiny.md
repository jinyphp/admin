# jiny 커멘드

## 지니 페키지 git pull
`/vendor/jin/*` 안에 있는 페키지들이 git으로 clone이 되어 있는 경우, 한번에 pull 명령을 실행할 수 있습니다.
> git 기능을 사용하기 위해서는 `composer require czproject/git-php` 설치가 필요합니다.
```bash
# 페키지 전체를 pull 합니다.
php artisan jiny:pull
```

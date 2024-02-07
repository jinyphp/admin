# Jiny Admin 
JinyPHP `Admin` 관리자 입니다.  
라라벨은 기본적으로 `admin` 관리 기능을 제공하지 않습니다. 하지만, 본 패키지를 설치하게 되면 보다 손쉽게 Admin 백엔드 사이트를 구현할 수 있으며, 개발된 서비스의 관리를 보다 쉽게 개발할 수 있습니다. 

## 설치
컴포저를 이용하여 패키지를 설치합니다.
```
composer require jiny/admin
```

> Admin 기능을 설치하기 위해서는 사용자 계정을 관리할 수 있는 auth 패키지와 데이터베이스가 필요로 합니다.


## 라라벨 설치
Jiny Projects 는 laravel을 기반으로 동작하는 응용 패키지입니다. 이를 사용하기 위해서는 먼저 laravel을 설치해 주셔야 합니다.
콘솔에서 composer를 통하여 다음과 같이 입력하여 설치 가능합니다.

```
composer create-project laravel/laravel example-app
```

> 보다 자세한 방법은 공식문서 https://laravel.com/docs/10.x 를 확인합니다.


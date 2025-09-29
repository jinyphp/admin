<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Module')</title>

    @stack('styles')
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>

    <!-- Alpine.js CDN for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
</head>
{{--
    중앙 정렬 레이아웃 템플릿
    - Admin 모듈의 기본 레이아웃
    - 화면 중앙에 최대 너비 md(28rem)의 콘텐츠 영역 제공
    - 다크 모드 지원 (bg-gray-100 dark:bg-gray-900)
    - 전체 화면 높이(min-h-screen)를 사용하여 수직 중앙 정렬
--}}
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center"
    data-page="@yield('script-state')">
    <div class="w-full max-w-md">

        @yield('content')

    </div>

    @stack('scripts')
</body>
</html>
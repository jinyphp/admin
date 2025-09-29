<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    @livewireStyles
</head>
<body>
    <main>
        @yield('content')
    </main>
    @livewireScripts
</body>
</html>
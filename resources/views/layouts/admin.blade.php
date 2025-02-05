<!DOCTYPE html>
<!--
  HOW TO USE:
  data-layout: fluid (default), boxed
  data-sidebar-theme: dark (default), colored, light
  data-sidebar-position: left (default), right
  data-sidebar-behavior: sticky (default), fixed, compact
-->
<html lang="en" data-bs-theme="light" data-layout="fluid" data-sidebar-theme="dark" data-sidebar-position="left"
    data-sidebar-behavior="sticky">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Bootstrap 5 Admin &amp; Dashboard Template">
    <meta name="author" content="Bootlab">

    <title>
        @if (isset($seo_title))
            {{ $seo_title }}
        @endif
    </title>

    <link rel="canonical" href="https://appstack.bootlab.io/dashboard-default.html" />
    <link rel="shortcut icon" href="img/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    @vite('resources/css/admin/app.scss')
    @vite('resources/css/tailwind.scss')
    @vite('resources/css/jiny.scss')
    @stack('css')

</head>

<body>
    <div class="wrapper">

        {{-- 사이드바 --}}
        {{-- <x-admin-sidebar></x-admin-sidebar> --}}
        @includeIf("jiny-admin::layouts.sidebar")

        <div class="main">

            <x-admin-header></x-admin-header>

            <main class="content">
                <div class="container-fluid p-0">
                    {{ $slot }}
                </div>
            </main>

            <x-admin-footer>

            </x-admin-footer>
        </div>
    </div>

    {{-- <script src="https://jinyphp.github.io/css/assets/js/app.js" defer></script> --}}
    {{-- <script src="js/app.js"></script> --}}
    @vite('resources/js/admin/app.js')


    @stack('script')

    <x-set-actions></x-set-actions>

    {{-- HotKey 단축키 이벤트 --}}
    @livewire('HotKeyEvent')

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('page-realod', (event) => {
                console.log("page-realod");
                location.reload();
            });

            Livewire.on('history-back', (event) => {
                console.log("history-back");
                history.back();
            });
        });
    </script>


</body>

</html>

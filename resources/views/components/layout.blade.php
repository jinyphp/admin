<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>JinyPHP Admin</title>
    {{-- @vite('resources/css/app.css') --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.4387550e.css') }}"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('css')
    @livewireStyles
</head>

<body>

    <!-- Page Container -->
    <div id="page-container"
        x-data="{ userDropdownOpen: false, mobileSidebarOpen: false, desktopSidebarOpen: true }"
        class="flex flex-col mx-auto w-full min-h-screen bg-gray-100"
        x-bind:class="{
            'lg:pl-64': desktopSidebarOpen
        }">

        <!-- Page Sidebar -->
        @include('jiny-admin::layouts.sidebar')
        <!-- Page Sidebar -->

        <!-- Page Header -->
        @include('jiny-admin::layouts.header')
        <!-- END Page Header -->

        <!-- Page Content -->
        <main id="page-content" class="flex flex-auto flex-col max-w-full pt-16">

            <!-- Page Section -->
            <div>
                {{$slot}}
            </div>
            <!-- END Page Section -->
        </main>
        <!-- END Page Content -->

        <!-- Page Footer -->
        @include('jiny-admin::layouts.footer')
        <!-- END Page Footer -->
    </div>
    <!-- END Page Container -->



    @livewireScripts
    @stack('scripts')
</body>

</html>

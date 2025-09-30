<!DOCTYPE html>
<html lang="ko" class="h-full bg-white dark:bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel') . ' Admin')</title>

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

    <!-- Tailwind Plus Elements CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    @yield('head')
    @stack('styles')
</head>
<body class="h-full bg-gray-100">
    @php
        $currentRoute = request()->route()->getName();
    @endphp

    <!-- Mobile sidebar dialog -->
    <el-dialog>
        <dialog id="sidebar" class="backdrop:bg-transparent lg:hidden">
            <el-dialog-backdrop class="fixed inset-0 bg-gray-900/80 transition-opacity duration-300 ease-linear data-closed:opacity-0"></el-dialog-backdrop>

            <div tabindex="0" class="fixed inset-0 flex focus:outline-none">
                <el-dialog-panel class="group/dialog-panel relative mr-16 flex w-full max-w-xs flex-1 transform transition duration-300 ease-in-out data-closed:-translate-x-full">
                    <div class="absolute top-0 left-full flex w-16 justify-center pt-5 duration-300 ease-in-out group-data-closed/dialog-panel:opacity-0">
                        <button type="button" command="close" commandfor="sidebar" class="-m-2.5 p-2.5">
                            <span class="sr-only">Close sidebar</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 text-white">
                                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Sidebar content -->
                    <div class="relative flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-2 ring-1 ring-white/10 dark:before:pointer-events-none dark:before:absolute dark:before:inset-0 dark:before:bg-black/10">
                        <div class="relative flex h-16 shrink-0 items-center">
                            <span class="text-xl font-bold text-white">{{ config('app.name', 'JINYPHP') }}</span>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                @include('jiny-admin::layouts.partials.menu-items', ['mobile' => true])
                            </ul>
                        </nav>
                    </div>
                </el-dialog-panel>
            </div>
        </dialog>
    </el-dialog>

    <!-- Static sidebar for desktop -->
    <div class="hidden bg-gray-900 lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 px-6 dark:border-white/10 dark:bg-black/10">
            <div class="flex h-16 shrink-0 items-center">
                <span class="text-xl font-bold text-white">{{ config('app.name', 'JINYPHP') }}</span>
            </div>
            <nav class="flex flex-1 flex-col">
                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    @include('jiny-admin::layouts.partials.menu-items')
                </ul>
            </nav>
        </div>
    </div>

    <!-- Mobile header -->
    <div class="sticky top-0 z-40 flex items-center gap-x-6 bg-gray-900 px-4 py-4 shadow-sm sm:px-6 lg:hidden dark:shadow-none dark:after:pointer-events-none dark:after:absolute dark:after:inset-0 dark:after:border-b dark:after:border-white/10 dark:after:bg-black/10">
        <button type="button" command="show-modal" commandfor="sidebar" class="-m-2.5 p-2.5 text-gray-400 hover:text-white lg:hidden">
            <span class="sr-only">Open sidebar</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <div class="flex-1 text-sm/6 font-semibold text-white">@yield('title', 'Dashboard')</div>
        @if(Auth::check())
        <a href="{{ route('admin.system.users.show', Auth::id()) }}">
            <span class="sr-only">Your profile</span>
            @if(Auth::user()->avatar && Auth::user()->avatar !== '/images/default-avatar.png')
                <img src="{{ Auth::user()->avatar }}"
                     alt="{{ Auth::user()->name }}"
                     class="size-8 rounded-full object-cover outline -outline-offset-1 outline-white/10"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'size-8 rounded-full bg-gray-800 flex items-center justify-center text-white text-sm font-medium outline -outline-offset-1 outline-white/10\'>{{ mb_strtoupper(mb_substr(Auth::user()->name ?? Auth::user()->email, 0, 1)) }}</div>';">
            @else
                @php
                    $initial = mb_strtoupper(mb_substr(Auth::user()->name ?? Auth::user()->email ?? '?', 0, 1));
                    $colors = ['bg-red-600', 'bg-yellow-600', 'bg-green-600', 'bg-blue-600', 'bg-indigo-600', 'bg-purple-600', 'bg-pink-600'];
                    $colorIndex = crc32(Auth::user()->name ?? Auth::user()->email) % count($colors);
                    $bgColor = $colors[$colorIndex];
                @endphp
                <div class="size-8 rounded-full {{ $bgColor }} flex items-center justify-center text-white text-sm font-medium outline -outline-offset-1 outline-white/10">
                    {{ $initial }}
                </div>
            @endif
        </a>
        @endif
    </div>

    <!-- Main content -->
    <main class="py-10 lg:pl-72  bg-gray-100">
        <div class="px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>
    @livewireScripts

    <script>
        // Livewire 리다이렉트 이벤트 처리
        document.addEventListener('livewire:init', () => {
            Livewire.on('redirect-with-replace', (event) => {
                if (event.url) {
                    window.location.replace(event.url);
                }
            });

            // 페이지 새로고침 이벤트
            Livewire.on('refresh-page', () => {
                window.location.reload();
            });

            // 첫 번째 필드에 포커스
            Livewire.on('focus-first-field', () => {
                setTimeout(() => {
                    const firstInput = document.querySelector('input:not([type="hidden"]):not([disabled])');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 100);
            });

            // 성공 하이라이트
            Livewire.on('highlight-success', () => {
                const successMsg = document.querySelector('.bg-green-100');
                if (successMsg) {
                    successMsg.classList.add('animate-pulse');
                    setTimeout(() => {
                        successMsg.classList.remove('animate-pulse');
                    }, 2000);
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>

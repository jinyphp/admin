<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Admin Portal</title>

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

<body class="bg-gray-50 dark:bg-gray-900 antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                    관리자 포털
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    관리하려는 영역을 선택하세요
                </p>
                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        로그인: <span class="font-semibold">{{ Auth::user()->email }}</span>
                    </p>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($cards as $card)
                @php
                    $gradientClass = '';
                    $hoverClass = '';
                    $focusClass = '';

                    if ($card['id'] == 'system') {
                        $gradientClass = 'bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700';
                        $focusClass = 'focus:ring-indigo-500';
                    } elseif ($card['id'] == 'cms') {
                        $gradientClass = 'bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700';
                        $focusClass = 'focus:ring-green-500';
                    } elseif ($card['id'] == 'store') {
                        $gradientClass = 'bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700';
                        $focusClass = 'focus:ring-orange-500';
                    } elseif ($card['id'] == 'erp') {
                        $gradientClass = 'bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700';
                        $focusClass = 'focus:ring-purple-500';
                    }
                @endphp
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg hover:shadow-2xl transition-shadow duration-300 h-full">
                    <div class="p-8 h-full flex flex-col">
                        <div class="flex items-center justify-center mb-6">
                            <div class="p-4 {{ $gradientClass }} rounded-full">
                                <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon']['path'] }}"></path>
                                </svg>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-3">
                            {{ $card['title'] }}
                        </h3>

                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                            {{ $card['description'] }}
                        </p>

                        <ul class="space-y-2 mb-6 text-xs text-gray-600 dark:text-gray-400 flex-grow">
                            @foreach($card['features'] as $feature)
                            <li class="flex items-center">
                                <svg class="h-3.5 w-3.5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>

                        <div class="mt-auto">
                            @if(str_starts_with($card['route'], 'admin.'))
                                <a href="{{ route($card['route']) }}"
                            @else
                                <a href="{{ $card['route'] }}"
                            @endif
                               class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white {{ $gradientClass }} focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $focusClass }} transition duration-200">
                                {{ $card['buttonText'] }}
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Quick Links -->
            <div class="mt-8 text-center">
                <div class="flex items-center justify-center space-x-6 text-sm">
                    <a href="{{ route('admin.system.users') }}" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        사용자 관리
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <a href="{{ route('admin.system.mail.setting') }}" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        메일 설정
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <a href="{{ route('admin.system.security.ip-whitelist') }}" class="text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        보안 설정
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition text-sm">
                            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            로그아웃
                        </button>
                    </form>
                </div>
            </div>

            <!-- Copyright Footer -->
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                <div class="text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        Powered by <span class="font-semibold">JinyPHP Admin</span> v1.0.0
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dark Mode Toggle (Optional) -->
    <div class="fixed bottom-4 right-4">
        <button onclick="document.documentElement.classList.toggle('dark')"
                class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
        </button>
    </div>

    @livewireScripts
</body>

</html>
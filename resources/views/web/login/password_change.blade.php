<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - 비밀번호 변경</title>
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

    @livewire('jiny-admin::admin-notification')

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-sm w-full">
            <div class="text-center mb-6">
                <div
                    class="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900 mb-3">
                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">비밀번호 변경</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    @if($required)
                        비밀번호가 만료되었습니다. 새 비밀번호를 설정해주세요.
                    @else
                        보안을 위해 새 비밀번호를 설정해주세요.
                    @endif
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                {{-- 사용자 정보 표시 --}}
                @auth
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <div class="text-xs">
                                    <span class="text-gray-600 dark:text-gray-400">로그인 계정:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                            @if(auth()->user()->password_changed_at)
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">마지막 변경:</span>
                                        <span class="font-medium text-gray-900 dark:text-white ml-1">{{ auth()->user()->password_changed_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endif
                            @if(auth()->user()->password_expires_at)
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 {{ now()->greaterThan(auth()->user()->password_expires_at) ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400' }} mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">만료일:</span>
                                        <span class="font-medium {{ now()->greaterThan(auth()->user()->password_expires_at) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} ml-1">
                                            {{ auth()->user()->password_expires_at->format('Y-m-d') }}
                                            @if(now()->greaterThan(auth()->user()->password_expires_at))
                                                (만료됨)
                                            @else
                                                ({{ auth()->user()->password_expires_at->diffForHumans() }})
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="text-xs text-yellow-700 dark:text-yellow-300">
                                로그인이 필요합니다. 로그인 페이지로 이동합니다...
                            </div>
                        </div>
                    </div>
                @endauth

                @if ($errors->any())
                    <div
                        class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-xs">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-2">
                                <h3 class="font-medium text-red-800 dark:text-red-400">비밀번호 변경 실패</h3>
                                <div class="mt-1 text-red-700 dark:text-red-500">
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.password.change.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_password"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">현재 비밀번호</label>
                        <input type="password" id="current_password" name="current_password"
                            class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white @error('current_password') border-red-500 dark:border-red-500 @enderror"
                            placeholder="현재 비밀번호 입력" autocomplete="current-password" required>
                    </div>

                    <div>
                        <label for="password"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">새 비밀번호</label>
                        <input type="password" id="password" name="password"
                            class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white @error('password') border-red-500 dark:border-red-500 @enderror"
                            placeholder="새 비밀번호 입력" autocomplete="new-password" required>
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">새 비밀번호 확인</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="새 비밀번호 확인" autocomplete="new-password" required>
                    </div>

                    <div class="mt-3 p-2.5 bg-gray-50 dark:bg-gray-900/50 rounded text-[10px]">
                        <div class="text-gray-500 dark:text-gray-400">
                            <p class="font-medium mb-1">비밀번호 요구사항:</p>
                            <ul class="list-disc pl-3.5 space-y-0.5 text-gray-400 dark:text-gray-500">
                                <li>최소 8자 이상</li>
                                <li>대문자와 소문자 포함</li>
                                <li>숫자 포함</li>
                                <li>특수문자 포함</li>
                                <li>최근 사용한 비밀번호 재사용 불가</li>
                            </ul>
                            <p class="mt-1.5 text-gray-400 dark:text-gray-500">
                                비밀번호 변경 기록은 보안을 위해 저장됩니다.
                            </p>
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit"
                            class="flex-1 h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:ring-offset-1 transition duration-200">
                            비밀번호 변경
                        </button>
                        @if(!$required)
                            <a href="{{ route('admin.system.dashboard') }}"
                                class="flex-1 h-8 px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-gray-500 focus:ring-offset-1 transition duration-200 flex items-center justify-center">
                                나중에 변경
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="mt-6 text-xs text-gray-400 text-center">
                <p>비밀번호는 정기적으로 변경하시기 바랍니다.</p>
                <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
            </div>

        </div>
    </div>

    @livewireScripts
    <script>
        // 비밀번호 강도 체크 (선택적)
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');

            if (passwordInput && passwordConfirmInput) {
                passwordConfirmInput.addEventListener('input', function() {
                    if (this.value !== passwordInput.value) {
                        this.setCustomValidity('비밀번호가 일치하지 않습니다.');
                    } else {
                        this.setCustomValidity('');
                    }
                });

                passwordInput.addEventListener('input', function() {
                    if (passwordConfirmInput.value && this.value !== passwordConfirmInput.value) {
                        passwordConfirmInput.setCustomValidity('비밀번호가 일치하지 않습니다.');
                    } else {
                        passwordConfirmInput.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</body>

</html>
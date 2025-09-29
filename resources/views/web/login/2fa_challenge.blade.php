<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - 2차 인증</title>
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
                <div class="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 mb-3">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">2차 인증</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Google Authenticator 앱에서 6자리 코드를 입력해주세요
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                @if(session('error'))
                    <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-xs">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" 
                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" 
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="font-medium text-red-800 dark:text-red-400">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-xs">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" 
                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" 
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-2">
                                <h3 class="font-medium text-red-800 dark:text-red-400">인증 오류</h3>
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

                <form action="{{ route('admin.2fa.verify') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            인증 코드
                        </label>
                        <input type="text" 
                               id="code" 
                               name="code"
                               class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-center font-mono tracking-wider @error('code') border-red-500 dark:border-red-500 @enderror"
                               placeholder="000000"
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               autocomplete="off"
                               autofocus
                               required>
                        @error('code')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:ring-offset-1 transition duration-200">
                        인증하기
                    </button>

                    <div class="text-center space-y-2 pt-2">
                        <button type="button" 
                                onclick="showBackupCodeModal()" 
                                class="text-xs text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                            백업 코드 사용
                        </button>
                        
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('admin.logout') }}" 
                               class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                다른 계정으로 로그인
                            </a>
                        </div>
                    </div>
                </form>

                @if(session()->has('2fa_user_email'))
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        <p class="font-medium">접속 계정</p>
                        <p class="text-[10px]">{{ session('2fa_user_email') }}</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="mt-6 text-xs text-gray-400 text-center">
                <p>2차 인증으로 계정을 안전하게 보호하세요.</p>
                <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- 백업 코드 모달 -->
    <div id="backupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-4 border w-80 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-2">
                <div class="flex items-center mb-3">
                    <div class="mx-auto flex items-center justify-center h-8 w-8 rounded-full bg-yellow-100 dark:bg-yellow-900/20">
                        <svg class="h-4 w-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                </div>
                
                <h3 class="text-xs font-medium text-gray-900 dark:text-white text-center mb-1">백업 코드 입력</h3>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 text-center mb-3">
                    백업 코드 중 하나를 입력해주세요
                </p>
                
                <form action="{{ route('admin.2fa.verify') }}" method="POST">
                    @csrf
                    <input type="hidden" name="use_backup" value="1">
                    
                    <div class="mb-3">
                        <input type="text" 
                               id="backup_code" 
                               name="code" 
                               class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-center font-mono tracking-wider"
                               placeholder="XXXX-XXXX" 
                               pattern="[A-Z0-9]{4}-[A-Z0-9]{4}"
                               required>
                        <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">
                            백업 코드는 대문자와 숫자로 구성됩니다
                        </p>
                    </div>
                    
                    <div class="flex justify-center space-x-2">
                        <button type="button" 
                                onclick="hideBackupCodeModal()"
                                class="h-7 px-3 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs font-medium hover:bg-gray-300 dark:hover:bg-gray-500">
                            취소
                        </button>
                        <button type="submit"
                                class="h-7 px-3 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700">
                            확인
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @livewireScripts
    <script>
    function showBackupCodeModal() {
        document.getElementById('backupModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('backup_code').focus();
        }, 100);
    }

    function hideBackupCodeModal() {
        document.getElementById('backupModal').classList.add('hidden');
    }

    // 모달 외부 클릭 시 닫기
    document.getElementById('backupModal').addEventListener('click', function(event) {
        if (event.target === this) {
            hideBackupCodeModal();
        }
    });

    // 자동 포커스
    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('code');
        if (codeInput) {
            codeInput.focus();
        }

        // 6자리 숫자만 입력 허용
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });

        // 백업 코드 입력 필드 포맷
        const backupInput = document.getElementById('backup_code');
        if (backupInput) {
            backupInput.addEventListener('input', function(e) {
                let value = this.value.replace(/[^A-Z0-9]/g, '').toUpperCase();
                if (value.length > 4) {
                    value = value.substring(0, 4) + '-' + value.substring(4, 8);
                }
                this.value = value;
            });
        }
    });

    // 에러 메시지가 있으면 notification으로 표시
    @if ($errors->any())
        window.addEventListener('livewire:init', () => {
            @foreach ($errors->all() as $error)
                Livewire.dispatch('notifyError', { message: '{{ $error }}', title: '인증 오류' });
            @endforeach
        });
    @endif
    </script>
</body>

</html>
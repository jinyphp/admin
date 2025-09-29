<!DOCTYPE html>
<html lang="ko" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 비밀번호 찾기</title>
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
<body class="h-full">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">

            <div class="text-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">비밀번호 재설정 안내</h3>
            </div>

            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="text-center">
                    <div class="mt-4 text-sm text-gray-600">
                        <p>관리자 계정은 보안상의 이유로</p>
                        <p>일반적인 비밀번호 찾기 기능을 제공하지 않습니다.</p>
                    </div>
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <p class="text-sm font-medium text-yellow-800">
                            관리자에게 문의해 주세요
                        </p>
                        <p class="mt-2 text-sm text-yellow-700">
                            시스템 관리자에게 연락하여<br>
                            비밀번호 재설정을 요청하시기 바랍니다.
                        </p>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('admin.login') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            로그인 페이지로 돌아가기
                        </a>
                    </div>
                </div>
            </div>

             <div class="mt-6 text-xs text-gray-400 text-center">
                <p>본 로그인은 관리자 전용입니다. 무단 사용 시 법적 처벌을 받을 수 있습니다.</p>
                <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>

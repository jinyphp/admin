<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Admin Login</title>
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
    
    @php
        $captchaManager = app(\Jiny\Admin\Services\Captcha\CaptchaManager::class);
        $showCaptcha = $captchaManager->isRequired(
            old('email'),
            request()->ip()
        );
    @endphp
    
    @if($showCaptcha && config('admin.setting.captcha.enabled'))
        @php
            try {
                $captchaDriver = $captchaManager->driver();
                echo $captchaDriver->getScript();
            } catch (\Exception $e) {
                // CAPTCHA 설정 오류 처리
            }
        @endphp
    @endif
</head>

<body class="bg-gray-50 dark:bg-gray-900 antialiased">
    <script>
        // 419 오류 시 페이지 새로고침
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // CSRF 토큰이 없거나 만료된 경우 페이지 새로고침
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfToken || !csrfToken.content) {
                        e.preventDefault();
                        window.location.reload();
                    }
                });
            }
            
            // 페이지 로드 시 CSRF 토큰 확인
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta || !csrfMeta.content) {
                // CSRF 토큰이 없으면 페이지 새로고침
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        });
    </script>

    @livewire('jiny-admin::admin-notification')

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="text-center mb-6">
                <div
                    class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-3">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">관리자 로그인</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    관리자 계정으로 로그인하세요
                </p>
            </div>



            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                @if (session('message'))
                    <div
                        class="mb-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded text-sm text-green-800 dark:text-green-400">
                        {{ session('message') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-2">
                                <h3 class="font-medium text-red-800 dark:text-red-400">로그인 실패</h3>
                                <div class="mt-1 text-red-700 dark:text-red-500">
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                            @if($error == 'The CSRF token has expired.')
                                                <li>세션이 만료되었습니다. 페이지를 새로고침 해주세요.</li>
                                                <script>
                                                    setTimeout(() => {
                                                        window.location.reload();
                                                    }, 3000);
                                                </script>
                                            @else
                                                <li>{{ $error }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">이메일</label>
                        <input type="email" id="email" name="email"
                            class="block w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white @error('email') border-red-500 dark:border-red-500 @enderror"
                            placeholder="admin@example.com" value="{{ old('email') }}" autocomplete="email" required>
                    </div>

                    <div>
                        <label for="password"
                            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">비밀번호</label>
                        <input type="password" id="password" name="password"
                            class="block w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white @error('password') border-red-500 dark:border-red-500 @enderror"
                            placeholder="••••••••" autocomplete="current-password" required>
                    </div>

                    @if($showCaptcha && config('admin.setting.captcha.enabled'))
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">보안 인증</label>
                            @php
                                try {
                                    $captchaDriver = $captchaManager->driver();
                                    $captchaHtml = $captchaDriver->render([
                                        'theme' => 'light',
                                        'size' => 'normal'
                                    ]);
                                    echo $captchaHtml;
                                } catch (\Exception $e) {
                                    echo '<div class="text-sm text-red-600 dark:text-red-400">CAPTCHA 설정 오류: ' . e($e->getMessage()) . '</div>';
                                }
                            @endphp
                            @error('captcha')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($showCaptcha && config('admin.setting.captcha.enabled'))
                        <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded text-xs">
                            <div class="flex items-start">
                                <svg class="h-4 w-4 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="text-yellow-800 dark:text-yellow-300">
                                    <p class="font-medium">보안 인증이 필요합니다</p>
                                    <p class="mt-0.5 text-yellow-700 dark:text-yellow-400">
                                        여러 번의 로그인 실패가 감지되었습니다. 보안을 위해 CAPTCHA 인증을 완료해주세요.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded text-xs">
                        <div class="text-gray-500 dark:text-gray-400">
                            <p class="font-medium mb-1">현재 접속 정보:</p>
                            <ul class="list-disc pl-3.5 space-y-0.5 text-gray-400 dark:text-gray-500">
                                <li>IP 주소: {{ request()->ip() }}</li>
                                @php
                                    $userAgent = request()->header('User-Agent');
                                    $browser = 'Unknown';
                                    $platform = 'Unknown';

                                    // 간단한 플랫폼 감지
                                    if (stripos($userAgent, 'windows') !== false) $platform = 'Windows';
                                    elseif (stripos($userAgent, 'mac') !== false) $platform = 'Mac OS';
                                    elseif (stripos($userAgent, 'linux') !== false) $platform = 'Linux';
                                    elseif (stripos($userAgent, 'android') !== false) $platform = 'Android';
                                    elseif (stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false) $platform = 'iOS';

                                    // 간단한 브라우저 감지
                                    if (stripos($userAgent, 'firefox') !== false) $browser = 'Firefox';
                                    elseif (stripos($userAgent, 'edge') !== false) $browser = 'Edge';
                                    elseif (stripos($userAgent, 'chrome') !== false) $browser = 'Chrome';
                                    elseif (stripos($userAgent, 'safari') !== false) $browser = 'Safari';
                                    elseif (stripos($userAgent, 'opera') !== false || stripos($userAgent, 'opr') !== false) $browser = 'Opera';

                                    // 언어 파싱
                                    $acceptLanguage = request()->header('Accept-Language', 'ko-KR');
                                    $languages = explode(',', $acceptLanguage);
                                    $primaryLang = isset($languages[0]) ? explode(';', $languages[0])[0] : 'ko-KR';
                                    $langParts = explode('-', $primaryLang);
                                    $langCode = $langParts[0];
                                    $countryCode = isset($langParts[1]) ? $langParts[1] : '';

                                    $langNames = [
                                        'ko' => '한국어',
                                        'en' => 'English',
                                        'ja' => '日本語',
                                        'zh' => '中文',
                                        'es' => 'Español',
                                        'fr' => 'Français',
                                        'de' => 'Deutsch',
                                        'ru' => 'Русский',
                                    ];
                                    $langDisplay = isset($langNames[$langCode]) ? $langNames[$langCode] : strtoupper($langCode);
                                @endphp
                                <li>브라우저: {{ $browser }} ({{ $platform }})</li>
                                <li>접속 시간: {{ now()->format('Y-m-d H:i:s') }}</li>
                                <li>접속 프로토콜: {{ request()->secure() ? 'HTTPS (보안)' : 'HTTP' }}</li>
                                <li>언어/지역: {{ $langDisplay }} {{ $countryCode ? '(' . strtoupper($countryCode) . ')' : '' }}</li>
                                <li>세션 ID: {{ substr(session()->getId(), 0, 8) }}...</li>
                            </ul>
                            <p class="mt-2 text-gray-400 dark:text-gray-500">
                                모든 접속 기록은 보안을 위해 저장되며, 불법적인 접근 시도는 차단됩니다.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-1 dark:bg-gray-700 dark:border-gray-600">
                            <label for="remember" class="ml-2 text-sm text-gray-900 dark:text-gray-300">
                                로그인 상태 유지
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="{{ route('admin.password.forgot') }}"
                                class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                비밀번호를 잊으셨나요?
                            </a>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full h-10 px-6 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                        로그인
                    </button>
                </form>
            </div>

            <div class="mt-6 text-sm text-gray-400 text-center">
                <p>본 로그인은 관리자 전용입니다. 무단 사용 시 법적 처벌을 받을 수 있습니다.</p>
                <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
            </div>



        </div>
    </div>

    @livewireScripts
    <script>
        // 에러 메시지가 있으면 notification으로 표시
        @if ($errors->any())
            window.addEventListener('livewire:init', () => {
                @foreach ($errors->all() as $error)
                    Livewire.dispatch('notifyError', { message: '{{ $error }}', title: '로그인 실패' });
                @endforeach
            });
        @endif

        // 성공 메시지가 있으면 notification으로 표시
        @if (session('message'))
            window.addEventListener('livewire:init', () => {
                Livewire.dispatch('notifySuccess', {
                    message: '{{ session('message') }}',
                    title: '알림'
                });
            });
        @endif
    </script>
</body>

</html>

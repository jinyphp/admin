{{-- 
    2FA 설정 페이지
    Google Authenticator를 사용한 2차 인증 설정
--}}
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">2차 인증(2FA) 설정</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Google Authenticator 앱을 사용하여 계정의 보안을 강화합니다.
            </p>
        </div>

        {{-- 사용자 정보 --}}
        @if(isset($user))
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">사용자 정보</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">이름:</span>
                    <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ $user->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">이메일:</span>
                    <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ $user->email }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- 설정 단계 --}}
        <div class="space-y-6">
            {{-- Step 1: 앱 설치 --}}
            <div class="border-l-4 border-blue-500 pl-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    1단계: Google Authenticator 앱 설치
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    스마트폰에 Google Authenticator 앱을 설치하세요.
                </p>
                <div class="flex space-x-4">
                    <a href="https://apps.apple.com/app/google-authenticator/id388497605" 
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2C5.58 2 2 5.58 2 10s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/>
                        </svg>
                        iOS App Store
                    </a>
                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" 
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2C5.58 2 2 5.58 2 10s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/>
                        </svg>
                        Google Play Store
                    </a>
                </div>
            </div>

            @if(isset($qrCodeImage) && isset($secret))
            {{-- Step 2: QR 코드 스캔 --}}
            <div class="border-l-4 border-blue-500 pl-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    2단계: QR 코드 스캔
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Google Authenticator 앱에서 아래 QR 코드를 스캔하거나 수동으로 키를 입력하세요.
                </p>
                
                <div class="grid md:grid-cols-2 gap-6">
                    {{-- QR 코드 --}}
                    <div class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-900 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                        <img src="{{ $qrCodeImage }}" alt="QR Code" class="w-48 h-48">
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">QR 코드를 스캔하세요</p>
                    </div>
                    
                    {{-- 수동 입력 --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">수동 입력</h4>
                        <div class="space-y-3">
                            @if(isset($user))
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">계정 이름</label>
                                <div class="flex items-center">
                                    <input type="text" value="{{ $user->email }}" readonly
                                           class="flex-1 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-gray-900 dark:text-white">
                                    <button type="button" onclick="copyToClipboard('{{ $user->email }}')"
                                            class="ml-2 p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endif
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">비밀 키</label>
                                <div class="flex items-center">
                                    <input type="text" value="{{ $secret }}" readonly
                                           class="flex-1 text-sm font-mono bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-gray-900 dark:text-white">
                                    <button type="button" onclick="copyToClipboard('{{ $secret }}')"
                                            class="ml-2 p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($backupCodes))
            {{-- Step 3: 백업 코드 --}}
            <div class="border-l-4 border-yellow-500 pl-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    3단계: 백업 코드 저장
                </h3>
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">중요: 백업 코드를 안전한 곳에 보관하세요</h4>
                            <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">
                                휴대폰을 분실하거나 Google Authenticator 앱에 접근할 수 없을 때 이 코드를 사용하여 로그인할 수 있습니다.
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach($backupCodes as $code)
                            <div class="bg-white dark:bg-gray-800 px-3 py-2 rounded border border-yellow-300 dark:border-yellow-600 text-center">
                                <code class="text-sm font-mono text-gray-900 dark:text-white">{{ $code }}</code>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 flex space-x-2">
                        <button type="button" onclick="downloadBackupCodes()"
                                class="inline-flex items-center px-3 py-1.5 border border-yellow-300 dark:border-yellow-600 rounded-md text-xs font-medium text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            다운로드
                        </button>
                        <button type="button" onclick="printBackupCodes()"
                                class="inline-flex items-center px-3 py-1.5 border border-yellow-300 dark:border-yellow-600 rounded-md text-xs font-medium text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            인쇄
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Step 4: 인증 코드 확인 --}}
            <div class="border-l-4 border-green-500 pl-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    4단계: 설정 확인
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Google Authenticator 앱에 표시된 6자리 인증 코드를 입력하여 설정을 완료하세요.
                </p>
                
                <form wire:submit.prevent="verify2FA" class="max-w-sm">
                    <div class="mb-4">
                        <label for="verification_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            인증 코드
                        </label>
                        <input type="text" 
                               id="verification_code" 
                               wire:model="verification_code"
                               maxlength="6" 
                               pattern="[0-9]{6}"
                               placeholder="000000"
                               class="w-full px-4 py-2 text-lg font-mono text-center bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('verification_code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            2FA 활성화
                        </button>
                        <a href="{{ route('admin.user.2fa.index') }}" 
                           class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            취소
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(isset($backupCodes) && isset($user))
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('클립보드에 복사되었습니다.');
    });
}

function downloadBackupCodes() {
    const codes = @json($backupCodes);
    const content = '2FA 백업 코드\n\n' + codes.join('\n') + '\n\n이 코드는 한 번만 사용할 수 있습니다.\n안전한 곳에 보관하세요.';
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '2fa-backup-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function printBackupCodes() {
    const codes = @json($backupCodes);
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>2FA 백업 코드</title>
            <style>
                body { font-family: monospace; padding: 20px; }
                h1 { font-size: 18px; }
                .code { margin: 5px 0; padding: 5px; background: #f0f0f0; }
            </style>
        </head>
        <body>
            <h1>2FA 백업 코드</h1>
            <p>사용자: {{ $user->email }}</p>
            <p>생성일: ${new Date().toLocaleDateString()}</p>
            <hr>
            ${codes.map(code => '<div class="code">' + code + '</div>').join('')}
            <hr>
            <p><small>이 코드는 한 번만 사용할 수 있습니다. 안전한 곳에 보관하세요.</small></p>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// 인증 코드 입력 자동 포맷
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('verification_code');
    if (input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    }
});
</script>
@endif
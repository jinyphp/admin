{{-- TOTP (Authenticator 앱) 설정 화면 --}}
<div class="space-y-4">
    {{-- QR 코드 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">1단계: QR 코드 스캔</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
            Google Authenticator, Microsoft Authenticator 등의 앱으로 아래 QR 코드를 스캔하세요
        </p>
        
        <div class="flex justify-start items-start space-x-4">
            <div class="inline-block p-3 bg-white border-2 border-gray-200 rounded">
                @if(strpos($qrCodeImage, 'data:') === 0)
                    {{-- Base64 인코딩된 이미지 --}}
                    <img src="{{ $qrCodeImage }}" alt="QR Code" class="w-40 h-40">
                @else
                    {{-- 외부 URL --}}
                    <img src="{{ $qrCodeImage }}" alt="QR Code" class="w-40 h-40" crossorigin="anonymous">
                @endif
            </div>
            
            <div class="flex-1">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded p-3">
                    <h4 class="text-xs font-medium text-blue-900 dark:text-blue-200 mb-2">지원되는 앱</h4>
                    <ul class="space-y-1 text-xs text-blue-800 dark:text-blue-300">
                        <li>• Google Authenticator</li>
                        <li>• Microsoft Authenticator</li>
                        <li>• Authy</li>
                        <li>• 1Password</li>
                        <li>• LastPass Authenticator</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 수동 입력 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">수동 입력 (선택사항)</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
            QR 코드를 스캔할 수 없는 경우 아래 정보를 수동으로 입력하세요
        </p>
        
        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">계정 이름</p>
                    <div class="flex items-center justify-between">
                        <code class="text-xs font-mono text-gray-900 dark:text-white">{{ $user->email }}</code>
                        <button onclick="copyToClipboard('{{ $user->email }}')" 
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs">
                            복사
                        </button>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">비밀 키</p>
                    <div class="flex items-center justify-between">
                        <code class="text-xs font-mono text-gray-900 dark:text-white break-all">{{ $secret }}</code>
                        <button onclick="copyToClipboard('{{ $secret }}')" 
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs ml-2">
                            복사
                        </button>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">설정</p>
                    <p class="text-xs text-gray-700 dark:text-gray-300">
                        • 종류: 시간 기반 (TOTP)<br>
                        • 간격: 30초<br>
                        • 자릿수: 6자리
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 백업 코드 섹션 --}}
    @if(isset($backupCodes))
        @include('jiny-admin::admin.admin_user2fa.partials.backup-codes', [
            'backupCodes' => $backupCodes,
            'user' => $user
        ])
    @endif

    {{-- 인증 코드 입력 섹션 --}}
    <div>
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">2단계: 인증 코드 확인</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
            Authenticator 앱에 표시된 6자리 코드를 입력하여 설정을 완료하세요
        </p>
        
        <form action="{{ route('admin.system.user.2fa.store', $user->id) }}" method="POST">
            @csrf
            <input type="hidden" name="method" value="totp">
            <input type="hidden" name="secret" value="{{ $secret }}">
            @if(isset($backupCodes))
                @foreach($backupCodes as $code)
                    <input type="hidden" name="backup_codes[]" value="{{ $code }}">
                @endforeach
            @endif
            
            <div class="flex items-end space-x-3">
                <div class="flex-1 max-w-xs">
                    <label for="totp_code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        6자리 인증 코드
                    </label>
                    <input type="text" 
                           id="totp_code" 
                           name="verification_code"
                           maxlength="6" 
                           pattern="[0-9]{6}"
                           placeholder="000000"
                           class="w-full h-8 px-2.5 text-sm font-mono text-center bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           required
                           autofocus>
                    @error('verification_code')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" 
                        class="h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:ring-offset-1">
                    2FA 활성화
                </button>
            </div>
            
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                💡 팁: 코드는 30초마다 변경됩니다. 입력 중 코드가 변경되면 새 코드를 입력하세요.
            </div>
        </form>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // 간단한 알림
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 px-4 py-2 bg-green-600 text-white text-xs rounded-lg shadow-lg z-50';
        notification.textContent = '클립보드에 복사되었습니다';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }).catch(function(err) {
        console.error('복사 실패:', err);
    });
}

// 코드 입력 자동 포맷
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('totp_code');
    if (input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
        
        // 자동 포커스
        input.focus();
    }
});
</script>
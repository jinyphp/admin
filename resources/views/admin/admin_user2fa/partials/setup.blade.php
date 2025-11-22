{{-- 2FA 설정 화면 --}}
<div class="p-4 space-y-4">
    {{-- 사용자 정보 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">사용자 정보</h3>
        <div class="flex items-center space-x-3">
            @if($user->avatar)
                <img src="{{ asset(ltrim($user->avatar, '/')) }}" 
                     alt="{{ $user->name }}" 
                     class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-700">
            @else
                <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                    <span class="text-gray-600 dark:text-gray-300 font-medium text-sm">{{ mb_substr($user->name, 0, 1, 'UTF-8') }}</span>
                </div>
            @endif
            <div>
                <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    @if(isset($secret) && isset($qrCodeImage))
    {{-- QR 코드 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">1단계: QR 코드 스캔</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
            Google Authenticator 앱에서 아래 QR 코드를 스캔하세요
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
            
            {{-- QR 코드 재생성 버튼 (2FA 미활성화 상태에서만 표시) --}}
            @if(!$user->two_factor_enabled)
            <div class="pt-3">
                <button type="button" 
                        onclick="confirmRegenerateQr()"
                        class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    QR 코드 재생성
                </button>
                <p class="mt-2 text-xs text-yellow-600 dark:text-yellow-400">
                    ⚠️ 재생성하면 현재 QR 코드가 무효화됩니다
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- 수동 입력 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">수동 입력 (선택사항)</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
            QR 코드를 스캔할 수 없는 경우 아래 키를 수동으로 입력하세요
        </p>
        
        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">계정 이름</p>
                    <code class="text-xs font-mono text-gray-900 dark:text-white">{{ $user->email }}</code>
                </div>
                <button onclick="copyToClipboard('{{ $user->email }}')" 
                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs">
                    복사
                </button>
            </div>
            <div class="flex items-center justify-between mt-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">비밀 키</p>
                    <code class="text-xs font-mono text-gray-900 dark:text-white">{{ $secret }}</code>
                </div>
                <button onclick="copyToClipboard('{{ $secret }}')" 
                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs">
                    복사
                </button>
            </div>
        </div>
    </div>

    @if(isset($backupCodes))
    {{-- 백업 코드 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">2단계: 백업 코드 저장</h3>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
            <div class="flex items-start mb-3">
                <svg class="h-4 w-4 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <p class="text-xs text-yellow-800 dark:text-yellow-200 font-medium">
                        중요: 백업 코드를 안전한 곳에 보관하세요
                    </p>
                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                        휴대폰을 분실하거나 Google Authenticator 앱에 접근할 수 없을 때 이 코드를 사용합니다.
                    </p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mb-3">
                @foreach($backupCodes as $code)
                    <code class="bg-white dark:bg-gray-800 px-2 py-1.5 rounded border border-yellow-300 dark:border-yellow-600 text-xs font-mono text-center">
                        {{ $code }}
                    </code>
                @endforeach
            </div>
            
            <div class="flex space-x-2">
                <button onclick="copyBackupCodes()" 
                        class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    백업 코드 복사
                </button>
                <button onclick="downloadBackupCodes()" 
                        class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    다운로드
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- 인증 코드 입력 섹션 --}}
    <div>
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">3단계: 인증 코드 확인</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
            Google Authenticator 앱에 표시된 6자리 코드를 입력하여 설정을 완료하세요
        </p>
        
        <form action="{{ isset($isRegenerating) && $isRegenerating ? route('admin.system.user.2fa.confirm-regenerate', $user->id) : route('admin.system.user.2fa.store', $user->id) }}" method="POST">
            @csrf
            @if(!isset($isRegenerating) || !$isRegenerating)
                <input type="hidden" name="secret" value="{{ $secret ?? '' }}">
                @if(isset($backupCodes))
                    @foreach($backupCodes as $code)
                        <input type="hidden" name="backup_codes[]" value="{{ $code }}">
                    @endforeach
                @endif
            @endif
            
            <div class="flex items-end space-x-3">
                <div class="flex-1 max-w-xs">
                    <label for="verification_code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        인증 코드
                    </label>
                    <input type="text" 
                           id="verification_code" 
                           name="verification_code"
                           maxlength="6" 
                           pattern="[0-9]{6}"
                           placeholder="000000"
                           class="w-full h-8 px-2.5 text-sm font-mono text-center bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('verification_code')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" 
                        class="h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:ring-offset-1">
                    @if(isset($isRegenerating) && $isRegenerating)
                        재생성 확인
                    @else
                        2FA 활성화
                    @endif
                </button>
            </div>
        </form>
    </div>
    @else
    {{-- 초기 설정 버튼 (QR 코드 생성 전) --}}
    <div class="text-center py-6">
        <svg class="mx-auto h-10 w-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">2FA 설정 시작</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
            Google Authenticator를 사용하여 계정 보안을 강화하세요
        </p>
        <form action="{{ route('admin.system.user.2fa.generate', $user->id) }}" method="POST">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:ring-offset-1">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                2FA 설정 시작
            </button>
        </form>
    </div>
    @endif
</div>

{{-- QR 코드 재생성 모달 --}}
<div id="regenerateQrModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 transition-opacity duration-300">
    <div class="relative top-20 mx-auto p-5 w-96 max-w-[90%]">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/20">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mt-4">QR 코드 재생성 확인</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        QR 코드를 재생성하시겠습니까?<br>
                        <strong class="text-red-600 dark:text-red-400">현재 설정이 무효화되며 다시 스캔해야 합니다.</strong>
                    </p>
                </div>
                <div class="flex justify-center space-x-4 mt-4">
                    <button onclick="closeRegenerateModal()" 
                            class="h-8 px-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs font-medium hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                        취소
                    </button>
                    <form action="{{ route('admin.system.user.2fa.regenerate-qr', $user->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="h-8 px-3 bg-yellow-600 text-white rounded text-xs font-medium hover:bg-yellow-700 transition-colors">
                            재생성
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($backupCodes))
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('클립보드에 복사되었습니다.');
    });
}

function copyBackupCodes() {
    const codes = @json($backupCodes);
    const text = codes.join('\n');
    navigator.clipboard.writeText(text).then(function() {
        alert('백업 코드가 클립보드에 복사되었습니다.');
    });
}

function downloadBackupCodes() {
    const codes = @json($backupCodes);
    const content = '2FA 백업 코드\n\n' + codes.join('\n') + '\n\n사용자: {{ $user->email }}\n생성일: ' + new Date().toLocaleDateString() + '\n\n이 코드는 한 번만 사용할 수 있습니다.\n안전한 곳에 보관하세요.';
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '2fa-backup-codes-{{ $user->id }}.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function confirmRegenerateQr() {
    document.getElementById('regenerateQrModal').classList.remove('hidden');
}

function closeRegenerateModal() {
    document.getElementById('regenerateQrModal').classList.add('hidden');
}

// 인증 코드 입력 자동 포맷
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('verification_code');
    if (input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
        // 자동 포커스
        input.focus();
    }
    
    // 모달 외부 클릭 시 닫기
    const modal = document.getElementById('regenerateQrModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                closeRegenerateModal();
            }
        });
    }
});
</script>
@endif
{{-- 2FA 관리 화면 --}}
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

    {{-- 현재 상태 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">2FA 상태</h3>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3">
            <div class="flex items-center">
                <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 dark:text-green-200 font-medium text-xs">2FA가 활성화되어 있습니다</span>
            </div>
            <div class="mt-2 space-y-1">
                @if($user->two_factor_confirmed_at)
                    <p class="text-xs text-green-700 dark:text-green-300">
                        설정일: {{ \Carbon\Carbon::parse($user->two_factor_confirmed_at)->format('Y년 m월 d일 H:i') }}
                    </p>
                @endif
                @if($user->last_2fa_used_at)
                    <p class="text-xs text-green-700 dark:text-green-300">
                        마지막 사용: {{ \Carbon\Carbon::parse($user->last_2fa_used_at)->diffForHumans() }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- QR 코드 재표시 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">QR 코드</h3>
        
        @if(isset($qrCodeImage) && $qrCodeImage)
            {{-- QR 코드가 이미 생성된 경우 --}}
            <div class="space-y-3">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Google Authenticator 앱에서 아래 QR 코드를 스캔하세요
                </p>
                
                <div class="flex justify-start">
                    <div class="inline-block p-3 bg-white border-2 border-gray-200 rounded">
                        @if(strpos($qrCodeImage, 'data:') === 0)
                            <img src="{{ $qrCodeImage }}" alt="QR Code" class="w-40 h-40">
                        @else
                            <img src="{{ $qrCodeImage }}" alt="QR Code" class="w-40 h-40" crossorigin="anonymous">
                        @endif
                    </div>
                </div>
                
                @if(isset($secret) && $secret)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">비밀 키</p>
                        <code class="text-xs font-mono text-gray-900 dark:text-white">{{ $secret }}</code>
                    </div>
                @endif
                
                <form action="{{ route('admin.system.user.2fa.generate', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center h-8 px-3 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        QR 코드 숨기기
                    </button>
                </form>
            </div>
        @else
            {{-- QR 코드가 없는 경우 --}}
            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                <div class="flex items-start">
                    <svg class="h-4 w-4 text-gray-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3 flex-1">
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            QR 코드를 다시 표시하려면 아래 버튼을 클릭하세요.
                        </p>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                            새 기기에서 Google Authenticator를 설정하거나 기존 설정을 확인할 때 사용하세요.
                        </p>
                    </div>
                </div>
                
                <div class="mt-3">
                    <form action="{{ route('admin.system.user.2fa.show-qr', $user->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center h-8 px-3 border border-blue-600 dark:border-blue-500 text-xs font-medium rounded text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2l-2-2v2z"/>
                            </svg>
                            QR 코드 표시
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- 백업 코드 섹션 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">백업 코드</h3>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
            <div class="flex items-start">
                <svg class="h-4 w-4 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3 flex-1">
                    <p class="text-xs text-yellow-800 dark:text-yellow-200">
                        @php
                            $backupCodes = $user->two_factor_recovery_codes ? count(json_decode(decrypt($user->two_factor_recovery_codes), true)) : 0;
                        @endphp
                        남은 백업 코드: <span class="font-medium">{{ $backupCodes }}개</span>
                    </p>
                    <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">
                        백업 코드는 휴대폰을 분실했을 때 로그인하는 데 사용됩니다.
                    </p>
                </div>
            </div>
            
            <div class="mt-3">
                <form action="{{ route('admin.system.user.2fa.regenerate-backup', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        백업 코드 재생성
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 2FA 비활성화 섹션 --}}
    <div>
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">2FA 비활성화</h3>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded p-3">
            <div class="flex items-start">
                <svg class="h-4 w-4 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3 flex-1">
                    <p class="text-xs text-red-800 dark:text-red-200 font-medium">
                        주의: 2FA를 비활성화하면 계정 보안이 약화됩니다
                    </p>
                    <p class="mt-1 text-xs text-red-700 dark:text-red-300">
                        2FA를 비활성화하면 비밀번호만으로 로그인할 수 있게 됩니다.
                    </p>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="button" 
                        onclick="confirmDisable2FA()"
                        class="inline-flex items-center h-8 px-3 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    2FA 비활성화
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 2FA 비활성화 확인 모달 --}}
<div id="disableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mt-4">2FA 비활성화 확인</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $user->name }}님의 2FA를 비활성화하시겠습니까?<br>
                    이 작업으로 계정 보안이 약화될 수 있습니다.
                </p>
            </div>
            <div class="flex justify-center space-x-4 mt-4">
                <button onclick="closeDisableModal()" 
                        class="h-8 px-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs font-medium hover:bg-gray-400 dark:hover:bg-gray-500">
                    취소
                </button>
                <form action="{{ route('admin.system.user.2fa.disable', $user->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="h-8 px-3 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700">
                        비활성화
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDisable2FA() {
    document.getElementById('disableModal').classList.remove('hidden');
}

function closeDisableModal() {
    document.getElementById('disableModal').classList.add('hidden');
}

// 모달 외부 클릭 시 닫기
document.getElementById('disableModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeDisableModal();
    }
});
</script>
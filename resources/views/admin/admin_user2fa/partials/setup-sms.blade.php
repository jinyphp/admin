{{-- SMS 2FA 설정 화면 --}}
<div class="space-y-4">
    @if(!$user->phone_number)
        {{-- 전화번호 등록 필요 --}}
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">전화번호 등록 필요</h3>
                    <p class="mt-2 text-xs text-yellow-700 dark:text-yellow-300">
                        SMS 2FA를 사용하려면 먼저 전화번호를 등록해야 합니다.
                    </p>
                    <form action="{{ route('admin.user.profile.update-phone', $user->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="flex items-end space-x-2">
                            <div class="flex-1">
                                <label for="phone_number" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    전화번호
                                </label>
                                <input type="tel" 
                                       id="phone_number" 
                                       name="phone_number"
                                       placeholder="+82 10-1234-5678"
                                       class="w-full h-8 px-2.5 text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            <button type="submit" 
                                    class="h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                                등록
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        {{-- SMS 코드 발송 --}}
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">SMS 인증 설정</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                등록된 전화번호로 인증 코드를 발송합니다.
            </p>
            
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">등록된 전화번호</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->phone_number }}</p>
                    </div>
                    <a href="{{ route('admin.user.profile.edit', $user->id) }}" 
                       class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        변경
                    </a>
                </div>
                
                {{-- 코드 발송 버튼 --}}
                <div id="send-code-section">
                    <button type="button" 
                            onclick="sendSmsCode()"
                            id="send-sms-button"
                            class="w-full h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="inline-flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            SMS 인증 코드 발송
                        </span>
                    </button>
                    
                    {{-- 재발송 대기 메시지 --}}
                    <div id="resend-timer" class="hidden mt-2 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            재발송 가능까지 <span id="countdown" class="font-medium text-blue-600">60초</span> 남음
                        </p>
                    </div>
                </div>
                
                {{-- 코드 입력 폼 (코드 발송 후 표시) --}}
                <div id="verify-code-section" class="hidden mt-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3 mb-3">
                        <p class="text-xs text-green-800 dark:text-green-200">
                            ✓ 인증 코드가 발송되었습니다. 5분 이내에 입력해주세요.
                        </p>
                    </div>
                    
                    <form action="{{ route('admin.user.2fa.verify-sms', $user->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="method" value="sms">
                        
                        <div class="flex items-end space-x-2">
                            <div class="flex-1">
                                <label for="sms_code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    6자리 인증 코드
                                </label>
                                <input type="text" 
                                       id="sms_code" 
                                       name="code"
                                       maxlength="6" 
                                       pattern="[0-9]{6}"
                                       placeholder="000000"
                                       class="w-full h-8 px-2.5 text-sm font-mono text-center bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            <button type="submit" 
                                    class="h-8 px-3 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                                확인 및 활성화
                            </button>
                        </div>
                    </form>
                    
                    <button type="button" 
                            onclick="sendSmsCode()"
                            id="resend-button"
                            class="hidden mt-2 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        코드 재발송
                    </button>
                </div>
            </div>
        </div>
        
        {{-- 백업 코드 섹션 --}}
        @if(isset($backupCodes))
            @include('jiny-admin::admin.admin_user2fa.partials.backup-codes', ['backupCodes' => $backupCodes])
        @endif
    @endif
</div>

<script>
let countdownInterval;

function sendSmsCode() {
    const button = document.getElementById('send-sms-button');
    const resendButton = document.getElementById('resend-button');
    
    // 버튼 비활성화
    button.disabled = true;
    if (resendButton) resendButton.classList.add('hidden');
    
    // SMS 코드 발송 요청
    fetch(`/admin/users/{{ $user->id }}/2fa/send-sms`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 코드 입력 섹션 표시
            document.getElementById('verify-code-section').classList.remove('hidden');
            document.getElementById('send-code-section').classList.add('hidden');
            
            // 재발송 타이머 시작
            startResendTimer();
            
            // 입력 필드에 포커스
            document.getElementById('sms_code').focus();
        } else {
            alert(data.message || 'SMS 발송에 실패했습니다.');
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
        button.disabled = false;
    });
}

function startResendTimer() {
    const timerDiv = document.getElementById('resend-timer');
    const countdown = document.getElementById('countdown');
    const resendButton = document.getElementById('resend-button');
    
    timerDiv.classList.remove('hidden');
    let seconds = 60;
    
    countdownInterval = setInterval(() => {
        seconds--;
        countdown.textContent = seconds + '초';
        
        if (seconds <= 0) {
            clearInterval(countdownInterval);
            timerDiv.classList.add('hidden');
            resendButton.classList.remove('hidden');
        }
    }, 1000);
}

// 코드 입력 자동 포맷
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('sms_code');
    if (input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    }
});
</script>
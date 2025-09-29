{{-- Email 2FA 설정 화면 --}}
<div class="space-y-4">
    {{-- 이메일 인증 설정 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">이메일 인증 설정</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
            등록된 이메일 주소로 인증 코드를 발송합니다.
        </p>
        
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">등록된 이메일</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->email }}</p>
                </div>
                <div class="flex items-center text-xs text-green-600 dark:text-green-400">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    확인됨
                </div>
            </div>
            
            {{-- 코드 발송 버튼 --}}
            <div id="send-email-section">
                <button type="button" 
                        onclick="sendEmailCode()"
                        id="send-email-button"
                        class="w-full h-8 px-3 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="inline-flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        이메일 인증 코드 발송
                    </span>
                </button>
                
                {{-- 재발송 대기 메시지 --}}
                <div id="email-resend-timer" class="hidden mt-2 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        재발송 가능까지 <span id="email-countdown" class="font-medium text-purple-600">60초</span> 남음
                    </p>
                </div>
            </div>
            
            {{-- 코드 입력 폼 (코드 발송 후 표시) --}}
            <div id="verify-email-section" class="hidden mt-4">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3 mb-3">
                    <p class="text-xs text-green-800 dark:text-green-200">
                        ✓ 인증 코드가 이메일로 발송되었습니다. 5분 이내에 입력해주세요.
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        스팸 메일함도 확인해주세요.
                    </p>
                </div>
                
                <form action="{{ route('admin.user.2fa.verify-email', $user->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="email">
                    
                    <div class="flex items-end space-x-2">
                        <div class="flex-1">
                            <label for="email_code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                6자리 인증 코드
                            </label>
                            <input type="text" 
                                   id="email_code" 
                                   name="code"
                                   maxlength="6" 
                                   pattern="[0-9]{6}"
                                   placeholder="000000"
                                   class="w-full h-8 px-2.5 text-sm font-mono text-center bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                                   required>
                        </div>
                        <button type="submit" 
                                class="h-8 px-3 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                            확인 및 활성화
                        </button>
                    </div>
                </form>
                
                <div class="mt-3 flex items-center justify-between">
                    <button type="button" 
                            onclick="sendEmailCode()"
                            id="email-resend-button"
                            class="hidden text-xs text-purple-600 hover:text-purple-800 dark:text-purple-400">
                        코드 재발송
                    </button>
                    <a href="mailto:{{ config('mail.from.address') }}" 
                       class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        이메일을 받지 못하셨나요?
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 이메일 2FA 장단점 안내 --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
        <h4 class="text-xs font-medium text-blue-900 dark:text-blue-200 mb-2">이메일 2FA 안내</h4>
        <ul class="space-y-1 text-xs text-blue-800 dark:text-blue-300">
            <li class="flex items-start">
                <svg class="w-3.5 h-3.5 mr-1.5 mt-0.5 flex-shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                별도의 앱 설치가 필요하지 않습니다
            </li>
            <li class="flex items-start">
                <svg class="w-3.5 h-3.5 mr-1.5 mt-0.5 flex-shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                어디서든 이메일만 확인할 수 있으면 사용 가능합니다
            </li>
            <li class="flex items-start">
                <svg class="w-3.5 h-3.5 mr-1.5 mt-0.5 flex-shrink-0 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                이메일 전송 지연이 발생할 수 있습니다
            </li>
            <li class="flex items-start">
                <svg class="w-3.5 h-3.5 mr-1.5 mt-0.5 flex-shrink-0 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                이메일이 스팸 메일함으로 분류될 수 있습니다
            </li>
        </ul>
    </div>
    
    {{-- 백업 코드 섹션 --}}
    @if(isset($backupCodes))
        @include('jiny-admin::admin.admin_user2fa.partials.backup-codes', ['backupCodes' => $backupCodes])
    @endif
</div>

<script>
let emailCountdownInterval;

function sendEmailCode() {
    const button = document.getElementById('send-email-button');
    const resendButton = document.getElementById('email-resend-button');
    
    // 버튼 비활성화
    button.disabled = true;
    if (resendButton) resendButton.classList.add('hidden');
    
    // 로딩 표시
    button.innerHTML = `
        <span class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            발송 중...
        </span>
    `;
    
    // 이메일 코드 발송 요청
    fetch(`/admin/users/{{ $user->id }}/2fa/send-email`, {
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
            document.getElementById('verify-email-section').classList.remove('hidden');
            document.getElementById('send-email-section').classList.add('hidden');
            
            // 재발송 타이머 시작
            startEmailResendTimer();
            
            // 입력 필드에 포커스
            document.getElementById('email_code').focus();
        } else {
            alert(data.message || '이메일 발송에 실패했습니다.');
            button.disabled = false;
            button.innerHTML = `
                <span class="inline-flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    이메일 인증 코드 발송
                </span>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
        button.disabled = false;
        button.innerHTML = `
            <span class="inline-flex items-center">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                이메일 인증 코드 발송
            </span>
        `;
    });
}

function startEmailResendTimer() {
    const timerDiv = document.getElementById('email-resend-timer');
    const countdown = document.getElementById('email-countdown');
    const resendButton = document.getElementById('email-resend-button');
    
    timerDiv.classList.remove('hidden');
    let seconds = 60;
    
    emailCountdownInterval = setInterval(() => {
        seconds--;
        countdown.textContent = seconds + '초';
        
        if (seconds <= 0) {
            clearInterval(emailCountdownInterval);
            timerDiv.classList.add('hidden');
            resendButton.classList.remove('hidden');
        }
    }, 1000);
}

// 코드 입력 자동 포맷
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('email_code');
    if (input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    }
});
</script>
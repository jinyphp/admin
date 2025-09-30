@extends('jiny-admin::layouts.admin')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-4">
    {{-- 페이지 헤더 --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title ?? '메일 설정' }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle ?? 'SMTP 메일 서버 설정을 관리합니다' }}</p>
            </div>
        </div>
    </div>

    {{-- 설정 폼 --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form id="mailSettingsForm" class="space-y-6">
                @csrf
                
                {{-- 메일 드라이버 선택 --}}
                <div>
                    <label for="mailer" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        메일 드라이버
                    </label>
                    <select id="mailer" name="mailer" 
                            class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="smtp" {{ ($mailSettings['mailer'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendmail" {{ ($mailSettings['mailer'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="mailgun" {{ ($mailSettings['mailer'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                        <option value="ses" {{ ($mailSettings['mailer'] ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                        <option value="postmark" {{ ($mailSettings['mailer'] ?? '') == 'postmark' ? 'selected' : '' }}>Postmark</option>
                        <option value="log" {{ ($mailSettings['mailer'] ?? '') == 'log' ? 'selected' : '' }}>Log (테스트용)</option>
                    </select>
                </div>

                {{-- SMTP 설정 (SMTP 선택시에만 표시) --}}
                <div id="smtpSettings" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        {{-- SMTP 호스트 --}}
                        <div>
                            <label for="host" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                SMTP 호스트
                            </label>
                            <input type="text" id="host" name="host" 
                                   value="{{ $mailSettings['host'] ?? '' }}"
                                   placeholder="smtp.gmail.com"
                                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        {{-- SMTP 포트 --}}
                        <div>
                            <label for="port" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                SMTP 포트
                            </label>
                            <input type="number" id="port" name="port" 
                                   value="{{ $mailSettings['port'] ?? '' }}"
                                   placeholder="587"
                                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        {{-- SMTP 사용자명 --}}
                        <div>
                            <label for="username" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                SMTP 사용자명
                            </label>
                            <input type="text" id="username" name="username" 
                                   value="{{ $mailSettings['username'] ?? '' }}"
                                   placeholder="your-email@gmail.com"
                                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        {{-- SMTP 비밀번호 --}}
                        <div>
                            <label for="password" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                SMTP 비밀번호
                            </label>
                            <input type="password" id="password" name="password" 
                                   value="{{ $mailSettings['password'] ?? '' }}"
                                   placeholder="••••••••"
                                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        {{-- 암호화 방식 --}}
                        <div>
                            <label for="encryption" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                암호화 방식
                            </label>
                            <select id="encryption" name="encryption" 
                                    class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="tls" {{ ($mailSettings['encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($mailSettings['encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="null" {{ ($mailSettings['encryption'] ?? '') == 'null' ? 'selected' : '' }}>없음</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 발신자 정보 --}}
                <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">발신자 정보</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        {{-- 발신자 이메일 --}}
                        <div>
                            <label for="from_address" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                발신자 이메일 <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="from_address" name="from_address" 
                                   value="{{ $mailSettings['from_address'] ?? '' }}"
                                   placeholder="noreply@example.com"
                                   required
                                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        {{-- 발신자 이름 --}}
                        <div>
                            <label for="from_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                발신자 이름 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="from_name" name="from_name" 
                                   value="{{ $mailSettings['from_name'] ?? '' }}"
                                   placeholder="시스템 관리자"
                                   required
                                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- 버튼 영역 --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            설정 저장
                        </button>
                    </div>
                    <div>
                        <button type="button" id="testMailBtn"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            테스트 메일 발송
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 도움말 --}}
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">도움말</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Gmail을 사용하는 경우: 2단계 인증을 활성화하고 앱 비밀번호를 생성하여 사용하세요.</li>
                        <li>포트 587은 TLS, 포트 465는 SSL을 사용합니다.</li>
                        <li>테스트 메일 발송으로 설정이 올바른지 확인하세요.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 테스트 메일 모달 --}}
<div id="testMailModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 transition-opacity duration-300">
    <div class="relative top-20 mx-auto p-5 w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl">
            {{-- 모달 헤더 --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">테스트 메일 발송</h3>
                    <button type="button" id="closeModalX" 
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- 모달 바디 --}}
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label for="testEmail" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        수신 이메일 주소
                    </label>
                    <input type="email" id="testEmail" 
                           placeholder="example@domain.com"
                           class="block w-full h-8 px-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        테스트 메일을 수신할 이메일 주소를 입력하세요.
                    </p>
                </div>
                
                {{-- 발송 중 표시 --}}
                <div id="sendingIndicator" class="hidden">
                    <div class="flex items-center justify-center py-3">
                        <svg class="animate-spin h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-400">메일 발송 중...</span>
                    </div>
                </div>
                
                {{-- 결과 메시지 --}}
                <div id="resultMessage" class="hidden">
                    <div class="rounded-md p-3">
                        <div class="flex">
                            <div class="flex-shrink-0" id="resultIcon"></div>
                            <div class="ml-3">
                                <p class="text-sm" id="resultText"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- 모달 푸터 --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                <div class="flex justify-end space-x-3">
                    <button type="button" id="closeModal" 
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        취소
                    </button>
                    <button type="button" id="sendTestMail" 
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        메일 발송
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 메일 드라이버 변경시 SMTP 설정 표시/숨김
    const mailerSelect = document.getElementById('mailer');
    const smtpSettings = document.getElementById('smtpSettings');
    
    function toggleSmtpSettings() {
        if (mailerSelect.value === 'smtp') {
            smtpSettings.style.display = 'block';
        } else {
            smtpSettings.style.display = 'none';
        }
    }
    
    mailerSelect.addEventListener('change', toggleSmtpSettings);
    toggleSmtpSettings();
    
    // 설정 저장
    document.getElementById('mailSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("admin.system.mail.setting.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert('설정 저장 실패: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('설정 저장 중 오류가 발생했습니다.');
        });
    });
    
    // 테스트 메일 모달
    const testMailBtn = document.getElementById('testMailBtn');
    const testMailModal = document.getElementById('testMailModal');
    const closeModal = document.getElementById('closeModal');
    const closeModalX = document.getElementById('closeModalX');
    const sendTestMail = document.getElementById('sendTestMail');
    const sendingIndicator = document.getElementById('sendingIndicator');
    const resultMessage = document.getElementById('resultMessage');
    const resultIcon = document.getElementById('resultIcon');
    const resultText = document.getElementById('resultText');
    const testEmailInput = document.getElementById('testEmail');
    
    // 모달 열기
    testMailBtn.addEventListener('click', function() {
        testMailModal.classList.remove('hidden');
        testEmailInput.value = '';
        sendingIndicator.classList.add('hidden');
        resultMessage.classList.add('hidden');
        testEmailInput.focus();
    });
    
    // 모달 닫기
    function closeTestMailModal() {
        testMailModal.classList.add('hidden');
        testEmailInput.value = '';
        sendingIndicator.classList.add('hidden');
        resultMessage.classList.add('hidden');
    }
    
    closeModal.addEventListener('click', closeTestMailModal);
    closeModalX.addEventListener('click', closeTestMailModal);
    
    // 모달 외부 클릭시 닫기
    testMailModal.addEventListener('click', function(event) {
        if (event.target === this) {
            closeTestMailModal();
        }
    });
    
    // ESC 키로 모달 닫기
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !testMailModal.classList.contains('hidden')) {
            closeTestMailModal();
        }
    });
    
    // Enter 키로 메일 발송
    testEmailInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            sendTestMail.click();
        }
    });
    
    // 테스트 메일 발송
    sendTestMail.addEventListener('click', function() {
        const testEmail = testEmailInput.value.trim();
        
        if (!testEmail) {
            testEmailInput.focus();
            testEmailInput.classList.add('border-red-500');
            setTimeout(() => {
                testEmailInput.classList.remove('border-red-500');
            }, 2000);
            return;
        }
        
        // 이메일 형식 검증
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(testEmail)) {
            showResult(false, '올바른 이메일 주소를 입력하세요.');
            return;
        }
        
        // UI 상태 변경
        sendTestMail.disabled = true;
        closeModal.disabled = true;
        sendingIndicator.classList.remove('hidden');
        resultMessage.classList.add('hidden');
        
        fetch('{{ route("admin.system.mail.setting.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                test_email: testEmail
            })
        })
        .then(response => response.json())
        .then(data => {
            sendingIndicator.classList.add('hidden');
            showResult(data.success, data.message);
            
            if (data.success) {
                setTimeout(() => {
                    closeTestMailModal();
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            sendingIndicator.classList.add('hidden');
            showResult(false, '테스트 메일 발송 중 오류가 발생했습니다.');
        })
        .finally(() => {
            sendTestMail.disabled = false;
            closeModal.disabled = false;
        });
    });
    
    // 결과 표시 함수
    function showResult(success, message) {
        resultMessage.classList.remove('hidden');
        
        if (success) {
            resultMessage.firstElementChild.classList.add('bg-green-50', 'dark:bg-green-900/20');
            resultMessage.firstElementChild.classList.remove('bg-red-50', 'dark:bg-red-900/20');
            resultIcon.innerHTML = '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
            resultText.classList.add('text-green-800', 'dark:text-green-200');
            resultText.classList.remove('text-red-800', 'dark:text-red-200');
        } else {
            resultMessage.firstElementChild.classList.add('bg-red-50', 'dark:bg-red-900/20');
            resultMessage.firstElementChild.classList.remove('bg-green-50', 'dark:bg-green-900/20');
            resultIcon.innerHTML = '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
            resultText.classList.add('text-red-800', 'dark:text-red-200');
            resultText.classList.remove('text-green-800', 'dark:text-green-200');
        }
        
        resultText.textContent = message;
    }
});
</script>
@endsection
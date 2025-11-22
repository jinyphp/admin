{{-- 개선된 2FA 설정 화면 (TOTP/SMS/Email 선택 가능) --}}
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
                @if($user->phone_number)
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->phone_number }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- 2FA 방법 선택 --}}
    @if(!$user->two_factor_enabled || isset($changingMethod))
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">2FA 인증 방법 선택</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
            원하는 2단계 인증 방법을 선택하세요
        </p>
        
        <div class="space-y-2">
            {{-- TOTP (Google Authenticator) --}}
            <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors
                {{ ($user->two_factor_method ?? 'totp') === 'totp' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                <input type="radio" 
                       name="2fa_method" 
                       value="totp" 
                       class="mt-1 text-blue-600 focus:ring-blue-500"
                       {{ ($user->two_factor_method ?? 'totp') === 'totp' ? 'checked' : '' }}
                       onchange="selectMethod('totp')">
                <div class="ml-3">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">Authenticator 앱 (TOTP)</span>
                        <span class="ml-2 px-1.5 py-0.5 text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 rounded">권장</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Google Authenticator, Microsoft Authenticator 등의 앱을 사용하여 인증 코드를 생성합니다.
                        인터넷 연결이 필요하지 않습니다.
                    </p>
                </div>
            </label>

            {{-- SMS --}}
            <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors
                {{ $user->two_factor_method === 'sms' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}
                {{ !$user->phone_number ? 'opacity-50 cursor-not-allowed' : '' }}">
                <input type="radio" 
                       name="2fa_method" 
                       value="sms" 
                       class="mt-1 text-blue-600 focus:ring-blue-500"
                       {{ $user->two_factor_method === 'sms' ? 'checked' : '' }}
                       {{ !$user->phone_number ? 'disabled' : '' }}
                       onchange="selectMethod('sms')">
                <div class="ml-3">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">SMS 문자 메시지</span>
                        @if(!$user->phone_number)
                            <span class="ml-2 px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded">전화번호 필요</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        등록된 전화번호로 6자리 인증 코드를 발송합니다.
                        @if(!$user->phone_number)
                            <span class="text-red-600 dark:text-red-400">먼저 프로필에서 전화번호를 등록해주세요.</span>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $user->phone_number }}로 발송됩니다.</span>
                        @endif
                    </p>
                </div>
            </label>

            {{-- Email --}}
            <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors
                {{ $user->two_factor_method === 'email' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                <input type="radio" 
                       name="2fa_method" 
                       value="email" 
                       class="mt-1 text-blue-600 focus:ring-blue-500"
                       {{ $user->two_factor_method === 'email' ? 'checked' : '' }}
                       onchange="selectMethod('email')">
                <div class="ml-3">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">이메일</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        등록된 이메일 주소로 6자리 인증 코드를 발송합니다.
                        <span class="text-gray-600 dark:text-gray-400">{{ $user->email }}로 발송됩니다.</span>
                    </p>
                </div>
            </label>
        </div>
    </div>
    @endif

    {{-- 선택된 방법에 따른 설정 화면 --}}
    <div id="setup-content">
        @if($user->two_factor_enabled && !isset($changingMethod))
            {{-- 이미 2FA가 활성화된 경우 --}}
            @include('jiny-admin::admin.admin_user2fa.partials.manage-enhanced')
        @else
            {{-- TOTP 설정 (기본값) --}}
            <div id="totp-setup" class="method-setup {{ ($user->two_factor_method ?? 'totp') !== 'totp' ? 'hidden' : '' }}">
                @if(isset($secret) && isset($qrCodeImage))
                    @include('jiny-admin::admin.admin_user2fa.partials.setup-totp', [
                        'user' => $user,
                        'secret' => $secret,
                        'qrCodeImage' => $qrCodeImage,
                        'backupCodes' => $backupCodes ?? null
                    ])
                @else
                    <div class="text-center py-6">
                        <form action="{{ route('admin.system.user.2fa.generate', $user->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="method" value="totp">
                            <button type="submit" 
                                    class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Authenticator 앱 설정 시작
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- SMS 설정 --}}
            <div id="sms-setup" class="method-setup {{ $user->two_factor_method !== 'sms' ? 'hidden' : '' }}">
                @include('jiny-admin::admin.admin_user2fa.partials.setup-sms', ['user' => $user])
            </div>

            {{-- Email 설정 --}}
            <div id="email-setup" class="method-setup {{ $user->two_factor_method !== 'email' ? 'hidden' : '' }}">
                @include('jiny-admin::admin.admin_user2fa.partials.setup-email', ['user' => $user])
            </div>
        @endif
    </div>
</div>

<script>
function selectMethod(method) {
    // 모든 설정 화면 숨기기
    document.querySelectorAll('.method-setup').forEach(el => {
        el.classList.add('hidden');
    });
    
    // 선택된 방법의 설정 화면 표시
    const setupEl = document.getElementById(method + '-setup');
    if (setupEl) {
        setupEl.classList.remove('hidden');
    }
    
    // 서버에 방법 변경 요청 (필요시)
    if ({{ $user->two_factor_enabled ? 'true' : 'false' }}) {
        fetch(`/admin/users/${{{ $user->id }}}/2fa/change-method`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ method: method })
        });
    }
}

// 카운트다운 타이머
function startCountdown(elementId, seconds) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let remaining = seconds;
    const interval = setInterval(() => {
        remaining--;
        element.textContent = remaining + '초';
        
        if (remaining <= 0) {
            clearInterval(interval);
            element.parentElement.classList.add('hidden');
            document.getElementById('resend-button')?.classList.remove('hidden');
        }
    }, 1000);
}
</script>
{{-- 개선된 2FA 관리 화면 --}}
<div class="space-y-4">
    {{-- 현재 2FA 상태 --}}
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="h-5 w-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">2FA가 활성화되어 있습니다</h3>
                <div class="mt-2 text-xs text-green-700 dark:text-green-300">
                    <p>인증 방법: 
                        <span class="font-medium">
                            @if($user->two_factor_method === 'sms')
                                SMS 문자 메시지
                            @elseif($user->two_factor_method === 'email')
                                이메일
                            @else
                                Authenticator 앱 (TOTP)
                            @endif
                        </span>
                    </p>
                    <p>활성화 날짜: {{ $user->two_factor_confirmed_at ? $user->two_factor_confirmed_at->format('Y-m-d H:i') : '-' }}</p>
                    <p>마지막 사용: {{ $user->last_2fa_used_at ? $user->last_2fa_used_at->format('Y-m-d H:i') : '사용 기록 없음' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2FA 방법 변경 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">인증 방법 변경</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
            다른 2FA 인증 방법으로 변경할 수 있습니다.
        </p>
        
        <div class="flex space-x-2">
            @if($user->two_factor_method !== 'totp')
                <form action="{{ route('admin.user.2fa.change-method', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="method" value="totp">
                    <button type="submit" 
                            class="inline-flex items-center h-8 px-3 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Authenticator 앱으로 변경
                    </button>
                </form>
            @endif
            
            @if($user->two_factor_method !== 'sms' && $user->phone_number)
                <form action="{{ route('admin.user.2fa.change-method', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="method" value="sms">
                    <button type="submit" 
                            class="inline-flex items-center h-8 px-3 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        SMS로 변경
                    </button>
                </form>
            @endif
            
            @if($user->two_factor_method !== 'email')
                <form action="{{ route('admin.user.2fa.change-method', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="method" value="email">
                    <button type="submit" 
                            class="inline-flex items-center h-8 px-3 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        이메일로 변경
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- 백업 코드 관리 --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">백업 코드 관리</h3>
        
        @php
            $backupStatus = app(\Jiny\Admin\App\Services\TwoFactorAuthService::class)->getStatusEnhanced($user);
        @endphp
        
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">백업 코드 상태</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $backupStatus['backup_codes']['remaining'] }} / {{ $backupStatus['backup_codes']['total'] }} 개 남음
                    </p>
                    @if($backupStatus['backup_codes']['used'] > 0)
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                            {{ $backupStatus['backup_codes']['used'] }}개 사용됨
                        </p>
                    @endif
                </div>
                
                @if($backupStatus['backup_codes']['remaining'] < 3)
                    <div class="text-xs text-red-600 dark:text-red-400">
                        ⚠️ 백업 코드가 부족합니다
                    </div>
                @endif
            </div>
            
            <div class="flex space-x-2">
                <form action="{{ route('admin.user.2fa.regenerate-backup', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('기존 백업 코드가 모두 무효화됩니다. 계속하시겠습니까?')"
                            class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        백업 코드 재생성
                    </button>
                </form>
                
                <a href="{{ route('admin.user.2fa.download-backup', $user->id) }}" 
                   class="inline-flex items-center h-8 px-3 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    백업 코드 다운로드
                </a>
            </div>
        </div>
    </div>

    {{-- 2FA 비활성화 --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-3">위험 구역</h3>
        
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
            <p class="text-xs text-red-700 dark:text-red-300 mb-3">
                2FA를 비활성화하면 계정 보안이 약해집니다. 신중하게 결정하세요.
            </p>
            
            <form action="{{ route('admin.user.2fa.destroy', $user->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        onclick="return confirm('정말로 2FA를 비활성화하시겠습니까? 이 작업은 즉시 적용됩니다.')"
                        class="inline-flex items-center h-8 px-3 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    2FA 비활성화
                </button>
            </form>
        </div>
    </div>

    {{-- 활동 로그 --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">최근 2FA 활동</h3>
        
        @php
            $recentLogs = \DB::table('admin_user_logs')
                ->where('user_id', $user->id)
                ->where('action', 'like', '2fa_%')
                ->orderBy('logged_at', 'desc')
                ->limit(5)
                ->get();
        @endphp
        
        @if($recentLogs->count() > 0)
            <div class="space-y-2">
                @foreach($recentLogs as $log)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded p-2">
                        <div class="flex items-center justify-between">
                            <div class="text-xs">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ str_replace('2fa_', '', $log->action) }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 ml-2">
                                    {{ $log->description }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($log->logged_at)->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-gray-500 dark:text-gray-400">
                활동 기록이 없습니다.
            </p>
        @endif
    </div>
</div>

{{-- 새로 생성된 백업 코드 표시 (세션에서) --}}
@if(session('backup_codes'))
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 w-[500px] max-w-[90%]">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-6">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">새로운 백업 코드</h3>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mb-4">
                    ⚠️ 이 코드들은 한 번만 표시됩니다. 안전한 곳에 저장하세요!
                </p>
                
                <div class="grid grid-cols-2 gap-2 mb-4">
                    @foreach(session('backup_codes') as $code)
                        <code class="bg-gray-100 dark:bg-gray-900 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 text-xs font-mono text-center">
                            {{ $code }}
                        </code>
                    @endforeach
                </div>
                
                <button onclick="this.closest('.fixed').remove()" 
                        class="w-full h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                    확인 (저장했습니다)
                </button>
            </div>
        </div>
    </div>
@endif
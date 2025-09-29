{{-- 헤더 섹션 --}}
<div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">사용자 로그 상세</h2>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">로그 활동 정보를 확인할 수 있습니다</p>
            </div>
            <div class="flex items-center space-x-3">
                {{-- 액션 배지 --}}
                @php
                    $colors = [
                        'login' => 'green',
                        'logout' => 'blue',
                        'failed_login' => 'red',
                        'unauthorized_login' => 'orange',
                        'test_login' => 'yellow'
                    ];
                    $labels = [
                        'login' => '로그인',
                        'logout' => '로그아웃',
                        'failed_login' => '로그인 실패',
                        'unauthorized_login' => '권한 없음',
                        'test_login' => '테스트'
                    ];
                    $color = $colors[$data['action']] ?? 'gray';
                    $label = $labels[$data['action']] ?? $data['action'];
                @endphp
                @if($color == 'green')
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ $label }}
                    </span>
                @elseif($color == 'blue')
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        {{ $label }}
                    </span>
                @elseif($color == 'red')
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $label }}
                    </span>
                @elseif($color == 'orange')
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $label }}
                    </span>
                @elseif($color == 'yellow')
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $label }}
                    </span>
                @else
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                        {{ $label }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- 기본 정보 --}}
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 왼쪽 컬럼 --}}
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">로그 ID</label>
                    <div class="flex items-center">
                        <span class="inline-flex items-center h-8 px-3 rounded bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-xs font-mono font-semibold">
                            #{{ $data['id'] ?? '-' }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">사용자 ID</label>
                    @if($data['user_id'])
                        <a href="{{ route('admin.users.show', $data['user_id']) }}"
                           class="text-sm font-semibold text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                            {{ $data['user_id'] }} (프로필 보기)
                        </a>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">-</p>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">이메일</label>
                    @if($data['user_id'] && $data['email'])
                        <a href="{{ route('admin.users.show', $data['user_id']) }}"
                           class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                            {{ $data['email'] }}
                        </a>
                    @else
                        <p class="text-sm text-gray-900 dark:text-white">{{ $data['email'] ?? '-' }}</p>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">이름</label>
                    @if($data['user_id'] && $data['name'])
                        <a href="{{ route('admin.users.show', $data['user_id']) }}"
                           class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                            {{ $data['name'] }}
                        </a>
                    @else
                        <p class="text-sm text-gray-900 dark:text-white">{{ $data['name'] ?? '-' }}</p>
                    @endif
                </div>
            </div>

            {{-- 오른쪽 컬럼 --}}
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">IP 주소</label>
                    <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $data['ip_address'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">세션 ID</label>
                    <p class="text-xs text-gray-700 dark:text-gray-300 font-mono break-all">{{ $data['session_id'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">로그 시간</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        @if($data['logged_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['logged_at'])->format('Y-m-d H:i:s') }}
                        @elseif($data['created_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['created_at'])->format('Y-m-d H:i:s') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2FA 정보 --}}
@if($data['action'] === 'login' || $data['action'] === 'failed_login')
<div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">2차 인증 (2FA) 정보</h3>
    </div>
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">2FA 사용 여부</label>
                    @if($data['two_factor_used'] ?? false)
                        <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                            </svg>
                            사용함
                        </span>
                    @elseif($data['two_factor_required'] ?? false)
                        <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            필요했음
                        </span>
                    @else
                        <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            사용 안함
                        </span>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">인증 방법</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $data['two_factor_method'] ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">2FA 인증 시간</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        @if($data['two_factor_verified_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['two_factor_verified_at'])->format('Y-m-d H:i:s') }}
                        @else
                            -
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">인증 시도 횟수</label>
                    @if($data['two_factor_attempts'] ?? false)
                        <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium {{ $data['two_factor_attempts'] > 2 ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $data['two_factor_attempts'] }}회
                        </span>
                    @else
                        <p class="text-sm text-gray-900 dark:text-white">-</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 사용자 에이전트 정보 --}}
@if($data['user_agent'] ?? false)
<div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">사용자 에이전트</h3>
    </div>
    <div class="px-6 py-4">
        <p class="text-xs text-gray-700 dark:text-gray-300 break-all bg-gray-50 dark:bg-gray-800 p-3 rounded font-mono">
            {{ $data['user_agent'] }}
        </p>
    </div>
</div>
@endif

{{-- JSON 상세 정보 --}}
@if($data['details'] ?? false)
<div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">상세 정보</h3>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JSON 데이터를 트리 형태로 표시합니다</p>
    </div>
    <div class="px-6 py-4">
        <div class="json-tree">
            @php
                $details = is_string($data['details']) ? json_decode($data['details'], true) : $data['details'];
            @endphp
            
            @if($details && is_array($details))
                @include('jiny-admin::admin.partials.json-tree', ['data' => $details, 'level' => 0])
            @else
                <p class="text-xs text-gray-500 dark:text-gray-400">유효한 JSON 데이터가 없습니다.</p>
            @endif
        </div>
    </div>
</div>
@endif

{{-- JSON 트리 스타일 및 스크립트 --}}
<style>
.json-tree {
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.json-tree ul {
    list-style: none;
    margin: 0;
    padding-left: 1.25rem;
}

.json-tree > ul {
    padding-left: 0;
}

.json-tree li {
    margin: 0.25rem 0;
}

.json-key {
    color: #0369a1;
    font-weight: 600;
}

.dark .json-key {
    color: #7dd3fc;
}

.json-string {
    color: #16a34a;
}

.dark .json-string {
    color: #4ade80;
}

.json-number {
    color: #dc2626;
}

.dark .json-number {
    color: #f87171;
}

.json-boolean {
    color: #7c3aed;
}

.dark .json-boolean {
    color: #a78bfa;
}

.json-null {
    color: #6b7280;
    font-style: italic;
}

.dark .json-null {
    color: #9ca3af;
}

.json-toggle {
    cursor: pointer;
    user-select: none;
    color: #6b7280;
    margin-right: 0.25rem;
    font-size: 0.75rem;
    transition: transform 0.2s;
}

.dark .json-toggle {
    color: #9ca3af;
}

.json-toggle.expanded {
    transform: rotate(90deg);
}

.json-collapsed {
    display: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // JSON 트리 토글 기능
    document.querySelectorAll('.json-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const expanded = this.classList.contains('expanded');
            const target = this.parentElement.querySelector('.json-collapsible');
            
            if (expanded) {
                this.classList.remove('expanded');
                this.textContent = '▶';
                if (target) target.classList.add('json-collapsed');
            } else {
                this.classList.add('expanded');
                this.textContent = '▼';
                if (target) target.classList.remove('json-collapsed');
            }
        });
    });
});
</script>

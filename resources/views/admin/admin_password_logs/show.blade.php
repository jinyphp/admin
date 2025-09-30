{{--
    Admin Password Log 상세 뷰
    개별 비밀번호 로그 상세 정보 표시
--}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
    {{-- 헤더 섹션 --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                비밀번호 로그 상세 정보
            </h3>
            @if($data['status'] === 'blocked')
            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                <svg class="w-3 h-3 inline mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                </svg>
                차단됨
            </span>
            @elseif($data['status'] === 'resolved')
            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                <svg class="w-3 h-3 inline mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                해결됨
            </span>
            @else
            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                <svg class="w-3 h-3 inline mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                실패
            </span>
            @endif
        </div>
    </div>

    {{-- 기본 정보 섹션 --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-3">기본 정보</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">이메일</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($data['user_id'] ?? null)
                        <a href="{{ route('admin.system.users.show', $data['user_id']) }}" 
                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                            {{ $data['email'] ?? '-' }}
                        </a>
                    @else
                        {{ $data['email'] ?? '-' }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">IP 주소</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                    {{ $data['ip_address'] ?? '-' }}
                    @if($data['country_code'] ?? null)
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $data['country_code'] }})</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">시도 횟수</dt>
                <dd class="mt-1">
                    @php
                        $attemptCount = $data['attempt_count'] ?? 0;
                        $attemptColor = $attemptCount >= 5 ? 'red' : ($attemptCount >= 3 ? 'yellow' : 'gray');
                    @endphp
                    @if($attemptColor == 'red')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                    @elseif($attemptColor == 'yellow')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                    @endif
                        {{ $attemptCount }}회
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">상태</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['status'] ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- 브라우저 정보 섹션 --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-3">브라우저 정보</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">브라우저</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['browser'] ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">플랫폼</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['platform'] ?? '-' }}
                </dd>
            </div>
            @if($data['device'] ?? null)
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">디바이스</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['device'] }}
                </dd>
            </div>
            @endif
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">User Agent</dt>
                <dd class="mt-1 text-xs text-gray-600 dark:text-gray-400 font-mono break-all">
                    {{ $data['user_agent'] ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- 타임라인 섹션 --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-3">타임라인</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if($data['first_attempt_at'] ?? null)
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">첫 시도</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ \Carbon\Carbon::parse($data['first_attempt_at'])->format('Y-m-d H:i:s') }}
                </dd>
            </div>
            @endif
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">마지막 시도</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['last_attempt_at'] ? \Carbon\Carbon::parse($data['last_attempt_at'])->format('Y-m-d H:i:s') : '-' }}
                </dd>
            </div>
            @if($data['blocked_at'] ?? null)
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">차단 시간</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ \Carbon\Carbon::parse($data['blocked_at'])->format('Y-m-d H:i:s') }}
                </dd>
            </div>
            @endif
            @if($data['resolved_at'] ?? null)
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">해결 시간</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ \Carbon\Carbon::parse($data['resolved_at'])->format('Y-m-d H:i:s') }}
                </dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- 해결 정보 섹션 (상태가 resolved인 경우만) --}}
    @if($data['status'] === 'resolved' && ($data['resolved_by'] ?? null))
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-3">해결 정보</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">해결한 관리자</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($data['resolved_by'])
                        <a href="{{ route('admin.system.users.show', $data['resolved_by']) }}" 
                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                            관리자 #{{ $data['resolved_by'] }}
                        </a>
                    @else
                        -
                    @endif
                </dd>
            </div>
            @if($data['resolution_notes'] ?? null)
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">해결 메모</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['resolution_notes'] }}
                </dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- 추가 정보 섹션 --}}
    <div class="px-6 py-4">
        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-3">시스템 정보</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">로그 ID</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                    #{{ $data['id'] ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">생성일</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['created_at'] ? \Carbon\Carbon::parse($data['created_at'])->format('Y-m-d H:i:s') : '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">수정일</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $data['updated_at'] ? \Carbon\Carbon::parse($data['updated_at'])->format('Y-m-d H:i:s') : '-' }}
                </dd>
            </div>
        </dl>
    </div>
</div>
{{-- 패스워드 히스토리 상세 정보 --}}
<div class="space-y-6">
    {{-- 기본 정보 섹션 --}}
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">기본 정보</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- 사용자 정보 --}}
                @if(isset($data['user']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">사용자</dt>
                        <dd class="mt-1">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $data['user']->name ?? 'Unknown' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $data['user']->email ?? '' }}
                            </div>
                            @if(isset($data['user_id']))
                                <a href="{{ route('admin.system.users.show', $data['user_id']) }}" 
                                   class="text-sm text-indigo-600 hover:text-indigo-900">
                                    사용자 상세보기 →
                                </a>
                            @endif
                        </dd>
                    </div>
                @endif
                
                {{-- 변경 일시 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">변경 일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($data['changed_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['changed_at'])->format('Y년 m월 d일 H:i:s') }}
                            <div class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($data['changed_at'])->diffForHumans() }}
                            </div>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                
                {{-- 만료 일시 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">만료 일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($data['expires_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['expires_at'])->format('Y년 m월 d일 H:i:s') }}
                            @php
                                $isExpired = \Carbon\Carbon::parse($data['expires_at'])->isPast();
                                $daysRemaining = now()->diffInDays(\Carbon\Carbon::parse($data['expires_at']), false);
                            @endphp
                            <div class="text-xs {{ $isExpired ? 'text-red-600' : 'text-gray-500' }} mt-1">
                                @if($isExpired)
                                    {{ abs($daysRemaining) }}일 전 만료됨
                                @else
                                    {{ $daysRemaining }}일 후 만료
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">만료 기한 없음</span>
                        @endif
                    </dd>
                </div>
                
                {{-- 변경 사유 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">변경 사유</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data['change_reason'] ?? '기록 없음' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
    
    {{-- 상태 정보 섹션 --}}
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">상태 정보</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- 만료 상태 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">만료 상태</dt>
                    <dd class="mt-1">
                        @if($data['is_expired'] ?? false)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                만료됨
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                활성
                            </span>
                        @endif
                    </dd>
                </div>
                
                {{-- 패스워드 유형 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">패스워드 유형</dt>
                    <dd class="mt-1">
                        @if($data['is_temporary'] ?? false)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                임시 패스워드
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                사용자는 다음 로그인 시 패스워드를 변경해야 합니다.
                            </p>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                일반 패스워드
                            </span>
                        @endif
                    </dd>
                </div>
                
                {{-- 사용 횟수 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">사용 횟수</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data['usage_count'] ?? 0 }}회
                        @if($data['last_used_at'] ?? false)
                            <div class="text-xs text-gray-500 mt-1">
                                마지막 사용: {{ \Carbon\Carbon::parse($data['last_used_at'])->diffForHumans() }}
                            </div>
                        @endif
                    </dd>
                </div>
                
                {{-- 패스워드 강도 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">패스워드 강도</dt>
                    <dd class="mt-1">
                        @if($data['strength_score'] ?? false)
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full 
                                        @if($data['strength_score'] >= 80) bg-green-600
                                        @elseif($data['strength_score'] >= 60) bg-yellow-600
                                        @elseif($data['strength_score'] >= 40) bg-orange-600
                                        @else bg-red-600
                                        @endif"
                                        style="width: {{ $data['strength_score'] }}%">
                                    </div>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">{{ $data['strength_score'] }}%</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-400">평가되지 않음</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
    
    {{-- 보안 정보 섹션 --}}
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">보안 정보</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- 변경 IP 주소 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">변경 IP 주소</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">
                        {{ $data['changed_by_ip'] ?? '기록 없음' }}
                    </dd>
                </div>
                
                {{-- 사용자 에이전트 --}}
                <div>
                    <dt class="text-sm font-medium text-gray-500">사용자 에이전트</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ Str::limit($data['changed_by_user_agent'] ?? '기록 없음', 50) }}
                    </dd>
                </div>
            </dl>
            
            {{-- 패스워드 강도 상세 --}}
            @if($data['strength_details'] ?? false)
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500 mb-2">패스워드 강도 상세</dt>
                    <dd class="bg-gray-50 rounded-lg p-3">
                        <pre class="text-xs text-gray-700">{{ json_encode($data['strength_details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </dd>
                </div>
            @endif
        </div>
    </div>
    
    {{-- 타임스탬프 정보 --}}
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">타임스탬프</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">생성일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data['created_at'] ? \Carbon\Carbon::parse($data['created_at'])->format('Y년 m월 d일 H:i:s') : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">수정일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data['updated_at'] ? \Carbon\Carbon::parse($data['updated_at'])->format('Y년 m월 d일 H:i:s') : '-' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
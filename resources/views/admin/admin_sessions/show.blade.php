{{--
    세션 상세 정보 뷰
    사용자 세션의 자세한 정보 표시
--}}
<div class="bg-white rounded-lg shadow">
    {{-- 헤더 섹션 --}}
    <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div>
                    <p class="text-xs text-gray-500">{{ $subtitle ?? '' }}</p>
                </div>
                @if($data['is_current_session'])
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 mr-1 bg-green-500 rounded-full animate-pulse"></span>
                        현재 세션
                    </span>
                @elseif($data['is_active'])
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <span class="w-1.5 h-1.5 mr-1 bg-blue-500 rounded-full"></span>
                        활성
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        종료됨
                    </span>
                @endif
            </div>
            <div>
                {{-- <a href="{{ route('admin.system.user.sessions') }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    목록으로
                </a> --}}
            </div>
        </div>
    </div>

    {{-- 상세 정보 섹션 --}}
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 기본 정보 --}}
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">기본 정보</h3>
                <dl class="space-y-2">
                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">세션 ID:</dt>
                        <dd class="text-xs text-gray-900 font-mono flex-1">
                            {{ substr($data['session_id'], 0, 20) }}...
                            <button onclick="navigator.clipboard.writeText('{{ $data['session_id'] }}')"
                                    class="ml-1 text-blue-600 hover:text-blue-800">
                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                </svg>
                            </button>
                        </dd>
                    </div>

                    @if(isset($data['user']))
                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">사용자:</dt>
                        <dd class="text-xs text-gray-900 flex-1">
                            <a href="{{ route('admin.system.users.show', $data['user_id']) }}"
                               class="text-blue-600 hover:text-blue-800">
                                {{ $data['user']['name'] }}
                            </a>
                            <span class="text-gray-500">({{ $data['user']['email'] }})</span>
                        </dd>
                    </div>
                    @endif

                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">IP 주소:</dt>
                        <dd class="text-xs text-gray-900 font-mono flex-1">{{ $data['ip_address'] ?? '-' }}</dd>
                    </div>

                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">로그인 시간:</dt>
                        <dd class="text-xs text-gray-900 flex-1">
                            @if($data['login_at'])
                                {{ \Carbon\Carbon::parse($data['login_at'])->format('Y-m-d H:i:s') }}
                                <span class="text-gray-500">({{ \Carbon\Carbon::parse($data['login_at'])->diffForHumans() }})</span>
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">마지막 활동:</dt>
                        <dd class="text-xs text-gray-900 flex-1">
                            @if($data['last_activity'])
                                {{ \Carbon\Carbon::parse($data['last_activity'])->format('Y-m-d H:i:s') }}
                                <span class="text-gray-500">({{ $data['last_activity_human'] ?? '' }})</span>
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    @if(isset($data['session_duration']))
                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">세션 시간:</dt>
                        <dd class="text-xs text-gray-900 flex-1">
                            @if($data['session_duration'] < 60)
                                {{ $data['session_duration'] }}분
                            @else
                                {{ floor($data['session_duration'] / 60) }}시간 {{ $data['session_duration'] % 60 }}분
                            @endif
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- 브라우저 및 디바이스 정보 --}}
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">브라우저 및 디바이스</h3>
                <dl class="space-y-2">
                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">브라우저:</dt>
                        <dd class="text-xs text-gray-900 flex-1">
                            {{ $data['browser'] ?? 'Unknown' }}
                            @if($data['browser_version'])
                                <span class="text-gray-500">(v{{ $data['browser_version'] }})</span>
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">플랫폼:</dt>
                        <dd class="text-xs text-gray-900 flex-1">{{ $data['platform'] ?? 'Unknown' }}</dd>
                    </div>

                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">디바이스:</dt>
                        <dd class="text-xs text-gray-900 flex-1">
                            @if($data['device'] === 'Mobile')
                                <span class="inline-flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                                    </svg>
                                    모바일
                                </span>
                            @elseif($data['device'] === 'Tablet')
                                <span class="inline-flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 2a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2H5zm5 14a1 1 0 100-2 1 1 0 000 2z"/>
                                    </svg>
                                    태블릿
                                </span>
                            @else
                                <span class="inline-flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5z"/>
                                    </svg>
                                    데스크톱
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-start">
                        <dt class="text-xs text-gray-500 w-28">User Agent:</dt>
                        <dd class="text-xs text-gray-600 font-mono flex-1 break-all">
                            {{ $data['user_agent'] ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- 보안 정보 --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-900 mb-3">보안 정보</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start">
                    <dt class="text-xs text-gray-500 w-28">2FA 사용:</dt>
                    <dd class="text-xs flex-1">
                        @php
                            // 2FA 사용 여부 확인 - extra_data에서 먼저 확인하고, 없으면 사용자의 2FA 활성화 상태 확인
                            $twoFactorUsed = false;
                            if (isset($data['extra_data']['two_factor_used'])) {
                                $twoFactorUsed = $data['extra_data']['two_factor_used'];
                            } elseif (isset($data['user']['two_factor_enabled'])) {
                                $twoFactorUsed = $data['user']['two_factor_enabled'];
                            } elseif (isset($data['two_factor_used'])) {
                                $twoFactorUsed = $data['two_factor_used'];
                            }
                        @endphp

                        @if($twoFactorUsed)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                                </svg>
                                사용
                            </span>
                        @else
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                미사용
                            </span>
                        @endif
                    </dd>
                </div>

                <div class="flex items-start">
                    <dt class="text-xs text-gray-500 w-28">로그인 방법:</dt>
                    <dd class="text-xs text-gray-900 flex-1">{{ $data['login_method'] ?? '일반 로그인' }}</dd>
                </div>
            </dl>
        </div>

        {{-- 액션 버튼 --}}
        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between items-center">
            <div class="flex space-x-2">
                @if($data['is_active'] && !$data['is_current_session'])
                    <button wire:click="hookCustom('terminate', ['id' => {{ $data['id'] }}])"
                            onclick="return confirm('이 세션을 종료하시겠습니까?')"
                            class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        세션 종료
                    </button>
                @endif

                @if($data['is_current_session'])
                    <button wire:click="hookCustom('regenerate', ['id' => {{ $data['id'] }}])"
                            onclick="return confirm('세션을 재발급하시겠습니까?')"
                            class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-white hover:bg-blue-50">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        세션 재발급
                    </button>
                @endif
            </div>

            <div class="text-xs text-gray-500">
                생성: {{ \Carbon\Carbon::parse($data['created_at'])->format('Y-m-d H:i:s') }}
                @if($data['updated_at'])
                    | 수정: {{ \Carbon\Carbon::parse($data['updated_at'])->format('Y-m-d H:i:s') }}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 관련 로그 섹션 --}}
@if(isset($data['user_id']))
<div class="mt-6 bg-white rounded-lg shadow">
    <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="text-sm font-medium text-gray-900">최근 활동 로그</h3>
    </div>
    <div class="p-4">
        @php
            $logs = \Jiny\Admin\Models\AdminUserLog::where('user_id', $data['user_id'])
                ->where('session_id', $data['session_id'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        @endphp

        @if($logs->count() > 0)
            <div class="space-y-2">
                @foreach($logs as $log)
                <div class="flex items-start space-x-3 text-xs">
                    <span class="text-gray-400">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</span>
                    <span class="font-medium text-gray-700">{{ $log->action }}</span>
                    @if($log->details && is_string($log->details))
                        <span class="text-gray-500">{{ Str::limit($log->details, 100) }}</span>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="mt-3 pt-3 border-t">
                <a href="{{ route('admin.system.user.logs', ['filter[session_id]' => $data['session_id']]) }}"
                   class="text-xs text-blue-600 hover:text-blue-800">
                    이 세션의 모든 로그 보기 →
                </a>
            </div>
        @else
            <p class="text-xs text-gray-500">이 세션에 대한 활동 로그가 없습니다.</p>
        @endif
    </div>
</div>
@endif

@extends($jsonData['template']['layout'] ?? 'jiny-admin::layouts.admin')

@section('content')

<div class="w-full">

    {{-- AdminDashTitle 컴포넌트로 타이틀 표시 --}}
    @livewire('jiny-admin::admin-dash-title', [
        'jsonData' => $jsonData,
        'jsonPath' => $jsonPath ?? null,
        'editable' => true
    ])

    <div class="px-4 sm:px-6 lg:px-8">

    <!-- 알림 및 경고 섹션 -->
        @if($alerts ?? false)
        <div class="mb-6 space-y-3">
            @foreach($alerts as $alert)
            <div class="bg-{{ $alert['type'] }}-50 border border-{{ $alert['type'] }}-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-{{ $alert['type'] }}-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-{{ $alert['type'] }}-800">{{ $alert['title'] }}</h3>
                        <div class="mt-2 text-sm text-{{ $alert['type'] }}-700">
                            <p>{{ $alert['message'] }}</p>
                        </div>
                        @if($alert['action'] ?? false)
                        <div class="mt-3">
                            <a href="{{ $alert['action']['url'] }}" class="text-sm font-medium text-{{ $alert['type'] }}-600 hover:text-{{ $alert['type'] }}-500">
                                {{ $alert['action']['text'] }} →
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- 주요 통계 카드 -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <!-- 전체 사용자 -->
            <a href="{{ route('admin.system.users') }}" class="block">
                <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer min-h-[100px]">

                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-medium text-gray-600">전체 사용자</h3>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_users']) }}</p>

                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">관리자: {{ $stats['admin_users'] }}명</p>
                </div>
            </a>

            <!-- 활성 세션 -->
            <a href="{{ route('admin.system.user.sessions') }}" class="block">
                <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer relative min-h-[100px]">
                    <span class="absolute top-3 right-3 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-medium text-gray-600">활성 세션</h3>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['active_sessions']) }}</p>

                        </div>
                    </div>
                    <p class="text-xs text-green-600 mt-2">실시간 접속 중</p>
                </div>
            </a>

            <!-- 오늘 로그인 -->
            <a href="{{ route('admin.system.user.logs') }}" class="block">
                <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer min-h-[100px]">

                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-medium text-gray-600">오늘 로그인</h3>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['today_logins']) }}</p>

                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ now()->format('m월 d일') }}</p>
                </div>
            </a>

            <!-- 2FA 사용률 -->
            <a href="{{ route('admin.system.user.2fa') }}" class="block">
                <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer min-h-[100px]">

                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-medium text-gray-600">2FA 보안</h3>
                            <p class="text-xl font-bold text-purple-600 mt-1">{{ $security['two_factor_percentage'] }}%</p>

                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ $security['two_factor_enabled'] }}명 사용</p>
                </div>
            </a>

            <!-- 이메일 발송 -->
            <a href="{{ route('admin.system.mail.logs') }}" class="block">
                <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer min-h-[100px]">

                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-medium text-gray-600">오늘 이메일</h3>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['emails_today'] ?? 0) }}</p>

                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">발송 완료</p>
                </div>
            </a>

            <!-- SMS 발송 -->
            <a href="{{ route('admin.system.sms.send') }}" class="block">
                <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer min-h-[100px]">

                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-medium text-gray-600">오늘 SMS</h3>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['sms_today'] ?? 0) }}</p>

                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">발송 완료</p>
                </div>
            </a>
        </div>

        <!-- 빠른 작업 섹션 -->
        <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">빠른 작업</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <a href="{{ route('admin.system.users.create') }}" class="flex items-center justify-center px-4 py-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        사용자 추가
                    </a>
                    <a href="{{ route('admin.system.mail.templates.create') }}" class="flex items-center justify-center px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        이메일 템플릿
                    </a>
                    <a href="{{ route('admin.system.ipblacklist') }}" class="flex items-center justify-center px-4 py-3 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        IP 차단 관리
                    </a>
                    <a href="{{ route('admin.system.webhook') }}" class="flex items-center justify-center px-4 py-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        웹훅 설정
                    </a>
                    <a href="{{ route('admin.system.user.type.create') }}" class="flex items-center justify-center px-4 py-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        사용자 유형
                    </a>
                    <a href="{{ route('admin.system.sms.provider') }}" class="flex items-center justify-center px-4 py-3 bg-cyan-50 text-cyan-700 rounded-lg hover:bg-cyan-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        SMS 설정
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 왼쪽: 차트와 활동 -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 로그인 트렌드 차트 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800">24시간 로그인 트렌드</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="loginTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 통신 채널 통계 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800">통신 채널 현황</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-48">
                            <canvas id="communicationChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 최근 활동 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">최근 활동</h3>
                            <p class="text-sm text-gray-500 mt-1">실시간 사용자 활동 모니터링</p>
                        </div>
                        <a href="{{ route('admin.system.user.logs') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            전체 보기 →
                        </a>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        @forelse($recent_activities as $activity)
                        <div class="px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $activity['icon'] }}"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm">
                                            @if($activity['user_id'])
                                                <a href="{{ route('admin.system.users.show', $activity['user_id']) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                                    {{ $activity['name'] ?? $activity['email'] }}
                                                </a>
                                            @else
                                                <span class="font-medium text-gray-900">{{ $activity['email'] }}</span>
                                            @endif
                                            <span class="ml-1 text-gray-600">{{ $activity['label'] }}</span>
                                        </p>
                                        <span class="text-xs text-gray-400">
                                            {{ $activity['logged_at']->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500">
                                        <span>IP: {{ $activity['ip_address'] }}</span>
                                        @if($activity['browser'])
                                        <span>{{ $activity['browser'] }}</span>
                                        @endif
                                        @if($activity['location'] ?? false)
                                        <span>{{ $activity['location'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="px-4 py-8 text-center">
                            <p class="text-sm text-gray-500">활동 기록이 없습니다</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- 최근 통신 로그 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800">최근 통신 로그</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- 이메일 -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-gray-700">이메일</h4>
                                    <a href="{{ route('admin.system.mail.logs') }}" class="text-xs text-indigo-600 hover:text-indigo-700">
                                        더보기 →
                                    </a>
                                </div>
                                @if($recent_emails ?? false)
                                <div class="space-y-2">
                                    @foreach($recent_emails->take(3) as $email)
                                    <div class="text-xs">
                                        <p class="text-gray-900 truncate">{{ $email->subject }}</p>
                                        <p class="text-gray-500">{{ $email->to }} • {{ $email->created_at->diffForHumans() }}</p>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-xs text-gray-500">발송 기록 없음</p>
                                @endif
                            </div>

                            <!-- SMS -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-gray-700">SMS</h4>
                                    <a href="{{ route('admin.system.sms.send') }}" class="text-xs text-indigo-600 hover:text-indigo-700">
                                        더보기 →
                                    </a>
                                </div>
                                @if($recent_sms ?? false)
                                <div class="space-y-2">
                                    @foreach($recent_sms->take(3) as $sms)
                                    <div class="text-xs">
                                        <p class="text-gray-900 truncate">{{ Str::limit($sms->message, 30) }}</p>
                                        <p class="text-gray-500">{{ $sms->to }} • {{ $sms->created_at->diffForHumans() }}</p>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-xs text-gray-500">발송 기록 없음</p>
                                @endif
                            </div>

                            <!-- 웹훅 -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-gray-700">웹훅</h4>
                                    <a href="{{ route('admin.system.webhook.logs') }}" class="text-xs text-indigo-600 hover:text-indigo-700">
                                        더보기 →
                                    </a>
                                </div>
                                @if($recent_webhooks ?? false)
                                <div class="space-y-2">
                                    @foreach($recent_webhooks->take(3) as $webhook)
                                    <div class="text-xs">
                                        <p class="text-gray-900 truncate">{{ $webhook->event }}</p>
                                        <p class="text-gray-500">{{ $webhook->url }} • {{ $webhook->created_at->diffForHumans() }}</p>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-xs text-gray-500">전송 기록 없음</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 오른쪽: 시스템 상태 및 정보 -->
            <div class="space-y-6">
                <!-- 활성 세션 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-800">활성 세션</h3>
                        <a href="{{ route('admin.system.user.sessions') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            전체 →
                        </a>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                        @forelse($active_sessions as $session)
                        <div class="px-4 py-3 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ Str::limit($session['email'], 20) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $session['browser'] }} • {{ $session['ip_address'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $session['last_activity'] ? $session['last_activity']->diffForHumans() : '활동 없음' }}
                                    </p>
                                </div>
                                @if($session['is_current'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    현재
                                </span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="px-4 py-8 text-center">
                            <p class="text-sm text-gray-500">활성 세션 없음</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- 보안 상태 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800">보안 모니터링</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">차단된 IP</span>
                            </div>
                            <span class="text-lg font-bold text-red-600">{{ $security['blocked_ips'] }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">오늘 실패 시도</span>
                            </div>
                            <span class="text-lg font-bold text-yellow-600">{{ $security['failed_attempts_today'] }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">2FA 사용자</span>
                            </div>
                            <span class="text-lg font-bold text-green-600">{{ $security['two_factor_enabled'] }}</span>
                        </div>

                        <div class="pt-4 border-t border-gray-100 space-y-2">
                            <a href="{{ route('admin.system.ipblacklist') }}" class="block text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                IP 차단 관리 →
                            </a>
                            <a href="{{ route('admin.system.captcha.logs') }}" class="block text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                CAPTCHA 로그 →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 브라우저 통계 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800">브라우저 사용 현황</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-40">
                            <canvas id="browserChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 시스템 정보 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800">시스템 정보</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <span class="text-sm text-gray-600">환경</span>
                            <span class="text-sm font-medium {{ $system_status['environment'] === 'production' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $system_status['environment'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <span class="text-sm text-gray-600">PHP</span>
                            <span class="text-sm font-medium text-gray-800">{{ $system_status['php_version'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <span class="text-sm text-gray-600">Laravel</span>
                            <span class="text-sm font-medium text-gray-800">{{ $system_status['laravel_version'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <span class="text-sm text-gray-600">데이터베이스</span>
                            <span class="text-sm font-medium text-gray-800">{{ $system_status['database'] ?? 'MySQL' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <span class="text-sm text-gray-600">캐시</span>
                            <span class="text-sm font-medium text-gray-800">{{ $system_status['cache_driver'] ?? 'File' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-600">디버그</span>
                            <span class="text-sm font-medium {{ $system_status['debug_mode'] === 'On' ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $system_status['debug_mode'] }}
                                @if($system_status['debug_mode'] === 'On' && $system_status['environment'] === 'production')
                                <span class="ml-2 text-xs text-red-600">(주의!)</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 최근 차단된 IP (조건부) -->
        @if($recent_blocks->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">최근 차단된 IP</h3>
                    <p class="text-sm text-gray-500 mt-1">비정상적인 접근 시도로 차단된 IP 주소</p>
                </div>
                <a href="{{ route('admin.system.ipblacklist') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    전체 보기 →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">이메일</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP 주소</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">시도</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">차단 시간</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">브라우저</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recent_blocks as $block)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $block['email'] }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $block['ip_address'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $block['attempt_count'] }}회
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $block['blocked_at']->format('m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $block['browser'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    차단됨
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>




</div>
{{--
<div class="min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    </div>
</div> --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 로그인 트렌드 차트
    const loginCtx = document.getElementById('loginTrendChart').getContext('2d');
    const loginChart = new Chart(loginCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($login_trend['labels']) !!},
            datasets: [{
                label: '로그인',
                data: {!! json_encode($login_trend['data']) !!},
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(79, 70, 229)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgb(79, 70, 229)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 },
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 10 },
                        color: '#6b7280'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // 브라우저 통계 차트
    const browserCtx = document.getElementById('browserChart').getContext('2d');
    new Chart(browserCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($browser_stats['labels']) !!},
            datasets: [{
                data: {!! json_encode($browser_stats['data']) !!},
                backgroundColor: [
                    'rgba(79, 70, 229, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(244, 63, 94, 0.8)',
                    'rgba(156, 163, 175, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: { size: 10 },
                        padding: 10,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // 통신 채널 차트
    const commCtx = document.getElementById('communicationChart');
    if (commCtx) {
        new Chart(commCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['이메일', 'SMS', '웹훅'],
                datasets: [{
                    label: '오늘',
                    data: [
                        {{ $stats['emails_today'] ?? 0 }},
                        {{ $stats['sms_today'] ?? 0 }},
                        {{ $stats['webhooks_today'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(34, 211, 238, 0.8)',
                        'rgba(168, 85, 247, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + '건';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1,
                            font: { size: 10 },
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11 },
                            color: '#6b7280'
                        }
                    }
                }
            }
        });
    }

    // 자동 새로고침 (5분마다)
    @if($jsonData['refresh']['enabled'] ?? true)
    setTimeout(function() {
        location.reload();
    }, {{ $jsonData['refresh']['interval'] ?? 300000 }});
    @endif

    // 실시간 데이터 업데이트 (30초마다)
    setInterval(function() {
        fetch('{{ route("admin.system.dashboard") }}?ajax=1')
            .then(response => response.json())
            .then(data => {
                // 차트 업데이트
                if (data.login_trend) {
                    loginChart.data.labels = data.login_trend.labels;
                    loginChart.data.datasets[0].data = data.login_trend.data;
                    loginChart.update();
                }
                // 통계 카드 업데이트 등
            })
            .catch(error => console.error('Error:', error));
    }, 30000);
});
</script>
@endpush
@endsection

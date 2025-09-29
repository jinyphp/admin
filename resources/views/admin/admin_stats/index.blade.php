{{--
    Admin Stats Dashboard
    브라우저 및 기기 사용 통계 대시보드
--}}
@extends($jsonData['template']['layout'] ?? 'jiny-admin::layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $title ?? 'User Statistics' }}
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $subtitle ?? 'Browser and device usage analytics' }}
                </p>
            </div>
            
            {{-- Period Selector --}}
            <div class="flex items-center space-x-4">
                <select id="period-selector" 
                        class="px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="24hours">최근 24시간</option>
                    <option value="7days" selected>최근 7일</option>
                    <option value="30days">최근 30일</option>
                    <option value="90days">최근 90일</option>
                </select>
                
                <button onclick="window.location.reload()" 
                        class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    새로고침
                </button>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Sessions Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">총 세션</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($statistics['summary']['total_sessions'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Unique Users Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">활성 사용자</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($statistics['summary']['unique_users'] ?? 0) }}
                        <span class="text-xs text-gray-500">/ {{ number_format($statistics['summary']['total_registered_users'] ?? 0) }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Logins Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">총 로그인</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($statistics['summary']['total_logins'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Failed Logins Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">실패한 로그인</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($statistics['summary']['failed_logins'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Statistics Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Browser Usage Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">브라우저 사용률</h2>
            <div class="space-y-3">
                @forelse($statistics['browser_usage'] ?? [] as $browser)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 dark:text-gray-400">{{ $browser['name'] }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $browser['percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $browser['percentage'] }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format($browser['count']) }}개 세션</div>
                </div>
                @empty
                <p class="text-xs text-gray-500">브라우저 데이터가 없습니다</p>
                @endforelse
            </div>
        </div>

        {{-- Operating Systems Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">운영체제</h2>
            <div class="space-y-3">
                @forelse($statistics['operating_systems'] ?? [] as $os)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 dark:text-gray-400">{{ $os['name'] }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $os['percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $os['percentage'] }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format($os['count']) }}개 세션</div>
                </div>
                @empty
                <p class="text-xs text-gray-500">운영체제 데이터가 없습니다</p>
                @endforelse
            </div>
        </div>

        {{-- Device Types --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">기기 유형</h2>
            <div class="grid grid-cols-3 gap-4">
                @forelse($statistics['device_types'] ?? [] as $device)
                <div class="text-center">
                    <div class="text-3xl mb-2">
                        @if($device['type'] == 'Desktop')
                            🖥️
                        @elseif($device['type'] == 'Mobile')
                            📱
                        @elseif($device['type'] == 'Tablet')
                            📱
                        @else
                            ❓
                        @endif
                    </div>
                    <div class="text-xs font-medium text-gray-900 dark:text-white">{{ $device['type'] }}</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $device['percentage'] }}%</div>
                    <div class="text-xs text-gray-500">{{ number_format($device['count']) }}개 세션</div>
                </div>
                @empty
                <p class="text-xs text-gray-500 col-span-3">기기 데이터가 없습니다</p>
                @endforelse
            </div>
        </div>

        {{-- Login Methods --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">로그인 방법</h2>
            <div class="space-y-3">
                @foreach($statistics['login_methods'] ?? [] as $method)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 dark:text-gray-400">{{ $method['method'] }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $method['percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-{{ $method['color'] }}-600 h-2 rounded-full" style="width: {{ $method['percentage'] }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format($method['count']) }}회 로그인</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Session Status --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">세션 상태</h2>
            <div class="flex justify-around">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $statistics['session_status']['active']['count'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">활성</div>
                    <div class="text-xs text-gray-500">{{ $statistics['session_status']['active']['percentage'] ?? 0 }}%</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-400">{{ $statistics['session_status']['inactive']['count'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">비활성</div>
                    <div class="text-xs text-gray-500">{{ $statistics['session_status']['inactive']['percentage'] ?? 0 }}%</div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">평균 세션</h2>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $statistics['summary']['avg_session_duration'] ?? '0 min' }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">지속 시간</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">가장 활발한 날</h2>
            <div class="text-center">
                <div class="text-lg font-bold text-purple-600">{{ $statistics['summary']['most_active_day']['date'] ?? 'N/A' }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">{{ $statistics['summary']['most_active_day']['count'] ?? 0 }}회 로그인</div>
            </div>
        </div>
    </div>

    {{-- Peak Usage Times --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">피크 사용 시간 (24시간)</h2>
        <div class="overflow-x-auto">
            @php
                $maxCount = collect($statistics['peak_usage'] ?? [])->max('count') ?: 1;
                $scale = 100 / $maxCount; // Calculate scale for percentage
            @endphp
            
            <div class="relative" style="min-height: 200px;">
                {{-- Y-axis labels --}}
                <div class="absolute left-0 top-0 bottom-8 w-8 flex flex-col justify-between text-xs text-gray-500">
                    <span>{{ $maxCount }}</span>
                    <span>{{ round($maxCount * 0.75) }}</span>
                    <span>{{ round($maxCount * 0.5) }}</span>
                    <span>{{ round($maxCount * 0.25) }}</span>
                    <span>0</span>
                </div>
                
                {{-- Chart bars --}}
                <div class="ml-10 flex items-end space-x-1" style="height: 160px;">
                    @foreach($statistics['peak_usage'] ?? [] as $hour)
                    <div class="flex-1 flex flex-col items-center justify-end">
                        {{-- Bar --}}
                        <div class="w-full relative group">
                            @if($hour['count'] > 0)
                            <div class="bg-gradient-to-t from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 rounded-t transition-all duration-200 relative"
                                 style="height: {{ $hour['count'] * $scale * 1.6 }}px; min-height: 2px;">
                                {{-- Tooltip on hover --}}
                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                    {{ $hour['count'] }}회 로그인
                                </div>
                            </div>
                            @else
                            <div class="bg-gray-200 dark:bg-gray-700 rounded-t" style="height: 2px;"></div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                {{-- X-axis labels --}}
                <div class="ml-10 flex space-x-1 mt-2">
                    @foreach($statistics['peak_usage'] ?? [] as $hour)
                    <div class="flex-1 text-center">
                        <div class="text-xs text-gray-600 dark:text-gray-400 {{ $loop->iteration % 2 == 0 ? '' : 'font-semibold' }}">
                            {{ substr($hour['hour'], 0, 2) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        {{-- Legend and Summary --}}
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600 dark:text-gray-400">
                        총 로그인: <span class="font-semibold text-gray-900 dark:text-white">
                            {{ collect($statistics['peak_usage'] ?? [])->sum('count') }}
                        </span>
                    </span>
                    @php
                        $peakHour = collect($statistics['peak_usage'] ?? [])->sortByDesc('count')->first();
                    @endphp
                    @if($peakHour && $peakHour['count'] > 0)
                    <span class="text-gray-600 dark:text-gray-400">
                        피크 시간: <span class="font-semibold text-gray-900 dark:text-white">
                            {{ $peakHour['hour'] }} ({{ $peakHour['count'] }}회)
                        </span>
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Browser Versions Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">상위 브라우저 버전</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">브라우저</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">버전</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">세션</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($statistics['browser_versions'] ?? [] as $version)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">{{ $version['browser'] }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">{{ $version['version'] }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">{{ number_format($version['count']) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-3 py-4 text-center text-xs text-gray-500">브라우저 버전 데이터가 없습니다</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Geographic Distribution Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">지역 분포 (상위 20개 IP)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">IP 주소</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">위치</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">세션</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($statistics['geographic'] ?? [] as $geo)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap text-xs font-mono text-gray-900 dark:text-gray-100">{{ $geo['ip'] }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">{{ $geo['location'] }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">{{ number_format($geo['count']) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-3 py-4 text-center text-xs text-gray-500">지역 데이터가 없습니다</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- JavaScript for period selector --}}
<script>
    document.getElementById('period-selector').addEventListener('change', function() {
        const period = this.value;
        const url = new URL(window.location);
        url.searchParams.set('period', period);
        window.location.href = url.toString();
    });

    // Set current period from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPeriod = urlParams.get('period') || '7days';
    document.getElementById('period-selector').value = currentPeriod;
</script>
@endsection
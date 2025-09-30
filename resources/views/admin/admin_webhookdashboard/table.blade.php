{{--
    Webhook 대시보드 뷰
    웹훅 시스템의 통계와 현황을 표시
--}}
@extends($jsonData['template']['layout'] ?? 'jiny-admin::layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- 헤더 --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">웹훅 대시보드</h1>
        <p class="mt-1 text-sm text-gray-600">웹훅 시스템의 상태와 통계를 모니터링합니다</p>
    </div>

    {{-- 통계 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- 총 채널 수 --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">총 채널</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_channels'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">
                        활성: {{ $stats['active_channels'] ?? 0 }} / 비활성: {{ $stats['inactive_channels'] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 오늘 발송 --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">오늘 발송</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['today_sent'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">
                        성공률: {{ $stats['today_success_rate'] ?? 0 }}%
                    </p>
                </div>
            </div>
        </div>

        {{-- 24시간 발송 --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">24시간</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['last_24_hours'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">최근 24시간 발송</p>
                </div>
            </div>
        </div>

        {{-- 전체 성공률 --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">전체 성공률</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['success_rate'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-500">
                        총 {{ $stats['total_sent'] ?? 0 }}건 중 {{ $stats['total_success'] ?? 0 }}건 성공
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 차트와 채널 상태 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- 일별 통계 차트 --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">최근 7일 발송 통계</h3>
            <div class="space-y-3">
                @if(!empty($dailyStats))
                    @foreach($dailyStats as $day)
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 w-16">{{ $day['date'] }}</span>
                        <div class="flex-1 ml-3">
                            <div class="flex h-6 bg-gray-200 rounded overflow-hidden">
                                @php
                                    $total = $day['sent'] + $day['failed'];
                                    $sentPercent = $total > 0 ? ($day['sent'] / $total) * 100 : 0;
                                @endphp
                                @if($sentPercent > 0)
                                <div class="bg-green-500" style="width: {{ $sentPercent }}%"></div>
                                @endif
                                @if($sentPercent < 100)
                                <div class="bg-red-500" style="width: {{ 100 - $sentPercent }}%"></div>
                                @endif
                            </div>
                        </div>
                        <span class="ml-3 text-sm text-gray-600 w-20 text-right">
                            {{ $day['sent'] }}/{{ $day['failed'] }}
                        </span>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-500">데이터 없음</p>
                @endif
            </div>
        </div>

        {{-- 채널 상태 --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">채널 상태</h3>
                <a href="{{ route('admin.system.webhook.channels') }}" class="text-sm text-blue-600 hover:text-blue-900">
                    전체 보기 →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($channels->take(5) as $channel)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full {{ $channel->is_active ? 'bg-green-500' : 'bg-gray-400' }} mr-2"></span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $channel->name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($channel->type) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-900">{{ $channel->success_rate }}%</p>
                        <p class="text-xs text-gray-500">{{ $channel->total_sent }}건</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500">등록된 채널이 없습니다</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 최근 로그 --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">최근 웹훅 로그</h3>
                <a href="{{ route('admin.system.webhook.logs') }}" class="text-sm text-blue-600 hover:text-blue-900">
                    전체 로그 보기 →
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            시간
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            채널
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            메시지
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            상태
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentLogs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->channel_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate">
                                {{ $log->message }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $log->status_class }}-100 text-{{ $log->status_class }}-800">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                            웹훅 로그가 없습니다
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 빠른 링크 --}}
    <div class="mt-6 flex justify-center space-x-4">
        <a href="{{ route('admin.system.webhook.channels.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            새 채널 추가
        </a>
        <a href="{{ route('admin.system.webhook.channels') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            채널 관리
        </a>
        <a href="{{ route('admin.system.webhook.logs') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            로그 보기
        </a>
    </div>
</div>
@endsection
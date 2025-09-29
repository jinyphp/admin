{{--
    SMS Dashboard View
    SMS 통계, 프로바이더 현황, 큐 상태 등을 표시하는 대시보드
--}}
@extends($jsonData['template']['layout'] ?? 'jiny-admin::layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- 헤더 --}}
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">SMS 대시보드</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">SMS 발송 현황 및 통계를 확인할 수 있습니다.</p>
            </div>
            <div class="flex items-center space-x-4">
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

    {{-- 통계 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- 총 발송 수 --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">총 발송 수</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($stats['total_sent'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 오늘 발송 --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">오늘 발송</h3>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($stats['sent_today'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 성공률 --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">성공률</h3>
                    <p class="text-2xl font-semibold text-green-600 dark:text-green-400">
                        {{ $stats['success_rate'] ?? 0 }}%
                    </p>
                </div>
            </div>
        </div>

        {{-- 대기중인 큐 --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">대기중인 큐</h3>
                    <p class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">
                        {{ number_format($stats['pending_queue'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 빠른 링크 --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">SMS 관리</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="/admin/sms/provider" class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">SMS 프로바이더 관리</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $stats['providers_count'] ?? 0 }}개 활성 프로바이더</p>
                </div>
            </a>

            <a href="/admin/sms/send" class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">SMS 발송 관리</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">발송 기록 및 상태 확인</p>
                </div>
            </a>

            <a href="/admin/sms/queue" class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">SMS 큐 관리</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ ($stats['pending_queue'] ?? 0) + ($stats['processing_queue'] ?? 0) }}개 처리 대기</p>
                </div>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- 일별 발송 통계 차트 --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">최근 7일 발송 현황</h2>
            <div class="space-y-3">
                @foreach($dailyStats ?? [] as $stat)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 dark:text-gray-400">{{ $stat['date'] }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $stat['total'] }}건</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        @php
                            $totals = array_column($dailyStats ?? [], 'total');
                            $maxCount = !empty($totals) ? max($totals) : 1;
                            $maxCount = max($maxCount, 1);
                            $percentage = ($stat['total'] / $maxCount) * 100;
                        @endphp
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 프로바이더별 통계 --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">프로바이더별 사용 현황</h2>
            <div class="space-y-3">
                @forelse($providerStats ?? [] as $provider)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 dark:text-gray-400">{{ $provider->provider ?: '미지정' }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($provider->count) }}건</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        성공 {{ number_format($provider->success_count) }}건 | 
                        평균 비용 {{ number_format($provider->avg_cost, 2) }}원
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-500 dark:text-gray-400">프로바이더 사용 기록이 없습니다.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 큐 상태 및 최근 발송 기록 --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- 큐 상태 --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">큐 상태</h2>
            <div class="space-y-2">
                @forelse($queueStats ?? [] as $queue)
                <div class="flex justify-between items-center">
                    <span class="text-xs">
                        @if($queue->status == 'pending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                대기중
                            </span>
                        @elseif($queue->status == 'processing')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                처리중
                            </span>
                        @elseif($queue->status == 'completed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                완료
                            </span>
                        @elseif($queue->status == 'failed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                실패
                            </span>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $queue->status }}</span>
                        @endif
                    </span>
                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ number_format($queue->count) }}건</span>
                </div>
                @empty
                <p class="text-xs text-gray-500 dark:text-gray-400">큐에 데이터가 없습니다.</p>
                @endforelse
            </div>
        </div>

        {{-- 최근 발송 기록 --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">최근 SMS 발송 기록</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">수신자</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">프로바이더</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">상태</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">발송시간</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentSms ?? [] as $sms)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">
                                {{ substr($sms->to_number ?? '', 0, 3) }}****{{ substr($sms->to_number ?? '', -4) }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                {{ $sms->provider_name ?? '미지정' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                @if($sms->status == 'success')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">성공</span>
                                @elseif($sms->status == 'failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">실패</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $sms->status ?? '알수없음' }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($sms->created_at)->format('m/d H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                                발송 기록이 없습니다.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 추가 정보 --}}
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">시스템 정보</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-600 dark:text-gray-400">이번 주 발송</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['sent_this_week'] ?? 0) }}건</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-600 dark:text-gray-400">이번 달 발송</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['sent_this_month'] ?? 0) }}건</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-600 dark:text-gray-400">총 발송 비용</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_cost'] ?? 0) }}원</dd>
            </div>
        </div>
    </div>
</div>
@endsection
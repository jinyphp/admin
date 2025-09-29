@extends('jiny-admin::layouts.admin')

@section('title', '메일 관리 대시보드')

@section('content')
<div class="p-6">
    {{-- 헤더 영역: 타이틀과 버튼을 좌우로 배치 --}}
    <div class="mb-6 flex justify-between items-start">
        {{-- 왼쪽: 타이틀과 설명 --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">메일 관리 대시보드</h1>
            <p class="mt-1 text-sm text-gray-600">이메일 발송 현황 및 통계를 확인합니다</p>
        </div>

        {{-- 오른쪽: 액션 버튼들 --}}
        <div class="flex space-x-2">
            <a href="{{ route('admin.mail.logs.create') }}" 
               class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                새 메일 작성
            </a>
            <a href="{{ route('admin.mail.templates') }}" 
               class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                템플릿 관리
            </a>
            <a href="{{ route('admin.mail.tracking') }}" 
               class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                이메일 추적
            </a>
            <a href="{{ route('admin.mail.setting') }}" 
               class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                메일 설정
            </a>
        </div>
    </div>

    {{-- 주요 지표 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {{-- 오늘 발송 --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">오늘 발송</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($todayStats->total ?? 0) }}</p>
                    @if($todayStats && $todayStats->total > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            성공 {{ $todayStats->sent ?? 0 }} / 실패 {{ $todayStats->failed ?? 0 }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- 대기중 --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">대기중</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingCount) }}</p>
                    @if($pendingCount > 0)
                        <a href="{{ route('admin.mail.logs') }}?status=pending" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                            확인하기 →
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- 최근 실패 --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">최근 실패 (24h)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($recentFailures) }}</p>
                    @if($recentFailures > 0)
                        <a href="{{ route('admin.mail.logs') }}?status=failed" class="text-xs text-red-600 hover:text-red-800 mt-1">
                            처리하기 →
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- 이메일 추적 --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">이메일 추적</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($openRateStats->total_sent ?? 0) }}</p>
                    @if($openRateStats && $openRateStats->total_sent > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            열람 {{ $openRateStats->total_opened ?? 0 }} / 클릭 {{ $openRateStats->total_clicked ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-400">
                            열람률 {{ $openRateStats->open_rate ?? 0 }}%
                        </p>
                    @endif
                    <a href="{{ route('admin.mail.tracking') }}" class="text-xs text-purple-600 hover:text-purple-800 mt-1">
                        상세보기 →
                    </a>
                </div>
            </div>
        </div>

        {{-- 활성 템플릿 --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">활성 템플릿</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activeTemplates) }}</p>
                    <a href="{{ route('admin.mail.templates') }}" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                        관리하기 →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- 발송 통계 --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">발송 통계</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    {{-- 이번주 --}}
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">이번주</span>
                            <span class="font-medium text-gray-900">{{ number_format($weekStats->total ?? 0) }}건</span>
                        </div>
                        @if($weekStats && $weekStats->total > 0)
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($weekStats->sent / $weekStats->total) * 100 }}%"></div>
                            </div>
                            <div class="mt-1 flex justify-between text-xs text-gray-500">
                                <span>성공: {{ number_format($weekStats->sent ?? 0) }}</span>
                                <span>실패: {{ number_format($weekStats->failed ?? 0) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- 이번달 --}}
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">이번달</span>
                            <span class="font-medium text-gray-900">{{ number_format($monthStats->total ?? 0) }}건</span>
                        </div>
                        @if($monthStats && $monthStats->total > 0)
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($monthStats->sent / $monthStats->total) * 100 }}%"></div>
                            </div>
                            <div class="mt-1 flex justify-between text-xs text-gray-500">
                                <span>성공: {{ number_format($monthStats->sent ?? 0) }}</span>
                                <span>실패: {{ number_format($monthStats->failed ?? 0) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 일별 추이 차트 (간단한 바 차트) --}}
                @if($dailyTrend->count() > 0)
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">최근 7일 발송 추이</h3>
                        <div class="space-y-3">
                            {{-- 차트 영역 --}}
                            <div class="flex items-end gap-2" style="height: 120px;">
                                @php
                                    // 최근 7일 데이터가 없는 날짜도 포함하여 생성
                                    $chartData = [];
                                    for ($i = 6; $i >= 0; $i--) {
                                        $date = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');
                                        $dayData = $dailyTrend->firstWhere('date', $date);
                                        $chartData[] = [
                                            'date' => $date,
                                            'total' => $dayData ? $dayData->total : 0,
                                            'sent' => $dayData ? $dayData->sent : 0,
                                            'failed' => $dayData ? $dayData->failed : 0
                                        ];
                                    }
                                    $maxHeight = collect($chartData)->max('total') ?: 1;
                                @endphp
                                
                                @foreach($chartData as $day)
                                    @php
                                        $heightPercent = ($day['total'] / $maxHeight) * 100;
                                        $sentPercent = $day['total'] > 0 ? ($day['sent'] / $day['total']) * 100 : 0;
                                    @endphp
                                    <div class="flex-1 flex flex-col items-center">
                                        {{-- 막대 그래프 --}}
                                        <div class="relative w-full flex items-end justify-center" style="height: 100px;">
                                            <div class="relative" style="width: 80%;">
                                                @if($day['total'] > 0)
                                                    {{-- 전체 높이 (회색 배경) --}}
                                                    <div class="w-full bg-gray-200 rounded relative" 
                                                         style="height: {{ $heightPercent }}px;"
                                                         title="{{ \Carbon\Carbon::parse($day['date'])->format('m/d') }}: 총 {{ $day['total'] }}건 (성공: {{ $day['sent'] }}, 실패: {{ $day['failed'] }})">
                                                        {{-- 성공한 부분 (파란색) --}}
                                                        <div class="absolute bottom-0 w-full bg-blue-500 rounded-b" 
                                                             style="height: {{ $sentPercent }}%;"></div>
                                                    </div>
                                                @else
                                                    {{-- 데이터가 없는 날 --}}
                                                    <div class="w-full bg-gray-100 rounded" 
                                                         style="height: 2px;"
                                                         title="{{ \Carbon\Carbon::parse($day['date'])->format('m/d') }}: 발송 없음"></div>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- 수치 표시 --}}
                                        <span class="text-xs text-gray-700 font-medium mt-1">
                                            {{ $day['total'] > 0 ? $day['total'] : '-' }}
                                        </span>
                                        {{-- 날짜 표시 --}}
                                        <span class="text-xs text-gray-500 mt-1">
                                            {{ \Carbon\Carbon::parse($day['date'])->format('m/d') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            
                            {{-- 범례 --}}
                            <div class="flex items-center justify-center space-x-4 text-xs mt-3">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-1"></div>
                                    <span class="text-gray-600">성공</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-gray-400 rounded mr-1"></div>
                                    <span class="text-gray-600">실패</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-gray-100 rounded mr-1"></div>
                                    <span class="text-gray-600">데이터 없음</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 최근 발송 내역 --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">최근 발송 내역</h2>
                <a href="{{ route('admin.mail.logs') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    전체보기 →
                </a>
            </div>
            <div class="overflow-hidden">
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 truncate" style="max-width: 200px;">
                                            {{ $log->to_email }}
                                        </div>
                                        <div class="text-gray-500 truncate" style="max-width: 200px;">
                                            {{ $log->subject }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-gray-100 text-gray-800',
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'sent' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'bounced' => 'bg-yellow-100 text-yellow-800',
                                            'opened' => 'bg-indigo-100 text-indigo-800',
                                            'clicked' => 'bg-purple-100 text-purple-800'
                                        ];
                                        $statusLabels = [
                                            'pending' => '대기중',
                                            'processing' => '처리중',
                                            'sent' => '발송완료',
                                            'failed' => '실패',
                                            'bounced' => '반송',
                                            'opened' => '열람',
                                            'clicked' => '클릭'
                                        ];
                                        $color = $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800';
                                        $label = $statusLabels[$log->status] ?? $log->status;
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                        {{ $label }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-8 text-center text-sm text-gray-500">
                                    발송 내역이 없습니다.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 템플릿 사용 통계 --}}
    @if($templateStats->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">템플릿 사용 통계 (이번달)</h2>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($templateStats as $stat)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-900">
                                {{ $stat->template->name ?? 'Template #' . $stat->template_id }}
                            </span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($stat->usage_count / $templateStats->first()->usage_count) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 w-12 text-right">{{ $stat->usage_count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
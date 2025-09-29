<div>
    {{-- 페이지 헤더 --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">CAPTCHA 로그</h1>
        <p class="mt-1 text-sm text-gray-500">CAPTCHA 인증 시도 로그 및 분석</p>
    </div>

    {{-- 통계 카드 --}}
    @include('jiny-admin::admin.admin_captcha_logs.stats')

    {{-- 검색 및 필터 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-200">
            @include('jiny-admin::admin.admin_captcha_logs.search')
        </div>
    </div>

    {{-- 탭 네비게이션 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6 px-4" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'logs')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'logs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    로그 목록
                </button>
                <button wire:click="$set('activeTab', 'chart')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'chart' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    시간대별 차트
                </button>
                <button wire:click="$set('activeTab', 'ip')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'ip' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    IP별 통계
                </button>
            </nav>
        </div>

        {{-- 탭 컨텐츠 --}}
        <div class="p-4">
            @if($activeTab === 'logs')
                {{-- 테이블 --}}
                @include('jiny-admin::admin.admin_captcha_logs.table')
                
                {{-- 페이지네이션 --}}
                <div class="mt-4">
                    {{ $rows->links() }}
                </div>
            @elseif($activeTab === 'chart')
                {{-- 시간대별 차트 --}}
                <div class="bg-white rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">시간대별 CAPTCHA 시도</h3>
                    <p class="text-xs text-gray-500 mb-4">24시간 기준 CAPTCHA 인증 시도 분포</p>
                    
                    @if(!empty($hourlyAnalysis))
                        <div class="space-y-2">
                            @php
                                $maxCount = max(array_column($hourlyAnalysis, 'count'));
                            @endphp
                            @foreach($hourlyAnalysis as $hour)
                                <div class="flex items-center">
                                    <span class="text-xs text-gray-500 w-12">{{ str_pad($hour['hour'], 2, '0', STR_PAD_LEFT) }}시</span>
                                    <div class="flex-1 ml-2">
                                        <div class="bg-gray-200 rounded-full h-4">
                                            <div class="bg-blue-500 h-4 rounded-full" 
                                                 style="width: {{ $maxCount > 0 ? ($hour['count'] / $maxCount * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-900 ml-2 w-12 text-right">{{ $hour['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-500 text-center py-8">데이터가 없습니다.</p>
                    @endif
                </div>
            @elseif($activeTab === 'ip')
                {{-- IP별 통계 --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">IP 주소</th>
                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">총 시도</th>
                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">성공</th>
                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">실패</th>
                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">미입력</th>
                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">성공률</th>
                                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">상태</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($ipStatistics as $ip => $stats)
                                @php
                                    $successRate = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 0;
                                    $isSuspicious = $stats['failed'] > 5 || ($stats['total'] > 10 && $stats['success'] == 0);
                                @endphp
                                <tr class="hover:bg-gray-50 {{ $isSuspicious ? 'bg-red-50' : '' }}">
                                    <td class="px-3 py-2.5 whitespace-nowrap text-xs font-mono text-gray-900">
                                        {{ $ip }}
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                                        {{ $stats['total'] }}
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-green-600">
                                        {{ $stats['success'] }}
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-red-600">
                                        {{ $stats['failed'] }}
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-yellow-600">
                                        {{ $stats['missing'] }}
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <span class="text-xs font-medium {{ $successRate < 50 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $successRate }}%
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        @if($isSuspicious)
                                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800">
                                                의심
                                            </span>
                                        @else
                                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                                정상
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-4 text-center text-xs text-gray-500">
                                        IP 통계가 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- 자동 새로고침 스크립트 --}}
    @if($autoRefresh)
        <script>
            setTimeout(function() {
                @this.call('loadData');
            }, 30000); // 30초마다 새로고침
        </script>
    @endif
</div>
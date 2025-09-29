{{--
    Admin Password Logs 검색 폼
    비밀번호 로그 검색 및 필터링
    그리드 레이아웃으로 2-3줄로 압축
--}}
<div class="space-y-3">
    {{-- 첫번째 줄: 검색어와 상태 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        {{-- 검색 입력 --}}
        <div class="lg:col-span-2">
            <label for="search" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                검색
            </label>
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       id="search"
                       class="w-full px-3 py-2 pl-9 text-xs border border-gray-300 dark:border-gray-600 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100"
                       placeholder="이메일, IP 주소, 브라우저로 검색...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- 상태 필터 --}}
        <div>
            <label for="filter_status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                상태
            </label>
            <select wire:model.live="filters.status"
                    id="filter_status"
                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100">
                <option value="">전체 상태</option>
                <option value="failed">실패한 시도</option>
                <option value="blocked">차단된 IP</option>
                <option value="resolved">해결됨</option>
            </select>
        </div>
    </div>

    {{-- 두번째 줄: 날짜 범위, 시도 횟수, IP 주소 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- 날짜 범위 필터 --}}
        <div class="sm:col-span-2 lg:col-span-2">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                날짜 범위
            </label>
            <div class="grid grid-cols-2 gap-2">
                <input type="date"
                       wire:model.live="filters.date_from"
                       class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100"
                       placeholder="시작일">
                <input type="date"
                       wire:model.live="filters.date_to"
                       class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100"
                       placeholder="종료일">
            </div>
        </div>

        {{-- 시도 횟수 필터 --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                시도 횟수
            </label>
            <div class="grid grid-cols-2 gap-1">
                <input type="number"
                       wire:model.live="filters.attempt_min"
                       min="1"
                       class="w-full px-2 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100"
                       placeholder="최소">
                <input type="number"
                       wire:model.live="filters.attempt_max"
                       min="1"
                       class="w-full px-2 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100"
                       placeholder="최대">
            </div>
        </div>

        {{-- IP 주소 필터 --}}
        <div>
            <label for="filter_ip" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                IP 주소
            </label>
            <input type="text"
                   wire:model.live.debounce.300ms="filters.ip_address"
                   id="filter_ip"
                   class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100"
                   placeholder="예: 192.168.1.1">
        </div>
    </div>

    {{-- 필터 초기화 버튼 (조건부 표시) --}}
    @if(count(array_filter($filters ?? [])) > 0)
    <div class="flex justify-end">
        <button wire:click="resetFilters"
                type="button"
                class="px-4 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            필터 초기화
        </button>
    </div>
    @endif
</div>

{{-- 빠른 통계 --}}
<div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
    <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-3">빠른 통계</h4>
    <div class="space-y-2">
        @if(isset($stats))
        <div class="flex justify-between text-xs">
            <span class="text-gray-600 dark:text-gray-400">전체 기록:</span>
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-gray-600 dark:text-gray-400">차단된 IP:</span>
            <span class="font-medium text-red-600 dark:text-red-400">{{ number_format($stats['blocked'] ?? 0) }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-gray-600 dark:text-gray-400">실패한 시도:</span>
            <span class="font-medium text-yellow-600 dark:text-yellow-400">{{ number_format($stats['failed'] ?? 0) }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-gray-600 dark:text-gray-400">해결됨:</span>
            <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($stats['resolved'] ?? 0) }}</span>
        </div>
        @endif
    </div>
</div>
{{--
    이메일 발송 로그 테이블 래퍼
    
    @package jiny/admin
    @subpackage admin_email_logs
    @description 이메일 로그 테이블을 감싸는 래퍼입니다. 대량 작업 버튼을 포함합니다.
    @version 1.0
--}}
<div class="mt-6">
    {{-- 성공/오류 메시지 --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- 선택된 항목 정보 및 대량 작업 버튼 --}}
    @if($selectedCount > 0)
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex justify-between items-center">
            <span class="text-xs text-blue-700">
                {{ $selectedCount }}개 항목이 선택되었습니다.
            </span>
            <div class="flex space-x-2">
                {{-- 선택된 항목 중 대기중인 이메일이 있는 경우 대량 발송 버튼 표시 --}}
                @if($rows->whereIn('id', $selected)->where('status', 'pending')->count() > 0)
                <button wire:click="hookCustom('bulkSend', {'ids': @js($selected)})"
                        wire:confirm="선택된 대기중 이메일 {{ $rows->whereIn('id', $selected)->where('status', 'pending')->count() }}개를 발송하시겠습니까?"
                        wire:loading.attr="disabled"
                        wire:target="hookCustom"
                        class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-green-600 border border-transparent rounded hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg wire:loading.remove wire:target="hookCustom" class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <svg wire:loading wire:target="hookCustom" class="animate-spin h-3.5 w-3.5 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="hookCustom">선택 발송</span>
                    <span wire:loading wire:target="hookCustom">발송 중...</span>
                </button>
                @endif
                
                {{-- 선택 삭제 버튼 --}}
                <button wire:click="requestDeleteSelected"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-red-600 border border-transparent rounded hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    선택 삭제
                </button>
            </div>
        </div>
    @endif

    {{-- 대량 작업 진행 중 오버레이 --}}
    <div wire:loading.flex wire:target="hookCustom" class="fixed inset-0 bg-gray-900/50 z-50 items-center justify-center">
        <div class="bg-white rounded p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-lg font-medium text-gray-900">이메일 발송 중...</span>
            </div>
            <p class="mt-2 text-sm text-gray-600">잠시만 기다려 주세요.</p>
        </div>
    </div>

    {{-- 테이블 --}}
    @include('jiny-admin::admin.admin_email_logs.table')

    {{-- 페이지네이션 및 결과 정보 --}}
    <div class="mt-4">
        {{-- 결과 정보 표시 --}}
        <div class="text-sm text-gray-700 mb-4">
            총 <span class="font-semibold">{{ $rows->total() }}</span>개 중 
            <span class="font-semibold">{{ $rows->firstItem() ?? 0 }}</span>번째부터 
            <span class="font-semibold">{{ $rows->lastItem() ?? 0 }}</span>번째까지 표시
        </div>

        {{-- 페이지네이션 --}}
        {{ $rows->links() }}
    </div>
</div>
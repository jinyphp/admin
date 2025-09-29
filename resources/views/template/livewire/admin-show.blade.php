<div class="">

    {{-- 상세보기 컨덴츠 --}}
    @if(isset($jsonData['show']['showPath']) && !empty($jsonData['show']['showPath']))
        @includeIf($jsonData['show']['showPath'])
    @else
        @include('jiny-admin::template.components.config-error', [
            'title' => '상세보기 설정 오류',
            'config' => 'show.showPath'
        ])
    @endif

    {{-- 하단 버튼 영역 --}}
    <div class="py-2 flex justify-between items-center">
        {{-- 왼쪽: 삭제 버튼 --}}
        <div>
            @if($jsonData['show']['enableDelete'] ?? true)
                @php
                    $deleteId = $itemId ?? $data->id ?? null;
                @endphp
                <button wire:click="requestDelete"
                        class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-red-600 border border-transparent rounded hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    삭제
                </button>
            @endif
        </div>

        {{-- 오른쪽: 수정 버튼 --}}
        <div>
            @if($jsonData['show']['enableEdit'] ?? true)
                @php
                    $id = $itemId ?? $data->id ?? null;
                    $editRoute = isset($jsonData['route']['name']) && $id
                        ? route($jsonData['route']['name'] . '.edit', $id)
                        : "/admin2/templates/{$id}/edit";
                @endphp
                <a href="{{ $editRoute }}"
                   class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-blue-600 border border-transparent rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    수정
                </a>
            @endif
        </div>
    </div>
</div>

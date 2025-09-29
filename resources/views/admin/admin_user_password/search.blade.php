{{-- 패스워드 히스토리 검색 폼 --}}
<div class="bg-white p-4 rounded-lg shadow-sm mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- 검색어 입력 --}}
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                검색어
            </label>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   id="search"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="이름, 이메일 또는 변경사유로 검색...">
        </div>

        {{-- 만료 상태 필터 --}}
        <div>
            <label for="filter_expired" class="block text-sm font-medium text-gray-700 mb-1">
                만료 상태
            </label>
            <select wire:model.live="filters.is_expired" 
                    id="filter_expired"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">전체</option>
                <option value="1">만료됨</option>
                <option value="0">활성</option>
            </select>
        </div>

        {{-- 임시 패스워드 필터 --}}
        <div>
            <label for="filter_temporary" class="block text-sm font-medium text-gray-700 mb-1">
                패스워드 유형
            </label>
            <select wire:model.live="filters.is_temporary" 
                    id="filter_temporary"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">전체</option>
                <option value="1">임시</option>
                <option value="0">일반</option>
            </select>
        </div>
    </div>

    {{-- 검색 결과 정보 --}}
    @if($search || !empty(array_filter($filters ?? [])))
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                @if(isset($total))
                    총 <span class="font-semibold">{{ $total }}</span>개의 결과
                @endif
            </div>
            <button wire:click="resetFilters" 
                    class="text-sm text-indigo-600 hover:text-indigo-900">
                필터 초기화
            </button>
        </div>
    @endif
</div>
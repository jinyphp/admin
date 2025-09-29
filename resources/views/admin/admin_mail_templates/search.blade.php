{{-- Mail_templates 검색 폼 --}}
<div class="space-y-3">
    {{-- 검색어 입력 --}}
    <div>
        <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
            Search
        </label>
        <input type="text" 
               wire:model.live.debounce.300ms="search" 
               id="search" 
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Search by name, description...">
    </div>

    {{-- 상태 필터 --}}
    <div>
        <label for="filter_status" class="block text-xs font-medium text-gray-700 mb-1">
            Status Filter
        </label>
        <select wire:model.live="filters.status" 
                id="filter_status" 
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    {{-- 날짜 범위 필터 --}}
    <div class="grid grid-cols-2 gap-2">
        <div>
            <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">
                From Date
            </label>
            <input type="date" 
                   wire:model.live="filters.date_from" 
                   id="date_from" 
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">
                To Date
            </label>
            <input type="date" 
                   wire:model.live="filters.date_to" 
                   id="date_to" 
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{-- 추가 필터들을 여기에 추가하세요 --}}
    {{-- 
    <div>
        <label for="filter_category" class="block text-xs font-medium text-gray-700 mb-1">
            Category
        </label>
        <select wire:model.live="filters.category" 
                id="filter_category" 
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Categories</option>
            <option value="category1">Category 1</option>
            <option value="category2">Category 2</option>
        </select>
    </div>
    --}}

    {{-- 필터 초기화 버튼 --}}
    @if($search || !empty(array_filter($filters ?? [])))
    <div class="pt-2">
        <button wire:click="resetFilters" 
                type="button"
                class="w-full px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-gray-400">
            Clear All Filters
        </button>
    </div>
    @endif
</div>
    <div class="flex flex-col lg:flex-row gap-2">
        {{-- 검색 입력 필드 --}}
        <div class="flex-1">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                    <svg class="h-3.5 w-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="코드, 이름으로 검색..."
                    class="block w-full h-8 pl-8 pr-2.5 text-xs border border-gray-200 rounded bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
            </div>
        </div>

        {{-- 상태 필터 --}}
        <div class="lg:w-32">
            <select wire:model.live="filter.enable"
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 bg-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out appearance-none cursor-pointer">
                <option value="">모든 상태</option>
                <option value="1">✓ 활성화</option>
                <option value="0">✗ 비활성화</option>
            </select>
        </div>

        {{-- 정렬 옵션 --}}
        <div class="lg:w-28">
            <select wire:model.live="sortBy"
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 bg-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out appearance-none cursor-pointer">
                <option value="created_at">최신순</option>
                <option value="name">이름순</option>
                <option value="level">레벨순</option>
                <option value="pos">정렬순</option>
            </select>
        </div>



        {{-- 초기화 버튼 --}}
        @if ($search || !empty($filter['enable']))
            <button wire:click="resetSearch" type="button"
                class="inline-flex items-center h-8 px-3 bg-white border border-gray-200 rounded text-xs font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <svg class="h-3.5 w-3.5 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                        clip-rule="evenodd" />
                </svg>
                초기화
            </button>
        @endif
    </div>

    {{-- 검색 결과 정보 --}}
    @if ($search || !empty($filter['enable']))
        <div class="mt-2 flex items-center gap-2">
            <span class="text-xs text-gray-500">검색 조건:</span>
            @if ($search)
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                    <svg class="mr-1 h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $search }}
                    <button wire:click="$set('search', '')" class="ml-1.5 hover:text-blue-900 focus:outline-none">
                        <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            @endif
            @if (!empty($filter['enable']))
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $filter['enable'] == '1' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                    {{ $filter['enable'] == '1' ? '활성화' : '비활성화' }}
                    <button wire:click="$set('filter.enable', '')" class="ml-1.5 hover:opacity-75 focus:outline-none">
                        <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            @endif
        </div>
    @endif

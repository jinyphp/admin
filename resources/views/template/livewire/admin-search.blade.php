<div>
    {{--
    검색 폼 템플릿 레이아웃

    이 템플릿은 검색 폼의 전체 레이아웃을 담당합니다.
    실제 검색 필드는 searchFormPath로 지정된 파일을 include하여 구성됩니다.

    사용 방법:
    1. jsonData['index']['searchFormPath']에 검색 필드 뷰 경로 지정
    2. 해당 뷰 파일에서 실제 input 필드들을 구성
    3. wire:model을 통해 Livewire와 데이터 바인딩

    예시:
    'searchFormPath' => 'admin.admin_templates.search'
--}}
    <section class="bg-white  shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
        @if (isset($jsonData['index']['searchFormPath']) && !empty($jsonData['index']['searchFormPath']))
            <form wire:submit="search">
                {{--
                검색 입력 필드 영역
                이 곳에 실제 검색 필드들이 include됩니다.
                searchFormPath로 지정된 파일에서 다음과 같은 구조로 작성하세요:

                - 검색 필드들은 grid 레이아웃 사용 권장
                - wire:model.live.debounce를 사용하여 실시간 검색 구현
                - 각 필드는 label과 input을 포함
                - border가 있는 input 스타일 사용
            --}}
                <div class="p-6">
                    @includeIf($jsonData['index']['searchFormPath'])
                </div>

                {{--
                액션 버튼 영역
                초기화와 검색 버튼을 우측 정렬로 배치
            --}}
                <div class="px-6 py-3 flex justify-between space-x-2">
                    <div>
                        {{-- 페이지당 개수 --}}
                        <select wire:model.live="perPage"
                            class="block w-20 h-8 px-2.5 text-xs border border-gray-200 bg-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none cursor-pointer">
                            @php
                                $perPageOptions = $jsonData['index']['pagination']['perPageOptions'] ?? [10, 25, 50, 100];
                            @endphp
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}" @if($option == $perPage) selected @endif>{{ $option }}개</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" wire:click="resetFilters"
                            class="inline-flex items-center h-8 px-3 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            초기화
                        </button>
                        <button type="submit"
                            class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-gray-800 border border-transparent rounded hover:bg-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-600 transition-colors">
                            <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            검색
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="p-6">
                @include('jiny-admin::template.components.config-error', [
                    'title' => '검색 폼 설정 오류',
                    'config' => 'index.searchFormPath',
                ])
            </div>
        @endif
    </section>
</div>

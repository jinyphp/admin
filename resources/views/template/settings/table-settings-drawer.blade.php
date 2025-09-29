<div wire:ignore.self>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('refresh-page', () => {
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });
        });
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Drawer -->
    @if($isOpen)
    <div class="fixed inset-0 z-[9999] overflow-hidden"
         aria-labelledby="drawer-title" 
         role="dialog" 
         aria-modal="true"
         x-data="{ show: @entangle('isOpen').live }"
         x-init="$watch('show', value => {
             if (value) {
                 document.body.style.overflow = 'hidden';
             } else {
                 document.body.style.overflow = '';
             }
         })"
         x-show="show"
         x-cloak
         style="display: none;">
        
        <!-- Background overlay -->
        <div x-show="show" 
             x-transition:enter="ease-in-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-500"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-gray-900/50 transition-opacity backdrop-blur-sm"
             @click="$wire.close()"></div>

        <!-- Drawer panel -->
        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex z-10">
            <div x-show="show"
                 x-transition:enter="transform transition ease-in-out duration-500"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="relative w-screen max-w-md z-10">
                
                <div class="h-full flex flex-col bg-white shadow-xl">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-white" id="drawer-title">
                                {{ $settings['index']['settingsDrawer']['title'] ?? '테이블 설정' }}
                            </h2>
                            <button wire:click="close" 
                                    class="ml-3 text-blue-100 hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-blue-100">
                            {{ $settings['index']['settingsDrawer']['description'] ?? '테이블 표시 옵션을 사용자 정의할 수 있습니다' }}
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <div class="space-y-6">
                            <!-- Pagination Settings -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">페이지네이션</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label for="perPage" class="block text-sm font-medium text-gray-700">페이지당 항목 수</label>
                                        <select wire:model="perPage" id="perPage" 
                                                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            @foreach($perPageOptions as $option)
                                                <option value="{{ $option }}" @if($option == $perPage) selected @endif>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Sorting Settings -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">정렬</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label for="sortField" class="block text-sm font-medium text-gray-700">기본 정렬 필드</label>
                                        <select wire:model="sortField" id="sortField" 
                                                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="id">ID</option>
                                            <option value="title">제목</option>
                                            <option value="enable">상태</option>
                                            <option value="created_at">생성일</option>
                                            <option value="updated_at">수정일</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="sortDirection" class="block text-sm font-medium text-gray-700">정렬 방향</label>
                                        <select wire:model="sortDirection" id="sortDirection" 
                                                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="asc">오름차순</option>
                                            <option value="desc">내림차순</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature Toggles -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">기능</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input wire:model="enableSearch" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">검색 기능 사용</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableBulkActions" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">일괄 작업 사용</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enablePagination" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">페이지네이션 사용</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableStatusToggle" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">상태 토글 사용</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Visible Columns -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">표시할 컬럼</h3>
                                <div class="space-y-2">
                                    @if(isset($settings['index']['table']['columns']))
                                        @foreach($settings['index']['table']['columns'] as $key => $column)
                                            @if(isset($column['label']))
                                                <label class="flex items-center">
                                                    <input wire:model="visibleColumns" type="checkbox" value="{{ $key }}"
                                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $column['label'] }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    @else
                                        @foreach(['checkbox' => '체크박스', 'id' => 'ID', 'title' => '제목', 'description' => '설명', 'enable' => '상태', 'created_at' => '생성일', 'actions' => '작업'] as $key => $label)
                                        <label class="flex items-center">
                                            <input wire:model="visibleColumns" type="checkbox" value="{{ $key }}"
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between">
                            <button wire:click="resetToDefaults" type="button" 
                                    class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                                기본값으로 재설정
                            </button>
                            <div class="space-x-3">
                                <button wire:click="close" type="button" 
                                        class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                                    취소
                                </button>
                                <button wire:click="save" type="button" 
                                        class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                                    설정 저장
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
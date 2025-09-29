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
                    <div class="px-6 py-4 bg-gradient-to-r from-orange-500 to-orange-600">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-white" id="drawer-title">
                                편집 설정
                            </h2>
                            <button wire:click="close" 
                                    class="ml-3 text-orange-100 hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-orange-100">
                            편집 폼 옵션을 사용자 정의할 수 있습니다
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <div class="space-y-6">
                            <!-- Form Layout Settings -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">폼 레이아웃</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label for="formLayout" class="block text-sm font-medium text-gray-700">레이아웃 스타일</label>
                                        <select wire:model="formLayout" id="formLayout" 
                                                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="vertical">수직</option>
                                            <option value="horizontal">수평</option>
                                            <option value="inline">인라인</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Features -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">편집 기능</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input wire:model="enableDelete" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">삭제 버튼 사용</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableListButton" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">목록 버튼 표시</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableDetailButton" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">상세보기 버튼 표시</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableSettingsDrawer" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">설정 서랍 사용</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="includeTimestamps" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">타임스탬프 포함</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Advanced Options -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">고급 옵션</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input wire:model="enableFieldToggle" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">필드 토글 기능 사용</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableValidationRules" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">유효성 검사 규칙 표시</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableChangeTracking" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">변경 사항 추적 기능 사용</span>
                                    </label>
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
                                        class="inline-flex items-center h-8 px-3 bg-orange-600 text-white text-xs font-medium rounded hover:bg-orange-700 focus:outline-none focus:ring-1 focus:ring-orange-500 transition-colors">
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
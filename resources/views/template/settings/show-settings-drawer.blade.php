{{--
    상세보기 설정 서랍 컴포넌트
    
    @package jiny/admin
    @component show-settings-drawer
    @description 상세보기(show) 페이지의 표시 설정을 실시간으로 변경할 수 있는 서랍형 UI입니다.
                날짜 형식, 불린 레이블, 표시 옵션 등을 사용자가 커스터마이징할 수 있습니다.
    
    @settings 설정 가능한 항목
    - 날짜 형식: 날짜와 시간 표시 형식 선택
    - 불린 레이블: true/false 값을 한글로 표시 (예: 활성화/비활성화)
    - 필드 토글: 개별 필드 표시/숨김
    - 섹션 토글: 섹션별 접기/펼치기
    
    @livewire ShowSettingsDrawer 컴포넌트와 연동
    
    @note
    - Alpine.js를 사용한 애니메이션 효과
    - JSON 파일에 설정 저장
    - 페이지 새로고침 시 적용
    
    @version 1.0
--}}
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
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-purple-600">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-white" id="drawer-title">
                                표시 설정
                            </h2>
                            <button wire:click="close" 
                                    class="ml-3 text-purple-100 hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-purple-100">
                            상세보기 옵션을 사용자 맞춤 설정합니다
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <div class="space-y-6">
                            <!-- Date Format Settings -->
                            @if($enableDateFormat)
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">날짜 형식</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label for="dateFormat" class="block text-sm font-medium text-gray-700">날짜/시간 형식</label>
                                        <select wire:model="dateFormat" id="dateFormat" 
                                                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="Y-m-d H:i:s">2024-01-01 12:00:00</option>
                                            <option value="Y-m-d">2024-01-01</option>
                                            <option value="d/m/Y">01/01/2024</option>
                                            <option value="m/d/Y">01/01/2024</option>
                                            <option value="d M Y">01 Jan 2024</option>
                                            <option value="F j, Y">January 1, 2024</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Boolean Labels -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">불린 레이블</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label for="booleanTrueLabel" class="block text-sm font-medium text-gray-700">참(True) 레이블</label>
                                        <input type="text" wire:model="booleanTrueLabel" id="booleanTrueLabel"
                                               placeholder="예: 활성화, 사용중, 켜짐"
                                               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="booleanFalseLabel" class="block text-sm font-medium text-gray-700">거짓(False) 레이블</label>
                                        <input type="text" wire:model="booleanFalseLabel" id="booleanFalseLabel"
                                               placeholder="예: 비활성화, 미사용, 꺼짐"
                                               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Display Options -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">표시 옵션</h3>
                                <div class="space-y-3">
                                    {{-- 필드 토글 --}}
                                    @if($enableFieldToggle)
                                    <label class="flex items-center">
                                        <input wire:model="enableFieldToggle" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">필드 토글 활성화</span>
                                    </label>
                                    @endif
                                    
                                    {{-- 날짜 형식 옵션 --}}
                                    @if($enableDateFormat)
                                    <label class="flex items-center">
                                        <input wire:model="enableDateFormat" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">날짜 형식 옵션 활성화</span>
                                    </label>
                                    @endif
                                    
                                    {{-- 섹션 토글 --}}
                                    @if($enableSectionToggle)
                                    <label class="flex items-center">
                                        <input wire:model="enableSectionToggle" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">섹션 토글 활성화</span>
                                    </label>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">액션 버튼</h3>
                                <div class="space-y-3">
                                    {{-- 수정 버튼 --}}
                                    <label class="flex items-center">
                                        <input wire:model="enableEdit" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">수정 버튼 표시</span>
                                    </label>
                                    
                                    {{-- 삭제 버튼 --}}
                                    <label class="flex items-center">
                                        <input wire:model="enableDelete" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">삭제 버튼 표시</span>
                                    </label>
                                    
                                    {{-- 목록 버튼 --}}
                                    <label class="flex items-center">
                                        <input wire:model="enableListButton" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">목록 버튼 표시</span>
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
                                        class="inline-flex items-center h-8 px-3 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors">
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
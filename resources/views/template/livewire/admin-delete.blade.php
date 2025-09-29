<div>
    <!-- 삭제 확인 모달 -->
    @if($showDeleteModal)
        <!-- Backdrop with proper opacity -->
        <div class="fixed inset-0 z-[9998] bg-gray-900/50 backdrop-blur-sm" wire:click="closeDeleteModal"></div>
        
        <!-- Modal -->
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <!-- Modal Header -->
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900">
                                @if($deleteType === 'single')
                                    항목 삭제
                                @else
                                    다중 항목 삭제
                                @endif
                            </h3>
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-6">
                        @if($deleteType === 'single')
                            <p class="mb-2">이 항목을 삭제하시겠습니까?</p>
                        @else
                            <p class="mb-2">선택된 <span class="font-semibold text-gray-900">{{ $deleteCount }}개</span> 항목을 삭제하시겠습니까?</p>
                        @endif
                        <p>삭제된 데이터는 복구할 수 없습니다. 계속하려면 아래 확인 코드를 입력하세요.</p>
                    </div>
                    
                    <!-- 확인 코드 섹션 -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-600">확인 코드:</span>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center h-8 px-2.5 text-xs font-mono font-bold text-gray-900 bg-white rounded border border-gray-200">
                                    {{ $deleteConfirmKey }}
                                </span>
                                <button type="button" 
                                        wire:click="copyConfirmKey"
                                        class="inline-flex items-center justify-center h-8 w-8 text-gray-500 hover:text-gray-700 bg-white rounded border border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <input type="text" 
                               wire:model.live="deleteConfirmInput"
                               placeholder="확인 코드를 입력하세요"
                               class="w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 text-center font-mono"
                               autocomplete="off">
                    </div>
                    
                    @if($deleteConfirmInput && !$deleteButtonEnabled)
                        <p class="text-sm text-red-600 mb-4">확인 코드가 일치하지 않습니다.</p>
                    @endif
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
                    <button type="button" 
                            wire:click="closeDeleteModal"
                            class="inline-flex items-center h-8 px-3 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        취소
                    </button>
                    <button type="button" 
                            wire:click="executeDelete"
                            @if(!$deleteButtonEnabled) disabled @endif
                            class="inline-flex items-center h-8 px-3 text-xs font-medium text-white rounded focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors
                                   @if($deleteButtonEnabled) bg-red-600 hover:bg-red-700 @else bg-gray-300 cursor-not-allowed @endif">
                        삭제
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
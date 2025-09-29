<div class="w-full">
    {{-- 알림 메시지 --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg transition-opacity">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg transition-opacity">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- 타이틀 헤더 --}}
    <div class="">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="py-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $title }}
                            @if($editable)
                                <button wire:click="openTitleSettings"
                                        class="ml-2 inline-flex items-center p-1 text-gray-400 hover:text-gray-600 transition-colors"
                                        title="타이틀 편집">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            @endif
                        </h1>
                        @if ($subtitle)
                            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <div class="flex items-center space-x-4">
                        {{-- 마지막 업데이트 시간 --}}
                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $lastUpdated }}</span>
                        </div>

                        {{-- 새로고침 버튼 --}}
                        <button wire:click="refreshPage"
                                class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                title="새로고침">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 타이틀 설정 모달 --}}
    @if($showTitleSettingsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:keydown.escape.window="closeTitleSettings">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- 배경 오버레이 --}}
                <div wire:click="closeTitleSettings"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                </div>

                {{-- 모달 콘텐츠 --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="saveTitleSettings">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">타이틀 설정</h3>
                                <p class="mt-1 text-sm text-gray-500">페이지의 타이틀과 설명을 수정합니다.</p>
                            </div>

                            <div class="space-y-4">
                                {{-- 타이틀 입력 --}}
                                <div>
                                    <label for="edit-title" class="block text-sm font-medium text-gray-700 mb-1">
                                        타이틀 <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="edit-title"
                                           wire:model.defer="editTitle"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('editTitle') border-red-500 @enderror"
                                           placeholder="페이지 타이틀을 입력하세요">
                                    @error('editTitle')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 서브타이틀 입력 --}}
                                <div>
                                    <label for="edit-subtitle" class="block text-sm font-medium text-gray-700 mb-1">
                                        설명
                                    </label>
                                    <textarea id="edit-subtitle"
                                              wire:model.defer="editSubtitle"
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('editSubtitle') border-red-500 @enderror"
                                              placeholder="페이지 설명을 입력하세요 (선택사항)"></textarea>
                                    @error('editSubtitle')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                저장
                            </button>
                            <button type="button"
                                    wire:click="closeTitleSettings"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                취소
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- 로딩 상태 표시 --}}
    <div wire:loading wire:target="saveTitleSettings,refreshPage"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg px-6 py-4 flex items-center">
            <svg class="animate-spin h-5 w-5 mr-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700">처리 중...</span>
        </div>
    </div>
</div>

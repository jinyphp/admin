{{--
    목록 페이지 헤더 컴포넌트
    
    @package jiny/admin
    @component admin-header-index
    @description 목록(index) 페이지의 상단 헤더 영역을 표시합니다.
                제목, 설명, 생성 버튼, 설정 버튼을 포함하며,
                제목과 설명을 실시간으로 수정할 수 있는 모달을 제공합니다.
    
    @requires
    - $title: 페이지 제목 (필수)
    - $description: 페이지 설명 (선택)
    - $enableCreate: 생성 버튼 표시 여부 (기본값: true)
    - $createRoute: 생성 페이지 라우트 (선택)
    - $buttonText: 생성 버튼 텍스트 (기본값: '새 항목 추가')
    
    @features
    - 제목 인라인 편집: 제목 옆 설정 아이콘 클릭 시 모달로 수정
    - 생성 버튼: 라우트가 있으면 링크, 없으면 Livewire 이벤트
    - 설정 버튼: DetailSettingsDrawer 컴포넌트 호출
    - 세션 메시지: 성공/오류 메시지 자동 표시
    
    @note
    - Tailwind CSS 스타일 적용
    - Alpine.js 연동 가능
    - JSON 설정과 자동 연동
    
    @version 1.0
--}}
<div>
    {{-- 성공/에러 메시지 표시 --}}
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 inline-flex items-center">
                {{ $title }}
                {{-- 타이틀 설정 아이콘 --}}
                <button type="button"
                        wire:click="openTitleSettings" 
                        class="ml-2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200"
                        title="타이틀 설정">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
            </h1>
            @if($description)
                <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
            @endif
        </div>
        
        <div class="flex items-center space-x-2">
            @if($enableCreate)
                @if($createRoute)
                    <a href="{{ $createRoute }}" 
                       class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500  transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ $buttonText ?? '새 항목 추가' }}
                    </a>
                @else
                    <button wire:click="navigateToCreate" 
                            class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500  transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ $buttonText ?? '새 항목 추가' }}
                    </button>
                @endif
            @endif
            
            <button wire:click="openSettings" 
                    class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500  transition-colors">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                설정
            </button>
        </div>
    </div>

    {{-- 타이틀 설정 팝업 모달 --}}
    @if($showTitleSettingsModal)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- 배경 오버레이 --}}
            <div wire:click="closeTitleSettings" class="fixed inset-0 bg-gray-900/50 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>

            {{-- 중앙 정렬을 위한 스페이서 --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- 모달 패널 --}}
            <div class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                헤더 설정
                            </h3>
                            <div class="mt-4">
                                {{-- 타이틀 입력 필드 --}}
                                <div class="mb-4">
                                    <label for="edit-title" class="block text-sm font-medium text-gray-700">
                                        타이틀
                                    </label>
                                    <input type="text" 
                                           id="edit-title"
                                           wire:model="editTitle" 
                                           class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="페이지 타이틀 입력">
                                </div>
                                
                                {{-- 설명 입력 필드 --}}
                                <div class="mb-4">
                                    <label for="edit-description" class="block text-sm font-medium text-gray-700">
                                        설명
                                    </label>
                                    <textarea id="edit-description"
                                              wire:model="editDescription" 
                                              rows="3"
                                              class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="페이지 설명 입력"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="saveTitleSettings"
                            class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors sm:ml-3">
                        저장
                    </button>
                    <button type="button" 
                            wire:click="closeTitleSettings"
                            class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors sm:mt-0 sm:ml-3">
                        취소
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
{{--
    생성 페이지 헤더 컴포넌트
    
    @package jiny/admin
    @component admin-header-create
    @description 생성(create) 페이지의 상단 헤더 영역을 표시합니다.
                제목, 설명, 목록 버튼, 설정 버튼을 포함합니다.
    
    @requires
    - $title: 페이지 제목 (선택, 기본값: '새 항목 생성')
    - $description: 페이지 설명 (선택)
    - $listRoute: 목록 페이지 라우트 (선택)
    - $data: JSON 설정 데이터 (선택)
    
    @features
    - 목록 버튼: 목록 페이지로 이동
    - 설정 버튼: CreateSettingsDrawer 컴포넌트 호출
    - JSON 설정 기반 버튼 표시 제어
    
    @settings JSON 설정을 통한 제어
    - create.enableListButton: 목록 버튼 표시 여부 (기본값: true)
    - create.enableSettingsDrawer: 설정 서랍 활성화 (기본값: true)
    
    @note
    - Tailwind CSS 스타일 적용
    - Livewire 컴포넌트와 연동
    - JSON 설정과 자동 연동
    
    @version 1.0
--}}
<div class="flex justify-between items-center mb-6">
    {{-- 제목 영역 --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $title ?? '새 항목 생성' }}</h1>
        @if($description)
            <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
        @endif
    </div>
    
    {{-- 버튼 영역 --}}
    <div class="flex items-center space-x-2">
        {{-- 목록 버튼: JSON 설정이 있으면 설정값, 없으면 기본으로 표시 --}}
        @if(($data['create']['enableListButton'] ?? null) !== false)
            @if($listRoute)
                {{-- 라우트가 있으면 링크로 처리 --}}
                <a href="{{ $listRoute }}" 
                   class="inline-flex items-center h-8 px-3 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-500 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    목록
                </a>
            @else
                {{-- 라우트가 없으면 Livewire 이벤트로 처리 --}}
                <button wire:click="navigateToList" 
                        class="inline-flex items-center h-8 px-3 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-500 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    목록
                </button>
            @endif
        @endif
        
        {{-- 설정 버튼: JSON 설정이 있으면 설정값, 없으면 기본으로 표시 --}}
        @if(($data['create']['enableSettingsDrawer'] ?? null) !== false)
            <button wire:click="openSettings" 
                    class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                설정
            </button>
        @endif
    </div>
</div>
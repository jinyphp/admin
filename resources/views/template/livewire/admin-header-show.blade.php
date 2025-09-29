{{--
    상세보기 페이지 헤더 컴포넌트
    
    @package jiny/admin
    @component admin-header-show
    @description 상세보기(show) 페이지의 상단 헤더 영역을 표시합니다.
                제목, 설명, 네비게이션 버튼(목록, 설정)을 포함하며,
                JSON 설정에 따라 버튼 표시 여부를 동적으로 제어합니다.
    
    @requires
    - $title: 페이지 제목 (필수)
    - $description: 페이지 설명 (선택)
    - $jsonData: JSON 설정 데이터 (필수)
    - $listRoute: 목록 페이지 라우트 (선택)
    
    @settings JSON 설정 (show 섹션)
    - enableListButton: 목록 버튼 표시 여부 (기본값: true)
    - enableSettingsDrawer: 설정 버튼 표시 여부 (기본값: true)
    
    @note
    - 목록 버튼은 $listRoute가 있으면 <a> 태그로, 없으면 Livewire 이벤트로 처리
    - 설정 버튼 클릭 시 ShowSettingsDrawer 컴포넌트를 열어 런타임 설정 변경 가능
    - Tailwind CSS 스타일 적용 (회색 목록 버튼, 흰색 설정 버튼)
    
    @version 1.0
--}}
<div class="flex justify-between items-center mb-6">
    {{-- 왼쪽: 제목 및 설명 영역 --}}
    <div>
        {{-- 메인 제목 --}}
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        
        {{-- 부가 설명 (있는 경우만 표시) --}}
        @if($description)
            <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
        @endif
    </div>
    
    {{-- 오른쪽: 액션 버튼 영역 --}}
    <div class="flex items-center space-x-2">
        {{-- 
            목록 버튼
            - JSON 설정의 show.enableListButton이 true일 때만 표시
            - 기본값은 true이므로 설정이 없어도 표시됨
            - $listRoute가 정의되어 있으면 직접 링크, 없으면 Livewire 이벤트 처리
        --}}
        @if($jsonData['show']['enableListButton'] ?? true)
            @if($listRoute)
                {{-- 직접 라우트 링크 방식 --}}
                <a href="{{ $listRoute }}" 
                   class="inline-flex items-center h-8 px-3 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-500 transition-colors">
                    {{-- 목록 아이콘 (햄버거 메뉴) --}}
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    목록
                </a>
            @else
                {{-- Livewire 이벤트 방식 (동적 라우팅) --}}
                <button wire:click="navigateToList" 
                        class="inline-flex items-center h-8 px-3 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-500 transition-colors">
                    {{-- 목록 아이콘 (햄버거 메뉴) --}}
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    목록
                </button>
            @endif
        @endif
        
        {{-- 
            설정 버튼
            - JSON 설정의 show.enableSettingsDrawer가 true일 때만 표시
            - 기본값은 true이므로 설정이 없어도 표시됨
            - 클릭 시 openSettings Livewire 메소드 호출
            - ShowSettingsDrawer 컴포넌트를 열어 상세보기 설정을 실시간으로 변경 가능
        --}}
        @if($jsonData['show']['enableSettingsDrawer'] ?? true)
            <button wire:click="openSettings" 
                    class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                {{-- 설정 아이콘 (톱니바퀴) --}}
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                설정
            </button>
        @endif
    </div>
</div>
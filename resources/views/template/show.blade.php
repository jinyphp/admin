{{-- 
    템플릿 상세 보기 페이지
    선택한 관리자 템플릿의 상세 정보를 표시합니다.
    읽기 전용 뷰로 템플릿의 모든 필드 정보를 확인할 수 있습니다.
--}}
@extends($jsonData['template']['layout'] ?? 'jiny-admin::layouts.admin')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-4">
    {{-- 페이지 헤더 섹션 --}}
    @livewire('jiny-admin::admin-header-with-settings', [
        'jsonData' => $jsonData,
        'jsonPath' => $jsonPath ?? null,
        'mode' => 'show'
    ])

    {{-- 삭제 확인 모달 컴포넌트 --}}
    @livewire('jiny-admin::admin-delete', [
        'jsonData' => $jsonData
    ])

    {{-- 템플릿 상세 정보 컴포넌트 --}}
    @livewire('jiny-admin::admin-show', [
        'jsonData' => $jsonData,
        'data' => $data,
        'itemId' => $itemId ?? $id ?? null,
        'controllerClass' => $controllerClass ?? null
    ])
    
    {{-- 설정 드로어 컴포넌트 --}}
    @if(isset($jsonData['show']['enableSettingsDrawer']) && $jsonData['show']['enableSettingsDrawer'])
        @livewire('jiny-admin::settings.show-settings-drawer', [
            'jsonPath' => $settingsPath
        ])
    @endif
</div>
@endsection
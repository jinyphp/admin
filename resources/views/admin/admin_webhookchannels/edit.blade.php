{{--
    웹훅 채널 수정 페이지
    기존 웹훅 채널을 수정하는 폼을 표시합니다.
--}}
@extends($jsonData['template']['layout'] ?? 'jiny-admin::layouts.admin')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-4">
    {{-- 페이지 헤더 섹션 --}}
    @livewire('jiny-admin::admin-header-with-settings', [
        'jsonData' => $jsonData,
        'jsonPath' => $jsonPath ?? null,
        'mode' => 'edit'
    ])

    {{-- 삭제 확인 모달 컴포넌트 --}}
    @livewire('jiny-admin::admin-delete', [
        'jsonData' => $jsonData
    ])

    {{-- 웹훅 채널 수정 폼 --}}
    <div class="mt-6">
        @livewire('jiny-admin::admin-edit', [
            'controllerClass' => $controllerClass,
            'jsonData' => $jsonData,
            'form' => $form,
            'id' => $id,
            'formPath' => 'jiny-admin::admin.admin_webhookchannels.forms.edit'
        ])
    </div>

    {{-- 설정 드로어 컴포넌트 --}}
    @if(isset($settingsPath))
    @livewire('jiny-admin::settings.edit-settings-drawer', [
        'jsonPath' => $settingsPath
    ])
    @endif
</div>

{{-- 이벤트 처리 JavaScript --}}
<script>
    window.addEventListener('redirect-with-replace', event => {
        window.location.replace(event.detail.url);
    });

    // 설정 저장 후 페이지 새로고침
    window.addEventListener('refresh-page', event => {
        window.location.reload();
    });
</script>
@endsection
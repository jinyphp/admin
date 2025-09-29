{{--
    웹훅 채널 생성 페이지
    새로운 웹훅 채널을 생성하는 폼을 표시합니다.
--}}
@extends($jsonData['template']['layout'] ?? 'jiny-admin::layouts.admin')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-4">

    {{-- 페이지 헤더 섹션 --}}
    @livewire('jiny-admin::admin-header-with-settings', [
        'jsonData' => $jsonData,
        'jsonPath' => $jsonPath ?? null,
        'mode' => 'create'
    ])

    {{-- 웹훅 채널 생성 폼 --}}
    <div class="mt-6">
        @livewire('jiny-admin::admin-create', [
            'jsonData' => $jsonData,
            'form' => $form,
            'formPath' => 'jiny-admin::admin.admin_webhookchannels.forms.create'
        ])
    </div>

    {{-- 설정 드로어 컴포넌트 --}}
    @if(isset($settingsPath))
    @livewire('jiny-admin::settings.create-settings-drawer', [
        'jsonPath' => $settingsPath
    ])
    @endif
</div>

{{-- 리다이렉트 처리 JavaScript --}}
<script>
    window.addEventListener('redirect-with-replace', event => {
        window.location.replace(event.detail.url);
    });

    // 계속 생성 모드에서 첫 번째 입력 필드에 포커스
    window.addEventListener('focus-first-field', event => {
        setTimeout(() => {
            const firstInput = document.querySelector('form input[type="text"]:not([disabled]), form textarea:not([disabled])');
            if (firstInput) {
                firstInput.focus();
                firstInput.select();
            }
        }, 100);
    });

    // 성공 메시지 하이라이트 효과
    window.addEventListener('highlight-success', event => {
        const successAlert = document.querySelector('.bg-green-100');
        if (successAlert) {
            successAlert.classList.add('ring-2', 'ring-green-400', 'ring-offset-2');
            setTimeout(() => {
                successAlert.classList.remove('ring-2', 'ring-green-400', 'ring-offset-2');
            }, 2000);
        }
    });
</script>
@endsection
{{--
    템플릿 생성 페이지
    새로운 관리자 템플릿을 생성하는 폼을 표시합니다.
    JSON 설정 파일을 기반으로 동적으로 폼 필드를 생성합니다.
--}}
@extends($jsonData['template']['layout'] ??'jiny-admin::layouts.admin')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-4">

    {{--
        페이지 헤더 섹션
        페이지 제목, 설명, 목록으로 돌아가기 버튼을 표시
    --}}
    {{-- jsonData와 jsonPath를 직접 전달하여 컴포넌트에서 처리 --}}
    @livewire('jiny-admin::admin-header-with-settings', [
        'jsonData' => $jsonData,
        'jsonPath' => $jsonPath ?? null,
        'mode' => 'create'
    ])

    {{--
        템플릿 생성 폼 컴포넌트
        JSON 설정에 정의된 필드들을 동적으로 렌더링
        폼 유효성 검사 및 데이터 저장 처리
    --}}
    @livewire('jiny-admin::admin-create', [
        'jsonData' => $jsonData,
        'form' => $form
    ])

    {{--
        설정 드로어 컴포넌트
        관리자가 JSON 설정 파일을 실시간으로 편집할 수 있는 UI 제공
        생성 페이지의 폼 구성을 동적으로 변경 가능
    --}}
    @livewire('jiny-admin::settings.create-settings-drawer', [
        'jsonPath' => $settingsPath
    ])
</div>

{{--
    리다이렉트 처리 JavaScript
    템플릿 생성 후 브라우저 히스토리를 대체하면서 목록 페이지로 리다이렉트
    뒤로가기 버튼 사용 시 중복 생성 방지
--}}
<script>
    window.addEventListener('redirect-with-replace', event => {
        window.location.replace(event.detail.url);
    });

    // 계속 생성 모드에서 첫 번째 입력 필드에 포커스
    // Focus on first input field in continue creating mode
    window.addEventListener('focus-first-field', event => {
        setTimeout(() => {
            const firstInput = document.querySelector('form input[type="text"]:not([disabled]), form textarea:not([disabled])');
            if (firstInput) {
                firstInput.focus();
                firstInput.select(); // 텍스트 선택하여 바로 수정 가능
            }
        }, 100);
    });

    // 성공 메시지 하이라이트 효과
    // Highlight success message effect
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

{{--
    템플릿 수정 페이지
    기존 관리자 템플릿을 수정하는 폼을 표시합니다.
    JSON 설정 파일을 기반으로 동적으로 폼 필드를 생성하고 기존 데이터를 로드합니다.
--}}
@extends($jsonData['template']['layout'] ??'jiny-admin::layouts.admin')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-4">
    {{--
        페이지 헤더 섹션
        페이지 제목, 설명, 목록으로 돌아가기 버튼을 표시
        수정 모드에서 삭제 버튼도 함께 표시
    --}}
    {{-- jsonData와 jsonPath를 직접 전달하여 컴포넌트에서 처리 --}}
    @livewire('jiny-admin::admin-header-with-settings', [
        'jsonData' => $jsonData,
        'jsonPath' => $jsonPath ?? null,
        'mode' => 'edit'
    ])

    {{--
        삭제 확인 모달 컴포넌트
        템플릿 삭제 시 확인 다이얼로그를 표시
        삭제 작업을 비동기로 처리
    --}}
    @livewire('jiny-admin::admin-delete', [
        'jsonData' => $jsonData
    ])

    {{--
        템플릿 수정 폼 컴포넌트
        JSON 설정에 정의된 필드들을 동적으로 렌더링
        기존 데이터를 로드하고 폼 유효성 검사 및 업데이트 처리
    --}}
    @livewire('jiny-admin::admin-edit', [
        'controllerClass' => $controllerClass,
        'jsonData' => $jsonData,
        'form' => $form,
        'id' => $id
    ])

    {{--
        설정 드로어 컴포넌트
        관리자가 JSON 설정 파일을 실시간으로 편집할 수 있는 UI 제공
        수정 페이지의 폼 구성을 동적으로 변경 가능
    --}}
    @livewire('jiny-admin::settings.edit-settings-drawer', [
        'jsonPath' => $settingsPath
    ])
</div>

{{--
    이벤트 처리 JavaScript
    1. redirect-with-replace: 템플릿 수정 후 브라우저 히스토리를 대체하면서 목록 페이지로 리다이렉트
    2. refresh-page: 설정 변경 후 페이지를 새로고침하여 변경사항 적용
--}}
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

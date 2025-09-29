
{{--
    템플릿 수정 페이지
    기존 관리자 템플릿을 수정하는 폼을 표시합니다.
    JSON 설정 파일을 기반으로 동적으로 폼 필드를 생성하고 기존 데이터를 로드합니다.
--}}
@extends($jsonData['template']['layout'] ??'jiny-admin::layouts.admin')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-4">




@endsection

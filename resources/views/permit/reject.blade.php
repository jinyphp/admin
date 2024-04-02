@extends('jinyauth::layouts.bootstrap')
@section('content')

<div class="text-center mt-4">
    <h1 class="h2">관리자만 접속이 가능합니다.</h1>
    <p class="lead">관리자 페이지에 접속할 수 없습니다.</p>
</div>

<div class="card">
    <div class="card-body">

        <div class="text-center">
            권환이 없는 사용자가 지속적으로 접속을 시도하는 경우, 운영자에게 통보됩니다.
        </div>

    </div>
</div>

@endsection

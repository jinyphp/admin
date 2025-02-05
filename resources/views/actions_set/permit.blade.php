<x-form-hor>
    <x-form-label>Role</x-form-label>
    <x-form-item>
        {!! xCheckbox()
            ->setWire('model.defer',"forms.role")
        !!}
        <div>사용자 Role권한을 적용합니다.</div>
    </x-form-item>
</x-form-hor>

{{-- role 테이블 선택--}}
@php
    $roles = DB::table("roles")->get();
@endphp
<table class="table">
    <thead>
        <tr>
            <th>역할</th>
            <th width='100'>권환</th>
            <th width='100'>생성</th>
            <th width='100'>읽기</th>
            <th width='100'>변경</th>
            <th width='100'>삭제</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($roles as $item)
        <tr >
            <td>{{$item->name}}</td>
            <td width='100'>
                {!! xCheckbox()
                    ->setWire('model.defer',"forms.roles.".$item->name.".permit")
                !!}
            </td>
            <td width='100'>
                {!! xCheckbox()
                    ->setWire('model.defer',"forms.roles.".$item->name.".create")
                !!}
            </td>
            <td width='100'>
                {!! xCheckbox()
                    ->setWire('model.defer',"forms.roles.".$item->name.".read")
                !!}
            </td>
            <td width='100'>
                {!! xCheckbox()
                    ->setWire('model.defer',"forms.roles.".$item->name.".update")
                !!}
            </td>
            <td width='100'>
                {!! xCheckbox()
                    ->setWire('model.defer',"forms.roles.".$item->name.".delete")
                !!}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

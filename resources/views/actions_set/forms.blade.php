<x-navtab class="mb-3 nav-bordered">

    <x-navtab-item class="show active">
        <x-navtab-link class="rounded-0 active">
            <span class="d-none d-md-block">정보</span>
        </x-navtab-link>

        <fieldset>
            <legend class="px-2 text-xs">Argument</legend>
            <x-form-hor>
                <x-form-label>타이틀</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.title")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>서브타이틀</x-form-label>
                <x-form-item>
                    {!! xTextarea()
                        ->setWire('model.defer',"forms.subtitle")
                    !!}
                </x-form-item>
            </x-form-hor>
        </fieldset>

        <fieldset>
            <legend class="px-2 text-xs">Blade Resource</legend>

            <x-form-hor>
                <x-form-label>View_title </x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.view_title_check")
                    !!}

                    {!! xInputText()
                        ->setWire('model.defer',"forms.view_title")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

        </fieldset>


    </x-navtab-item>


    {{-- <x-navtab-item class="show active"><!-- formTab -->
        <x-navtab-link class="rounded-0 active">
            <span class="d-none d-md-block">화면</span>
        </x-navtab-link>

        @includeIf('jiny-admin::actions_set.form_views')

    </x-navtab-item> --}}

    <x-navtab-item ><!-- formTab -->
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">테이블</span>
        </x-navtab-link>

        @includeIf('jiny-admin::actions_set.form_table')

    </x-navtab-item>

    <x-navtab-item ><!-- formTab -->
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">입력폼</span>
        </x-navtab-link>

        @includeIf('jiny-admin::actions_set.form_forms')

    </x-navtab-item>

    <x-navtab-item ><!-- formTab -->
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">입력값</span>
        </x-navtab-link>

        @includeIf("jiny-admin::actions_set.values")
    </x-navtab-item>




    <x-navtab-item ><!-- formTab -->
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">파일관리</span>
        </x-navtab-link>

        <x-form-hor>
            <x-form-label>경로</x-form-label>
            <x-form-item>
                {!! xInputText()
                    ->setWire('model.defer',"forms.path")
                !!}
            </x-form-item>
        </x-form-hor>
    </x-navtab-item>




    <x-navtab-item >
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">메뉴</span>
        </x-navtab-link>

        @includeIf("jiny-admin::actions_set.from_action_menu")

    </x-navtab-item>

    <x-navtab-item ><!-- formTab -->
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">권환</span>
        </x-navtab-link>

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
                    <th >Name</th>
                    <th width='100'>Permit</th>
                    <th width='100'>Create</th>
                    <th width='100'>Read</th>
                    <th width='100'>Update</th>
                    <th width='100'>Delete</th>
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

    </x-navtab-item>

    <x-navtab-item ><!-- formTab -->
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">메모</span>
        </x-navtab-link>

        <x-form-hor>
            <x-form-label>메모</x-form-label>
            <x-form-item>
                {!! xTextarea()
                    ->setWire('model.defer',"forms.description")
                !!}
            </x-form-item>
        </x-form-hor>
    </x-navtab-item>

</x-navtab>

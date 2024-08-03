<div>
    {{-- loading 화면 처리 --}}
    <x-loading-indicator/>

    <style>

    </style>

    <button class="btn btn-primary" wire:click="popupRuleOpen()">
        <div class="d-flex gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
            </svg>
            <div>
                SetActions
            </div>
        </div>
    </button>


    <!-- 팝업 Rule 수정창 -->
    @if ($popupRule)
    <x-dialog-modal wire:model="popupRule" maxWidth="2xl">
        <x-slot name="content">
            <x-navtab class="mb-3 nav-bordered">

                <x-navtab-item><!-- Action 정보 -->
                    <x-navtab-link class="rounded-0">
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


                <x-navtab-item class="show active"><!-- formTab -->
                    <x-navtab-link class="rounded-0 active">
                        <span class="d-none d-md-block">화면</span>
                    </x-navtab-link>

                    @includeIf('jiny-admin::actions_set.form_views')

                </x-navtab-item>

                <x-navtab-item ><!-- formTab -->
                    <x-navtab-link class="rounded-0">
                        <span class="d-none d-md-block">입력폼</span>
                    </x-navtab-link>

                    @includeIf('jiny-admin::actions_set.form_forms')

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


                <x-navtab-item ><!-- formTab -->
                    <x-navtab-link class="rounded-0">
                        <span class="d-none d-md-block">데이터베이스</span>
                    </x-navtab-link>

                    <x-form-hor>
                        <x-form-label>테이블</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.table")
                            !!}
                        </x-form-item>
                    </x-form-hor>

                    <x-form-hor>
                        <x-form-label>페이징</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.paging")
                            !!}
                        </x-form-item>
                    </x-form-hor>

                    <x-form-hor>
                        <x-form-label>조건</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.where")
                            !!}
                        </x-form-item>
                    </x-form-hor>

                    <hr>

                    <x-form-hor>
                        <x-form-label>파일명</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.filename")
                            !!}
                            저장할 config 파일명을 지정합니다.
                        </x-form-item>
                    </x-form-hor>

                </x-navtab-item>

                <x-navtab-item >
                    <x-navtab-link class="rounded-0">
                        <span class="d-none d-md-block">메뉴</span>
                    </x-navtab-link>

                    <x-form-hor>
                        <x-form-label>메뉴</x-form-label>
                        <x-form-item>
                            @if(function_exists('xMenu'))
                            {!! xSelect()
                                ->table('menus','code')
                                ->setWire('model.defer',"forms.menu")
                                ->setWidth("medium")
                            !!}
                            @endif
                        </x-form-item>
                    </x-form-hor>

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
        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-between">
            @if (isset($actions['id']))
                <div>
                </div>
                <div>
                    <x-button secondary wire:click="popupRuleClose">취소</x-button>
                    <x-button primary wire:click="update">수정</x-button>
                </div>
            @else
                <div></div>
                <div class="text-right">
                    <x-button secondary wire:click="popupRuleClose">취소</x-button>
                    <x-button primary wire:click="save">저장</x-button>
                </div>
            @endif
            </div>
        </x-slot>
    </x-dialog-modal>
    @endif


    @if ($popupResourceEdit)
    <x-dialog-modal wire:model="popupResourceEdit" maxWidth="2xl">
        <x-slot name="content">
            {!! xTextarea()
                ->setWire('model.defer',"content")
            !!}
        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-between">
                <div></div>
                <div class="text-right">
                    <x-button secondary wire:click="returnRule">취소</x-button>
                    <x-button primary wire:click="update">수정</x-button>
                </div>
            </div>
        </x-slot>
    </x-dialog-modal>
    @endif

</div>

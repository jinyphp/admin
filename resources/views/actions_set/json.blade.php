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

    </x-navtab-item>


    <x-navtab-item class="show active"><!-- formTab -->
        <x-navtab-link class="rounded-0 active">
            <span class="d-none d-md-block">화면</span>
        </x-navtab-link>

        @includeIf('jiny-admin::actions_set.form_views')

    </x-navtab-item>

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




</x-navtab>

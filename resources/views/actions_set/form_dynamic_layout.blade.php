<x-navtab class="mb-3 nav-bordered">
    <x-navtab-item class="show active">
        <x-navtab-link class="rounded-0 active">
            <span class="d-none d-md-block">다이나믹</span>
        </x-navtab-link>

        @foreach($layouts as $key => $item)
        <x-form-hor>
            <x-form-label>{{$key}}</x-form-label>
            <x-form-item>
                <select class="form-control mb-3"
                    wire:model="forms.layouts.{{$key}}">
                    <option value="">적용할 디자인을 선택해주세요.</option>
                    @foreach($item as $val)
                    <option value="{{$val->name}}">{{$val->name}}</option>
                    @endforeach
                </select>
            </x-form-item>
        </x-form-hor>
        @endforeach

    </x-navtab-item>

    <x-navtab-item class="">
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">레이아웃</span>
        </x-navtab-link>

        <div class="mb-3">
            <label class="form-label">컨트롤러 Layout</label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.layout")
                ->setWidth("standard")
            !!}
        </div>


        <div class="mb-3">
            <label class="form-label">
                컨트롤러 Main
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.main")
                ->setWidth("standard")
            !!}
        </div>

    </x-navtab-item>

    <x-navtab-item >
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">메뉴</span>
        </x-navtab-link>

        @includeIf("jiny-admin::actions_set.from_action_menu")

    </x-navtab-item>

    <x-navtab-item >
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">테마</span>
        </x-navtab-link>

        <x-form-hor>
            <x-form-label>적용테마</x-form-label>
            <x-form-item>
                <select class="form-control mb-3" wire:model="forms.theme">
                    <option value="">적용할 테마를 선택해 주세요</option>
                    @foreach(theme()->list as $item)
                    <option value="{{$item['code']}}">{{$item['code']}}</option>
                    @endforeach
                </select>

                {{-- {!! xInputText()
                    ->setWire('model.defer',"forms.theme")
                    ->setWidth("standard")
                !!} --}}
            </x-form-item>
        </x-form-hor>

    </x-navtab-item>
</x-navtab>

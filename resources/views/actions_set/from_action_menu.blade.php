<x-form-hor>
    <x-form-label>메뉴 Header</x-form-label>
    <x-form-item>
        {!! xSelect()
            ->table('site_menus','code')
            ->setWire('model.defer',"forms.menu.header")
            ->setWidth("medium")
        !!}
    </x-form-item>
</x-form-hor>

<x-form-hor>
    <x-form-label>메뉴 Side</x-form-label>
    <x-form-item>
        {!! xSelect()
            ->table('site_menus','code')
            ->setWire('model.defer',"forms.menu.side")
            ->setWidth("medium")
        !!}
    </x-form-item>
</x-form-hor>

<x-form-hor>
    <x-form-label>메뉴 Footer</x-form-label>
    <x-form-item>
        {!! xSelect()
            ->table('site_menus','code')
            ->setWire('model.defer',"forms.menu.footer")
            ->setWidth("medium")
        !!}
    </x-form-item>
</x-form-hor>

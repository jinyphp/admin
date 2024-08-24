<div>
    <x-form-hor>
        <x-form-label>Key</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setAttribute('name',"Key")
                ->setWire('model.defer',"forms.key")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

</div>

<x-form-hor>
    <x-form-label>Form</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.view.form")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor>


<x-form-hor>
    <x-form-label>view_edit</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.view.edit")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor>

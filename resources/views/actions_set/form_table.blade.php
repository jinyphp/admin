<x-flex class="gap-2">
    <div class="flex-fill">
    </div>

    <div class="flex-fill">
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
    </div>

    <div class="flex-fill">

        <div class="mb-3">
            <label class="form-label">
                Table
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.table")
                ->setWidth("standard")
            !!}
        </div>

        <div class="mb-3">
            <label class="form-label">
                Filter
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.filter")
                ->setWidth("standard")
            !!}
        </div>

        <div class="mb-3">
            <label class="form-label">
                List
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.list")
                ->setWidth("standard")
            !!}
        </div>

    </div>

</x-flex>



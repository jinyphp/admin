<x-flex class="gap-2">
    {{-- <div class="flex-fill">
        <h2>화면 단위 설정</h2>
        <p>컨트롤러에 적용된 화면을 변경할 수 있습니다. 입력값은 라라벨의 blade 경로 입니다.</p>
    </div> --}}
    <div class="flex-fill">
        <div class="mb-3">
            <label class="form-label">Layout</label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.layout")
                ->setWidth("standard")
            !!}
        </div>


        <div class="mb-3">
            <label class="form-label">
                Main
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.main")
                ->setWidth("standard")
            !!}
        </div>


    </div>

    <div class="flex-fill">
        <label class="form-label">
            Blade Partials
        </label>

        @if(isset($forms['blade']) && count($forms['blade'])>0)
            @foreach($forms['blade'] as $b => $vale)
            <div class="mb-3">
                {!! xInputText()
                    ->setWire('model.defer',"forms.blade.".$b)
                !!}
                <x-flex-between>
                    <div>
                        <x-click wire:click="removeBlade('{{$b}}')">
                            remove
                        </x-click>
                    </div>
                    <div class="d-flex gap-2">
                        <x-click wire:click="bladeUp('{{$b}}')">
                            up
                        </x-click>

                        <x-click wire:click="bladeDown('{{$b}}')">
                            Down
                        </x-click>
                    </div>
                </x-flex-between>



            </div>
            @endforeach
        @else
        <div class="mb-3">
            {!! xInputText()
                ->setWire('model.defer',"forms.blade.0")
                ->setWidth("standard")
            !!}
        </div>
        @endif

        <div class="mb-3">
            <button class="btn btn-info" wire:click="addBlade()">+</button>
        </div>
    </div>
</x-flex-between>




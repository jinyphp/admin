@if($addKeyStatus)
    <label class="form-label">키 이름</label>
    <div class="row row-cols-md-auto align-items-center">
        <div class="col-12">
            {!! xInputText()
                ->setWire('model.defer',"key_name")
                ->setWidth("standard")
            !!}
        </div>

        <div class="col-12">
            <button class="btn btn-primary" wire:click="addNewSubmit">확인</button>
        </div>

        <div class="col-12">
            <button class="btn btn-secondary" wire:click="addNewCancel">취소</button>
        </div>
    </div>
    <p>새로운 데이터 항목을 추가합니다.</p>

@else
    @foreach($forms as $key => $value)
        @if($key != "updated_at")
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2 text-sm-end">{{$key}}</label>
                <div class="col-sm-8">
                    {!! xInputText()
                        ->setWire('model.defer',"forms.site.".$key)
                    !!}

                </div>
                <div class="col-sm-2">
                    <x-click wire:click="itemRemove('{{$key}}')">
                        delete
                    </x-click>
                </div>
            </div>
        @endif
    @endforeach
    <div class="mb-3 row">
        <label class="col-form-label col-sm-2 text-sm-end"></label>
        <div class="col-sm-10">
            <button class="btn btn-primary" wire:click="addNewCreate()">+</button>
        </div>

    </div>
@endif

@foreach($rows as $key => $item)
<li class="p-1">
    @if(is_array($item))
    <div>
        @if($editable)
        <x-click class="col-form-label"
            wire:click="edit('{{$ref}}-{{$key}}')">
            {{$key}}
        </x-click>
        @else
            {{$key}}
        @endif
    </div>
    <ul style="border-left:1px solid #ccc;">
        @includeIf("jiny-admin::actions.node",[
            'ref' => $ref."-".$key,
            'rows' => $item
        ])

        @if($editable)
        <li>
            <x-click wire:click="create('{{$ref}}-{{$key}}')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-node-plus" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8M6.025 7.5a5 5 0 1 1 0 1H4A1.5 1.5 0 0 1 2.5 10h-1A1.5 1.5 0 0 1 0 8.5v-1A1.5 1.5 0 0 1 1.5 6h1A1.5 1.5 0 0 1 4 7.5zM11 5a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2A.5.5 0 0 1 11 5M1.5 7a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                </svg>
            </x-click>
        </li>
        @endif
    </ul>

    @else
        {{-- value값 일 경우 --}}
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                @if($editable)
                <x-click class="col-form-label"
                    wire:click="edit('{{$ref}}-{{$key}}')">
                    {{$key}}
                </x-click>
                @else
                {{$key}}
                @endif
            </div>
            <div class="col-auto">
                <input type="text" id="key" class="form-control"
                    wire:model.defer="rows{{str_replace('-','.',$ref.'-'.$key)}}">
            </div>
            <div class="col-auto">
                @if($editable)
                <x-click wire:click="create('{{$ref}}-{{$key}}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-node-plus" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8M6.025 7.5a5 5 0 1 1 0 1H4A1.5 1.5 0 0 1 2.5 10h-1A1.5 1.5 0 0 1 0 8.5v-1A1.5 1.5 0 0 1 1.5 6h1A1.5 1.5 0 0 1 4 7.5zM11 5a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2A.5.5 0 0 1 11 5M1.5 7a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                    </svg>
                </x-click>
                @endif
            </div>
        </div>

        {{-- @if($popupSubForm && $popupSubForm == trim($ref."-".$key,'-'))
        <div class="ml-8 mt-2">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="key" class="col-form-label">key</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="key" class="form-control"
                    wire:model.defer="forms.key">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" wire:click="store()">Add</button>
                    <button class="btn btn-secondary" wire:click="cancel()">close</button>
                </div>
            </div>
        </div>
        @endif --}}

    @endif






    {{-- up --}}
    {{-- <x-click wire:click="itemUp('{{$ref}}-{{$i}}')"
        style="display:inline-block;margin-left:20px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-circle" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z"/>
        </svg>
        <span>Up</span>
    </x-click> --}}

    {{-- down --}}
    {{-- <x-click wire:click="itemDown('{{$ref}}-{{$i}}')"
        style="display:inline-block;margin-left:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-circle" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293z"/>
        </svg>
        <span>Down</span>
    </x-click> --}}

    {{-- <ul style="list-style-type: none; border-left:1px solid;">
        @if(isset($item['items']))
            @includeIf("jiny-site::admin.menu_item.node",[
                'ref' => $ref."-".$i,
                'rows' => $item['items']
            ])
        @endif

        <li class="p-1">
            <x-click wire:click="create('{{$ref}}-{{$i}}')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-node-plus" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8M6.025 7.5a5 5 0 1 1 0 1H4A1.5 1.5 0 0 1 2.5 10h-1A1.5 1.5 0 0 1 0 8.5v-1A1.5 1.5 0 0 1 1.5 6h1A1.5 1.5 0 0 1 4 7.5zM11 5a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2A.5.5 0 0 1 11 5M1.5 7a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                </svg>
            </x-click>
        </li>

    </ul> --}}
</li>
@endforeach

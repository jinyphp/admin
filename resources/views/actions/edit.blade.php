<div class="card">
    <div class="card-header">
        Action Name :
        {{$filename}}
    </div>
    <div class="card-body">
        @livewire('action-edit',[
            'filename' => $filename
        ])
    </div>
</div>

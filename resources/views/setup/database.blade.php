<div>
    <div>database</div>

    @foreach ($message as $item)
    <div>{{$item}}</div>
    @endforeach


    @if($pdo)
    <div class="alert alert-success" role="alert">
        데이터베이스 설정 완료
    </div>
    @else
    <div class="card">
        <div class="card-header">
            데이터베이스 설정이 필요합니다.
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="user-name" class="form-label">아이디</label>
                <input type="email" class="form-control" id="user-name"
                wire:model.defer="forms.user">
            </div>
            <div class="mb-3">
                <label for="user-password" class="form-label">Password</label>
                <input type="password" class="form-control" id="user-password"
                wire:model.defer="forms.password">
            </div>
            <div class="mb-3">
                <label for="user-email" class="form-label">스키마</label>
                <input type="email" class="form-control" id="user-email"
                wire:model.defer="forms.schema">
            </div>
            <button type="button" class="btn btn-primary" wire:click="submit">생성</button>
        </div>
    </div>
    @endif
</div>

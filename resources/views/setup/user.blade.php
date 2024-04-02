<div>
    @if($user_count>0)
    <div class="alert alert-success" role="alert">
        관리자 회원 등록완료
    </div>
    @else
    <div class="card">
        <div class="card-header">
            등록된 회원이 없습니다.
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="user-name" class="form-label">Name</label>
                <input type="email" class="form-control" id="user-name"
                wire:model.defer="forms.name">
            </div>
            <div class="mb-3">
                <label for="user-email" class="form-label">Email</label>
                <input type="email" class="form-control" id="user-email"
                wire:model.defer="forms.email">
            </div>
            <div class="mb-3">
                <label for="user-password" class="form-label">Password</label>
                <input type="password" class="form-control" id="user-password"
                wire:model.defer="forms.password">
            </div>
            <button type="button" class="btn btn-primary" wire:click="submit">등록</button>
        </div>
    </div>
    @endif
</div>

{{-- AdminUsers 생성 폼 --}}
<div class="space-y-4">
    <div>
        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
            이름 <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               wire:model="form.name" 
               id="name" 
               name="name"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="사용자 이름"
               required>
        @error('form.name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-xs font-medium text-gray-700 mb-1">
            이메일 <span class="text-red-500">*</span>
        </label>
        <input type="email" 
               wire:model.blur="form.email" 
               id="email" 
               name="email"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.email') border-red-500 @enderror"
               placeholder="user@example.com"
               required>
        @error('form.email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-xs font-medium text-gray-700 mb-1">
            비밀번호 <span class="text-red-500">*</span>
        </label>
        <input type="password" 
               wire:model.blur="form.password" 
               id="password" 
               name="password"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.password') border-red-500 @enderror"
               placeholder="최소 8자 이상"
               required>
        @error('form.password')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">
            비밀번호 확인 <span class="text-red-500">*</span>
        </label>
        <input type="password" 
               wire:model.blur="form.password_confirmation" 
               id="password_confirmation" 
               name="password_confirmation"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.password_confirmation') border-red-500 @enderror"
               placeholder="비밀번호를 다시 입력하세요"
               required>
        @error('form.password_confirmation')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="utype" class="block text-xs font-medium text-gray-700 mb-1">
            사용자 유형
        </label>
        <select wire:model="form.utype" 
                id="utype" 
                name="utype"
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">선택하세요</option>
            @if(isset($userTypes))
                @foreach($userTypes as $type)
                    <option value="{{ $type->code }}">{{ $type->name }} (Level: {{ $type->level }})</option>
                @endforeach
            @endif
        </select>
        @error('form.utype')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center">
        <input type="checkbox" 
               wire:model="form.isAdmin" 
               id="isAdmin" 
               name="isAdmin"
               class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="isAdmin" class="ml-2 block text-xs text-gray-900">
            관리자 권한 부여
        </label>
    </div>
</div>
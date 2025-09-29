{{-- AdminUsers 수정 폼 --}}
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
            새 비밀번호 <span class="text-gray-400">(변경시에만 입력)</span>
        </label>
        <input type="password" 
               wire:model.blur="form.password" 
               id="password" 
               name="password"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.password') border-red-500 @enderror"
               placeholder="변경하려면 새 비밀번호 입력">
        @error('form.password')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-[10px] leading-relaxed text-gray-500">
            비밀번호는 
            @if(config('setting.password.min_length') ?? 8)
                최소 {{ config('setting.password.min_length') ?? 8 }}자 이상이어야 합니다.
            @endif<br>
            @if(config('setting.password.require_uppercase') ?? false)
                최소 1개의 대문자 포함
            @endif
            @if(config('setting.password.require_lowercase') ?? false)
                , 소문자 포함
            @endif
            @if(config('setting.password.require_numbers') ?? false)
                , 숫자 포함
            @endif
            @if(config('setting.password.require_special_chars') ?? false)
                , 특수문자를 포함해야 합니다.
            @endif<br>
            연속된 문자나 숫자를 사용할 수 없습니다.
        </p>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">
            비밀번호 확인 <span class="text-gray-400">(변경시에만 입력)</span>
        </label>
        <input type="password" 
               wire:model.blur="form.password_confirmation" 
               id="password_confirmation" 
               name="password_confirmation"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.password_confirmation') border-red-500 @enderror"
               placeholder="비밀번호를 다시 입력하세요">
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

    {{-- 2FA 설정 섹션 --}}
    <div class="pt-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-xs font-medium text-gray-700">2차 인증 (2FA)</h4>
                @if(isset($form['id']))
                    @php
                        $user = \Jiny\Admin\Models\User::find($form['id']);
                    @endphp
                    @if($user && $user->two_factor_enabled)
                        <p class="text-[10px] text-green-600 mt-0.5">
                            <svg class="inline-block w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            2FA가 활성화되어 있습니다
                        </p>
                    @else
                        <p class="text-[10px] text-gray-500 mt-0.5">
                            2FA가 비활성화되어 있습니다
                        </p>
                    @endif
                @endif
            </div>
            @if(isset($form['id']))
                <a href="{{ route('admin.user.2fa.edit', $form['id']) }}" 
                   class="inline-flex items-center h-7 px-2.5 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    2FA 관리
                </a>
            @endif
        </div>
    </div>
</div>
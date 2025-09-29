{{-- SMS Provider 수정 폼 --}}
<div class="space-y-6">
    {{-- 헤더 정보 --}}
    <div class="bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500">제공업체 ID</label>
                <p class="mt-1 text-xs font-mono text-gray-900">#{{ $form['id'] ?? '' }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">생성일</label>
                <p class="mt-1 text-xs text-gray-900">
                    {{ \Carbon\Carbon::parse($form['created_at'] ?? now())->format('Y-m-d H:i') }}
                </p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">발송 건수</label>
                <p class="mt-1 text-xs text-gray-900">{{ number_format($form['sent_count'] ?? 0) }}건</p>
            </div>
        </div>
    </div>

    {{-- 기본 정보 --}}
    <div>
        <h3 class="text-sm font-medium text-gray-900 mb-4">기본 정보</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="driver_type" class="block text-xs font-medium text-gray-700 mb-1">드라이버 타입 *</label>
                <select wire:model="form.driver_type"
                        class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                        id="driver_type" required>
                    <option value="vonage">Vonage (Nexmo)</option>
                    <option value="twilio">Twilio</option>
                </select>
            </div>
            <div>
                <label for="provider_name" class="block text-xs font-medium text-gray-700 mb-1">제공업체명 *</label>
                <input type="text" wire:model="form.provider_name" 
                       class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                       id="provider_name" required>
            </div>
        </div>
    </div>

    {{-- API 인증 정보 --}}
    <div class="border-t pt-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">API 인증 정보</h3>
        <div class="space-y-4">
            {{-- Vonage 설정 --}}
            @if(($form['driver_type'] ?? 'vonage') == 'vonage')
                <div>
                    <label for="api_key" class="block text-xs font-medium text-gray-700 mb-1">API Key *</label>
                    <input type="text" wire:model="form.api_key"
                           class="h-8 px-2.5 text-xs font-mono border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                           id="api_key" placeholder="Vonage API Key">
                    @if($form['api_key'] ?? false)
                        <p class="mt-1 text-xs text-gray-500">현재: ****{{ substr($form['api_key'], -4) }}</p>
                    @endif
                </div>
                <div>
                    <label for="api_secret" class="block text-xs font-medium text-gray-700 mb-1">API Secret *</label>
                    <input type="password" wire:model="form.api_secret"
                           class="h-8 px-2.5 text-xs font-mono border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                           id="api_secret" placeholder="Vonage API Secret">
                </div>
            @endif
            
            {{-- Twilio 설정 --}}
            @if(($form['driver_type'] ?? '') == 'twilio')
                <div>
                    <label for="account_sid" class="block text-xs font-medium text-gray-700 mb-1">Account SID *</label>
                    <input type="text" wire:model="form.account_sid"
                           class="h-8 px-2.5 text-xs font-mono border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                           id="account_sid" placeholder="Twilio Account SID">
                    @if($form['account_sid'] ?? false)
                        <p class="mt-1 text-xs text-gray-500">현재: ****{{ substr($form['account_sid'], -4) }}</p>
                    @endif
                </div>
                <div>
                    <label for="auth_token" class="block text-xs font-medium text-gray-700 mb-1">Auth Token *</label>
                    <input type="password" wire:model="form.auth_token"
                           class="h-8 px-2.5 text-xs font-mono border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                           id="auth_token" placeholder="Twilio Auth Token">
                </div>
            @endif
        </div>
    </div>

    {{-- 발신 정보 --}}
    <div class="border-t pt-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">발신 정보</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="from_number" class="block text-xs font-medium text-gray-700 mb-1">발신번호</label>
                <input type="text" wire:model="form.from_number"
                       class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                       id="from_number">
            </div>
            <div>
                <label for="from_name" class="block text-xs font-medium text-gray-700 mb-1">발신자명</label>
                <input type="text" wire:model="form.from_name"
                       class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                       id="from_name">
            </div>
        </div>
    </div>

    {{-- 설정 --}}
    <div class="border-t pt-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">설정</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">우선순위</label>
                <input type="number" wire:model="form.priority"
                       class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                       id="priority" value="{{ $form['priority'] ?? 0 }}">
            </div>
            <div class="flex items-center">
                <input type="checkbox" wire:model="form.is_active"
                       class="h-4 w-4 text-blue-600 border-gray-200 rounded focus:ring-1 focus:ring-blue-500" 
                       id="is_active" @if($form['is_active'] ?? false) checked @endif>
                <label class="ml-2 text-xs text-gray-700" for="is_active">활성화</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" wire:model="form.is_default"
                       class="h-4 w-4 text-blue-600 border-gray-200 rounded focus:ring-1 focus:ring-blue-500" 
                       id="is_default" @if($form['is_default'] ?? false) checked @endif>
                <label class="ml-2 text-xs text-gray-700" for="is_default">기본 제공업체</label>
            </div>
        </div>
    </div>

    {{-- 설명 --}}
    <div class="border-t pt-6">
        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">설명</label>
        <textarea wire:model="form.description"
                  class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                  id="description" rows="3">{{ $form['description'] ?? '' }}</textarea>
    </div>

    {{-- 잔액 정보 --}}
    <div class="border-t pt-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="balance" class="block text-xs font-medium text-gray-700 mb-1">잔액 (USD)</label>
                <input type="number" wire:model="form.balance" step="0.01"
                       class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500" 
                       id="balance" value="{{ $form['balance'] ?? 0 }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">마지막 사용</label>
                <p class="h-8 px-2.5 text-xs border border-gray-200 rounded-md flex items-center bg-gray-50">
                    @if($form['last_used_at'] ?? false)
                        {{ \Carbon\Carbon::parse($form['last_used_at'])->diffForHumans() }}
                    @else
                        사용 기록 없음
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
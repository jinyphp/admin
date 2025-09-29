<div>
    <div class="mb-4">
        <label for="driver_type" class="block text-xs font-medium text-gray-700 mb-1">드라이버 타입 *</label>
        <select wire:model="form.driver_type"
                class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                id="driver_type" 
                name="driver_type" 
                required>
            <option value="">선택하세요</option>
            <option value="vonage">Vonage (Nexmo)</option>
            <option value="twilio">Twilio</option>
        </select>
    </div>
    
    <div class="mb-4">
        <label for="provider_name" class="block text-xs font-medium text-gray-700 mb-1">제공업체명 *</label>
        <input type="text" 
               wire:model="form.provider_name"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
               id="provider_name" 
               name="provider_name" 
               placeholder="제공업체 이름을 입력하세요"
               required>
    </div>

    {{-- Vonage 설정 --}}
    @if(($form['driver_type'] ?? '') == 'vonage')
        <div class="mb-4">
            <label for="api_key" class="block text-xs font-medium text-gray-700 mb-1">API Key *</label>
            <input type="text" 
                   wire:model="form.api_key"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="api_key" 
                   name="api_key" 
                   placeholder="Vonage API Key를 입력하세요"
                   required>
        </div>

        <div class="mb-4">
            <label for="api_secret" class="block text-xs font-medium text-gray-700 mb-1">API Secret *</label>
            <input type="password" 
                   wire:model="form.api_secret"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="api_secret" 
                   name="api_secret" 
                   placeholder="Vonage API Secret을 입력하세요"
                   required>
        </div>
    @endif
    
    {{-- Twilio 설정 --}}
    @if(($form['driver_type'] ?? '') == 'twilio')
        <div class="mb-4">
            <label for="account_sid" class="block text-xs font-medium text-gray-700 mb-1">Account SID *</label>
            <input type="text" 
                   wire:model="form.account_sid"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="account_sid" 
                   name="account_sid" 
                   placeholder="Twilio Account SID를 입력하세요"
                   required>
        </div>

        <div class="mb-4">
            <label for="auth_token" class="block text-xs font-medium text-gray-700 mb-1">Auth Token *</label>
            <input type="password" 
                   wire:model="form.auth_token"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="auth_token" 
                   name="auth_token" 
                   placeholder="Twilio Auth Token을 입력하세요"
                   required>
        </div>
    @endif

    <div class="mb-4">
        <label for="from_number" class="block text-xs font-medium text-gray-700 mb-1">발신번호</label>
        <input type="text" 
               wire:model="form.from_number"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
               id="from_number" 
               name="from_number" 
               placeholder="예: 821012345678">
        <p class="mt-1 text-xs text-gray-500">국가코드를 포함한 번호를 입력하세요</p>
    </div>

    <div class="mb-4">
        <label for="from_name" class="block text-xs font-medium text-gray-700 mb-1">발신자명</label>
        <input type="text" 
               wire:model="form.from_name"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
               id="from_name" 
               name="from_name" 
               placeholder="예: BRAND_NAME">
        <p class="mt-1 text-xs text-gray-500">번호 대신 표시될 발신자명 (일부 국가에서만 지원)</p>
    </div>
    
    <div class="mb-4">
        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">설명</label>
        <textarea wire:model="form.description"
                  class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                  id="description" 
                  name="description" 
                  rows="3"
                  placeholder="제공업체에 대한 설명을 입력하세요"></textarea>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
            <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">우선순위</label>
            <input type="number" 
                   wire:model="form.priority"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="priority" 
                   name="priority" 
                   value="0">
            <p class="mt-1 text-xs text-gray-500">높은 숫자가 우선</p>
        </div>

        <div class="flex items-center">
            <input type="checkbox" 
                   wire:model="form.is_active"
                   class="h-4 w-4 text-blue-600 border-gray-200 rounded focus:ring-1 focus:ring-blue-500" 
                   id="is_active" 
                   name="is_active">
            <label class="ml-2 text-xs text-gray-700" for="is_active">
                활성화
            </label>
        </div>

        <div class="flex items-center">
            <input type="checkbox" 
                   wire:model="form.is_default"
                   class="h-4 w-4 text-blue-600 border-gray-200 rounded focus:ring-1 focus:ring-blue-500" 
                   id="is_default" 
                   name="is_default">
            <label class="ml-2 text-xs text-gray-700" for="is_default">
                기본 제공업체
            </label>
        </div>
    </div>

    <div class="bg-blue-50 p-3 rounded-md">
        <h6 class="text-xs font-semibold text-blue-900 mb-2">설정 안내</h6>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>• <strong>Vonage:</strong> <a href="https://dashboard.nexmo.com" target="_blank" class="underline">대시보드</a>에서 API 키와 시크릿을 확인하세요.</li>
            <li>• <strong>발신번호:</strong> 한국의 경우 82로 시작하는 국가코드를 포함해야 합니다.</li>
            <li>• <strong>기본 제공업체:</strong> 하나의 제공업체만 기본으로 설정할 수 있습니다.</li>
        </ul>
    </div>
</div>
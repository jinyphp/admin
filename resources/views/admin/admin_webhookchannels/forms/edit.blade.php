{{-- 웹훅 채널 수정 폼 필드 --}}
<div>
    {{-- 채널 이름 --}}
    <div class="mb-4">
        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
            채널 이름 *
        </label>
        <input type="text" 
               wire:model="form.name" 
               id="name" 
               name="name"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="예: Slack 알림 채널"
               required>
        @error('form.name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 채널 타입 --}}
    <div class="mb-4">
        <label for="type" class="block text-xs font-medium text-gray-700 mb-1">
            채널 타입 *
        </label>
        <select wire:model="form.type" 
                id="type" 
                name="type"
                class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                required>
            <option value="">선택하세요</option>
            <option value="slack">Slack</option>
            <option value="discord">Discord</option>
            <option value="teams">Microsoft Teams</option>
            <option value="custom">Custom Webhook</option>
        </select>
        @error('form.type')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 웹훅 URL --}}
    <div class="mb-4">
        <label for="webhook_url" class="block text-xs font-medium text-gray-700 mb-1">
            웹훅 URL *
        </label>
        <input type="url" 
               wire:model="form.webhook_url" 
               id="webhook_url" 
               name="webhook_url"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="https://hooks.slack.com/services/..."
               required>
        @error('form.webhook_url')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 설명 --}}
    <div class="mb-4">
        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
            설명
        </label>
        <textarea wire:model="form.description" 
                  id="description" 
                  name="description"
                  rows="3"
                  class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="이 채널의 용도를 설명하세요"></textarea>
        @error('form.description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 우선순위 --}}
    <div class="mb-4">
        <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">
            우선순위
        </label>
        <input type="number" 
               wire:model="form.priority" 
               id="priority" 
               name="priority"
               min="0"
               max="999"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="0">
        <p class="mt-1 text-xs text-gray-500">낮은 숫자가 높은 우선순위입니다</p>
        @error('form.priority')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 활성화 상태 --}}
    <div class="mb-4">
        <label for="is_active" class="block text-xs font-medium text-gray-700 mb-1">
            상태
        </label>
        <select wire:model="form.is_active" 
                id="is_active" 
                name="is_active"
                class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="1">활성</option>
            <option value="0">비활성</option>
        </select>
        @error('form.is_active')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 헤더 설정 (선택사항) --}}
    <div class="mb-4">
        <label for="headers" class="block text-xs font-medium text-gray-700 mb-1">
            커스텀 헤더 (선택사항)
        </label>
        <textarea wire:model="form.headers" 
                  id="headers" 
                  name="headers"
                  rows="3"
                  class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder='{"Authorization": "Bearer token", "Content-Type": "application/json"}'></textarea>
        <p class="mt-1 text-xs text-gray-500">JSON 형식으로 입력하세요</p>
        @error('form.headers')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 페이로드 템플릿 (선택사항) --}}
    <div class="mb-4">
        <label for="payload_template" class="block text-xs font-medium text-gray-700 mb-1">
            페이로드 템플릿 (선택사항)
        </label>
        <textarea wire:model="form.payload_template" 
                  id="payload_template" 
                  name="payload_template"
                  rows="5"
                  class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder='{"text": "@{{message}}", "username": "@{{username}}"}'></textarea>
        <p class="mt-1 text-xs text-gray-500">JSON 형식으로 입력하세요. @{{변수명}} 형태로 변수를 사용할 수 있습니다</p>
        @error('form.payload_template')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 메타 정보 표시 --}}
    @if(isset($form['created_at']) || isset($form['updated_at']))
    <div class="border-t border-gray-200 pt-4 mb-4">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
            @if(isset($form['created_at']))
            <div>
                <dt class="text-xs font-medium text-gray-500">생성일</dt>
                <dd class="mt-1 text-xs text-gray-900">{{ \Carbon\Carbon::parse($form['created_at'])->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
            @if(isset($form['updated_at']))
            <div>
                <dt class="text-xs font-medium text-gray-500">수정일</dt>
                <dd class="mt-1 text-xs text-gray-900">{{ \Carbon\Carbon::parse($form['updated_at'])->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- 안내 메시지 --}}
    <div class="bg-blue-50 p-3 rounded-md mb-4">
        <h6 class="text-xs font-semibold text-blue-900 mb-2">ℹ️ 웹훅 채널 설정 안내</h6>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>• Slack: Incoming Webhooks 앱을 설치하고 Webhook URL을 복사하세요</li>
            <li>• Discord: 서버 설정 → 연동 → 웹훅에서 URL을 생성하세요</li>
            <li>• Teams: 채널 설정 → 커넥터 → Incoming Webhook을 구성하세요</li>
            <li>• 테스트 발송 기능으로 연결 상태를 확인할 수 있습니다</li>
        </ul>
    </div>
</div>
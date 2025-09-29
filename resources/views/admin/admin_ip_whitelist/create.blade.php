{{-- IP 화이트리스트 생성 폼 --}}
<div class="space-y-4">
    {{-- IP 타입 선택 --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">IP 타입</label>
        <select wire:model.live="form.type" 
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="single">단일 IP</option>
            <option value="range">IP 범위</option>
            <option value="cidr">CIDR 표기법</option>
        </select>
    </div>

    {{-- 단일 IP 입력 --}}
    @if(($form['type'] ?? 'single') === 'single')
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">IP 주소 <span class="text-red-500">*</span></label>
        <input type="text" 
               wire:model="form.ip_address"
               placeholder="예: 192.168.1.100"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        @error('form.ip_address')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif

    {{-- IP 범위 입력 --}}
    @if(($form['type'] ?? 'single') === 'range')
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">시작 IP <span class="text-red-500">*</span></label>
            <input type="text" 
                   wire:model="form.ip_range_start"
                   placeholder="예: 192.168.1.1"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.ip_range_start')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">종료 IP <span class="text-red-500">*</span></label>
            <input type="text" 
                   wire:model="form.ip_range_end"
                   placeholder="예: 192.168.1.255"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.ip_range_end')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    @endif

    {{-- CIDR 입력 --}}
    @if(($form['type'] ?? 'single') === 'cidr')
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">기본 IP <span class="text-red-500">*</span></label>
            <input type="text" 
                   wire:model="form.ip_address"
                   placeholder="예: 192.168.1.0"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.ip_address')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">프리픽스 <span class="text-red-500">*</span></label>
            <input type="number" 
                   wire:model="form.cidr_prefix"
                   min="1" max="32"
                   placeholder="예: 24"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.cidr_prefix')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    @endif

    {{-- 설명 --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">설명 <span class="text-red-500">*</span></label>
        <input type="text" 
               wire:model="form.description"
               placeholder="예: 본사 사무실"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        @error('form.description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 활성화 상태 --}}
    <div class="flex items-center">
        <input type="checkbox" 
               wire:model="form.is_active"
               id="is_active"
               class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="is_active" class="ml-2 block text-xs text-gray-900">
            즉시 활성화
        </label>
    </div>

    {{-- 만료일 설정 --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">만료일시 <span class="text-gray-400">(선택)</span></label>
        <input type="datetime-local" 
               wire:model="form.expires_at"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        <p class="mt-1 text-xs text-gray-500">임시 허용인 경우 만료일을 설정하세요.</p>
        @error('form.expires_at')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 현재 IP 힌트 --}}
    @if(isset($userIpHint))
    <div class="p-3 bg-blue-50 border border-blue-200 rounded">
        <p class="text-xs text-blue-700">
            <strong>힌트:</strong> 현재 접속 중인 IP 주소는 <code class="font-mono bg-white px-1 py-0.5 rounded text-xs">{{ $userIpHint }}</code> 입니다.
        </p>
    </div>
    @endif
</div>
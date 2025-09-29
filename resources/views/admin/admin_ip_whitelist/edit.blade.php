{{-- IP 화이트리스트 수정 폼 --}}
<div class="space-y-4">
    {{-- IP 타입 (읽기 전용) --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">IP 타입</label>
        <input type="text" 
               value="{{ $form['type'] === 'single' ? '단일 IP' : ($form['type'] === 'range' ? 'IP 범위' : 'CIDR 표기법') }}"
               disabled
               class="block w-full h-8 px-2.5 text-xs bg-gray-100 border border-gray-200 rounded">
        <p class="mt-1 text-xs text-gray-500">IP 타입은 수정할 수 없습니다.</p>
    </div>

    {{-- 단일 IP 수정 --}}
    @if($form['type'] === 'single')
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">IP 주소 <span class="text-red-500">*</span></label>
        <input type="text" 
               wire:model="form.ip_address"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        @error('form.ip_address')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif

    {{-- IP 범위 수정 --}}
    @if($form['type'] === 'range')
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">시작 IP <span class="text-red-500">*</span></label>
            <input type="text" 
                   wire:model="form.ip_range_start"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.ip_range_start')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">종료 IP <span class="text-red-500">*</span></label>
            <input type="text" 
                   wire:model="form.ip_range_end"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.ip_range_end')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    @endif

    {{-- CIDR 수정 --}}
    @if($form['type'] === 'cidr')
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">기본 IP <span class="text-red-500">*</span></label>
            <input type="text" 
                   wire:model="form.ip_address"
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
            활성화 상태
        </label>
    </div>

    {{-- 만료일 설정 --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">만료일시</label>
        <input type="datetime-local" 
               wire:model="form.expires_at"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        @error('form.expires_at')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 경고 메시지 --}}
    @if(isset($showWarning) && $showWarning)
    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
        <p class="text-xs text-yellow-700">
            <strong>경고:</strong> {{ $warningMessage }}
        </p>
    </div>
    @endif

    {{-- 최근 접근 정보 --}}
    @if(isset($lastAccessInfo))
    <div class="p-3 bg-gray-50 border border-gray-200 rounded">
        <p class="text-xs text-gray-700">
            <strong>최근 접근:</strong> {{ $lastAccessInfo }}
        </p>
    </div>
    @endif

    {{-- 추가 정보 --}}
    <div class="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded">
        <div>
            <span class="text-xs text-gray-500">추가한 관리자:</span>
            <span class="text-xs font-medium text-gray-900">{{ $form['added_by'] ?? '-' }}</span>
        </div>
        <div>
            <span class="text-xs text-gray-500">생성일:</span>
            <span class="text-xs font-medium text-gray-900">
                {{ isset($form['created_at']) ? \Carbon\Carbon::parse($form['created_at'])->format('Y-m-d H:i') : '-' }}
            </span>
        </div>
    </div>
</div>
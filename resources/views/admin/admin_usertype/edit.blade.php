<div class="space-y-4">
    <div>
        <label for="code" class="block text-xs font-medium text-gray-700 mb-1">
            타입 코드
        </label>
        <input type="text" 
               wire:model="form.code" 
               id="code" 
               name="code"
               class="block w-full h-8 px-2.5 text-xs bg-gray-50 border border-gray-200 rounded cursor-not-allowed"
               placeholder="예: super, admin, staff"
               maxlength="50"
               readonly
               disabled>
        <p class="mt-1 text-xs text-gray-500">타입 코드는 수정할 수 없습니다</p>
    </div>

    <div>
        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
            등급명 <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               wire:model="form.name" 
               id="name" 
               name="name"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="예: Super Admin"
               required>
        @error('form.name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
            설명
        </label>
        <textarea wire:model="form.description" 
                  id="description" 
                  name="description"
                  rows="3"
                  class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="이 등급에 대한 설명을 입력하세요"></textarea>
        @error('form.description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="level" class="block text-xs font-medium text-gray-700 mb-1">
            권한 레벨 <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               wire:model="form.level" 
               id="level" 
               name="level"
               min="0"
               max="100"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="0-100"
               required>
        <p class="mt-1 text-xs text-gray-500">0-100 사이의 값 (높을수록 높은 권한)</p>
        @error('form.level')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center">
        <input type="checkbox" 
               wire:model="form.enable" 
               id="enable" 
               name="enable"
               class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="enable" class="ml-2 block text-xs text-gray-900">
            활성화
        </label>
    </div>

    <div>
        <label for="pos" class="block text-xs font-medium text-gray-700 mb-1">
            정렬 순서
        </label>
        <input type="number" 
               wire:model="form.pos" 
               id="pos" 
               name="pos"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        @error('form.pos')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
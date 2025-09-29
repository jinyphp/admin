{{-- Ipwhitelist 수정 폼 --}}
<div class="space-y-4">
    {{-- Name 필드 --}}
    <div>
        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
            Name <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               wire:model="form.name" 
               id="name" 
               name="name"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Enter name"
               required>
        @error('form.name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description 필드 --}}
    <div>
        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
            Description
        </label>
        <textarea wire:model="form.description" 
                  id="description" 
                  name="description"
                  rows="3"
                  class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter description"></textarea>
        @error('form.description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status 필드 --}}
    <div>
        <label for="status" class="block text-xs font-medium text-gray-700 mb-1">
            Status
        </label>
        <select wire:model="form.status" 
                id="status" 
                name="status"
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        @error('form.status')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 메타 정보 표시 --}}
    @if(isset($form['created_at']) || isset($form['updated_at']))
    <div class="pt-4 border-t border-gray-200">
        <dl class="space-y-2">
            @if(isset($form['created_at']))
            <div class="flex justify-between text-xs">
                <dt class="text-gray-500">Created:</dt>
                <dd class="text-gray-900">{{ \Carbon\Carbon::parse($form['created_at'])->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
            @if(isset($form['updated_at']))
            <div class="flex justify-between text-xs">
                <dt class="text-gray-500">Updated:</dt>
                <dd class="text-gray-900">{{ \Carbon\Carbon::parse($form['updated_at'])->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- 추가 필드들을 여기에 추가하세요 --}}
    {{-- 
    <div>
        <label for="field_name" class="block text-xs font-medium text-gray-700 mb-1">
            Field Label
        </label>
        <input type="text" 
               wire:model="form.field_name" 
               id="field_name" 
               name="field_name"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Enter value">
        @error('form.field_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    --}}
</div>
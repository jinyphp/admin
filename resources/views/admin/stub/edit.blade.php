{{-- Admin 수정 폼 --}}
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


</div>

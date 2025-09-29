<div>
    <div class="flex gap-3 mb-4">
        <div class="flex-1">
            <input type="text" 
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="Search sessions..." 
                   wire:model.debounce.300ms="search">
        </div>
        <div>
            <select class="h-8 px-2.5 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                    wire:model="filter.enable">
                <option value="">All Status</option>
                <option value="1">Enabled</option>
                <option value="0">Disabled</option>
            </select>
        </div>
    </div>
</div>
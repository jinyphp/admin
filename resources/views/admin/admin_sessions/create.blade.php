<div>
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Sessions</h2>
    
    <form>
        <div class="mb-4">
            <label for="title" class="block text-xs font-medium text-gray-700 mb-1">Title</label>
            <input type="text" 
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="title" 
                   name="title" 
                   required>
        </div>
        
        <div class="mb-4">
            <label for="description" class="block text-xs font-medium text-gray-700 mb-1">Description</label>
            <textarea class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                      id="description" 
                      name="description" 
                      rows="3"></textarea>
        </div>
        
        <div class="mb-4 flex items-center">
            <input type="checkbox" 
                   class="h-4 w-4 text-blue-600 border-gray-200 rounded focus:ring-1 focus:ring-blue-500" 
                   id="enable" 
                   name="enable" 
                   checked>
            <label class="ml-2 text-xs text-gray-700" for="enable">
                Enable
            </label>
        </div>
        
        <div class="mb-4">
            <label for="pos" class="block text-xs font-medium text-gray-700 mb-1">Position</label>
            <input type="number" 
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="pos" 
                   name="pos" 
                   value="0">
        </div>
        
        <div class="mb-4">
            <label for="depth" class="block text-xs font-medium text-gray-700 mb-1">Depth</label>
            <input type="number" 
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="depth" 
                   name="depth" 
                   value="0">
        </div>
        
        <div class="mb-4">
            <label for="ref" class="block text-xs font-medium text-gray-700 mb-1">Reference</label>
            <input type="number" 
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                   id="ref" 
                   name="ref" 
                   value="0">
        </div>
    </form>
</div>
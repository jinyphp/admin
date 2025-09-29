<div wire:ignore.self>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Drawer -->
    @if($isOpen)
    <div class="fixed inset-0 z-[9999] overflow-hidden"
         aria-labelledby="drawer-title" 
         role="dialog" 
         aria-modal="true"
         x-data="{ show: @entangle('isOpen').live }"
         x-init="$watch('show', value => {
             if (value) {
                 document.body.style.overflow = 'hidden';
             } else {
                 document.body.style.overflow = '';
             }
         })"
         x-show="show"
         x-cloak
         style="display: none;">
        
        <!-- Background overlay -->
        <div x-show="show" 
             x-transition:enter="ease-in-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-500"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-gray-900/50 transition-opacity backdrop-blur-sm"
             @click="$wire.close()"></div>

        <!-- Drawer panel -->
        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex z-10">
            <div x-show="show"
                 x-transition:enter="transform transition ease-in-out duration-500"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="relative w-screen max-w-md z-10">
                
                <div class="h-full flex flex-col bg-white shadow-xl">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-blue-700">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-white" id="drawer-title">
                                Detail View Settings
                            </h2>
                            <button wire:click="close" 
                                    class="ml-3 text-blue-200 hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-blue-200">
                            Customize detail view display options
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <div class="space-y-6">
                            <!-- Date Format -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Date Format</h3>
                                <div>
                                    <label for="dateFormat" class="block text-sm font-medium text-gray-700">Display format</label>
                                    <select wire:model="dateFormat" id="dateFormat" 
                                            class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="Y-m-d">2025-01-25</option>
                                        <option value="Y-m-d H:i:s">2025-01-25 14:30:00</option>
                                        <option value="d/m/Y">25/01/2025</option>
                                        <option value="m/d/Y">01/25/2025</option>
                                        <option value="F j, Y">January 25, 2025</option>
                                        <option value="M j, Y g:i A">Jan 25, 2025 2:30 PM</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Action Buttons</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input wire:model="enableEdit" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Show Edit button</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableDelete" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Show Delete button</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableCreate" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Show Create New button</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="enableListButton" type="checkbox" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Show Back to List button</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Visible Sections -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Display Sections</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input wire:model="visibleSections" type="checkbox" value="information"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Template Information</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="visibleSections" type="checkbox" value="timestamps"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Timestamps</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Visible Fields -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Visible Fields</h3>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="space-y-2">
                                        @foreach(['id' => 'ID', 'title' => 'Title', 'description' => 'Description', 'enable' => 'Status', 'created_at' => 'Created At', 'updated_at' => 'Updated At'] as $field => $label)
                                        <label class="flex items-center">
                                            <input wire:model="visibleFields" type="checkbox" value="{{ $field }}"
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between">
                            <button wire:click="resetToDefaults" type="button" 
                                    class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                                Reset to Defaults
                            </button>
                            <div class="space-x-3">
                                <button wire:click="close" type="button" 
                                        class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                                    Cancel
                                </button>
                                <button wire:click="save" type="button" 
                                        class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                                    Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
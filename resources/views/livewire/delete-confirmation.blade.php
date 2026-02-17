<div>
    @if($modalVisible)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete this item?</p>

                <div class="flex justify-end space-x-2">
                    <button 
                        wire:click="$set('modalVisible', false)" 
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button 
                        wire:click="delete" 
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

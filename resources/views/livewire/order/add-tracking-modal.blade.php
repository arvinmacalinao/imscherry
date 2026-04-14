<div>
    @if($showModal)
        <div class="modal modal-blur fade show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Add Tracking Number</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <input 
                            type="text" 
                            wire:model="tracking_number" 
                            class="form-control"
                            placeholder="Enter tracking number"
                        >

                        @error('tracking_number') 
                            <small class="text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="$set('showModal', false)">
                            Cancel
                        </button>

                        <button class="btn btn-primary" wire:click="save">
                            Save
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- backdrop --}}
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
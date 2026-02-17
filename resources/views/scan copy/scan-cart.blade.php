<div class="p-4 bg-white rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-3">
        Scan Orders for {{ $status_id == 2 ? 'Packing' : ($status_id == 3 ? 'Shipping' : 'Return') }}
    </h2>

    <input type="text" 
           wire:model="scan_code" 
           wire:keydown.enter="scan" 
           placeholder="Scan tracking number..." 
           class="form-control mb-3" autofocus>

    @if($message)
        <div class="alert alert-info">{{ $message }}</div>
    @endif

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>Tracking No</th>
                <th>Customer</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cart_items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->options->customer }}</td>
                    <td>
                        <button wire:click="removeItem('{{ $item->rowId }}')" class="btn btn-danger btn-sm">Remove</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No scanned items yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3 flex gap-2">
        <button wire:click="confirmScans" class="btn btn-success">Confirm All</button>
        <button wire:click="printList" class="btn btn-primary">Print List</button>
    </div>
</div>

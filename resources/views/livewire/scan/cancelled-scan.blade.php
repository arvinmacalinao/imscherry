<div class="p-4">
    <h2 class="text-xl font-bold mb-3">Cancel Order Scan</h2>

    <input type="text"
           wire:model.defer="scanInput"
           wire:keydown.enter="scan"
           autofocus
           placeholder="Scan or type tracking/order number..."
           class="w-full border rounded p-2">

    @error('scanInput')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror

    <table class="table-auto w-full mt-4 border">
        <thead class="bg-gray-200">
        <tr>
            <th class="p-2 border">Order #</th>
            <th class="p-2 border">Tracking #</th>
            <th class="p-2 border">Status</th>
            <th class="p-2 border">Scanned At</th>
        </tr>
        </thead>

        <tbody>
        @foreach($scannedOrders as $o)
            <tr>
                <td class="border p-2">{{ $o['order_number'] }}</td>
                <td class="border p-2">{{ $o['tracking_number'] }}</td>
                <td class="border p-2">{{ $o['status'] }}</td>
                <td class="border p-2">{{ $o['scanned_at'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

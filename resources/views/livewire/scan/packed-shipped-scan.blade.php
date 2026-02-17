<div class="p-5">

    <h1 class="text-2xl font-bold mb-3">Packed/Shipped Scan</h1>

    {{-- @if($message)
        <div class="p-2 bg-green-200 text-green-800 rounded mb-3">
            {{ $message }}
        </div>
    @endif --}}

    <form wire:submit.prevent="scan">
        <input type="text"
               wire:model="barcode"
               class="border p-2 w-full"
               placeholder="Scan order number or tracking number..."
               autofocus>
    </form>

    <div class="mt-4">
        <h2 class="font-bold mb-2">Scanned List ({{ count($scannedOrders) }})</h2>

        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Tracking</th>
                    <th>Customer</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
            @foreach($scannedOrders as $i => $item)
                <tr>
                    <td>{{ $item['order_number'] }}</td>
                    <td>{{ $item['tracking'] }}</td>
                    <td>{{ $item['customer'] }}</td>
                    <td>
                        <button wire:click="remove({{ $i }})" class="text-red-600">Remove</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <button wire:click="confirm"
                class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
            Confirm
        </button>
    </div>
</div>

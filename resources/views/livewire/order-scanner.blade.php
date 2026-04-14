<div class="p-4">

    {{-- Message --}}
    @if($message)
        <div class="mb-3 p-2 bg-green-100 text-green-800 rounded">
            {{ $message }}
        </div>
    @endif

    {{-- Scan Form --}}
    <form wire:submit.prevent="scanBarcode" class="d-flex gap-2">

        <input
            type="text"
            wire:model.defer="barcode"
            placeholder="Scan barcode..."
            autofocus
            class="border p-2 flex-1"
        >

        <input
            type="number"
            wire:model.defer="scanQty"
            min="1"
            class="border p-2 w-24 text-center"
        >

        <button class="bg-blue-600 px-4 text-white">
            Scan
        </button>
    </form>

    {{-- Items Table --}}
    <table class="table-auto w-full mt-4">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Scanned</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
        @foreach($this->order->details as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->scanned_qty }}</td>
                <td>
                    @if($item->scanned_qty >= $item->quantity)
                        <span class="text-green-600 font-bold">Done</span>
                    @else
                        <span class="text-red-600">On Hold</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
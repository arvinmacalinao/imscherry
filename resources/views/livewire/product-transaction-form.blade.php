<div>

    @session('message')
    <div class="p-4 bg-green-100">
        {{ $value }}
    </div>
    @endsession


    <table class="table table-bordered" id="products_table">
        <thead class="thead-dark">
            <tr>
                <th class="align-middle">Product</th>
                <th class="align-middle text-center">Quantity</th>
                <th class="align-middle text-center">No. of Stocks</th>
                <th class="align-middle text-center">Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($transactionProducts as $index => $transactionProduct)
            <tr>
                <td class="align-middle">
                    @if($transactionProduct['is_saved'])
                        <input type="hidden" name="transactionProducts[{{$index}}][product_id]" value="{{ $transactionProduct['product_id'] }}">

                        {{ $transactionProduct['product_name'] }}
                    @else
                        <select wire:model.live="transactionProducts.{{$index}}.product_id"
                                id="transactionProducts[{{$index}}][product_id]"
                                class="form-control text-center @error('transactionProducts.' . $index . '.product_id') is-invalid @enderror"
                        >
                            <option value="" class="text-center">-- choose product --</option>
                            @foreach ($allProducts as $product)
                                <option value="{{ $product->id }}" class="text-center">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('transactionProducts.' . $index)
                            <em class="text-danger">
                                {{ $message }}
                            </em>
                        @enderror
                    @endif
                </td>

                <td class="align-middle text-center">
                    @if($transactionProduct['is_saved'])
                        {{ $transactionProduct['quantity'] }}

                        <input type="hidden"
                               name="transactionProducts[{{$index}}][quantity]"
                               value="{{ $transactionProduct['quantity'] }}"
                        >
                    @else
                        <input type="number"
                               wire:model="transactionProducts.{{$index}}.quantity"
                               id="transactionProducts[{{$index}}][quantity]"
                               class="form-control"
                        />
                    @endif
                </td>

                <td class="align-middle">
                     @if($transactionProduct['is_saved'])
                        {{ $transactionProduct['stock'] }}
                                
                        <input type="hidden"
                               name="transactionProducts[{{$index}}][stock]"
                               value="{{ $transactionProduct['stock'] }}"
                        >
                    @else
                        {{-- Optional: display stock while selecting product --}}
                        @php
                            $selectedId = (int) ($transactionProduct['product_id'] ?? 0);
                            $product = $allProducts->firstWhere('id', $selectedId);
                        @endphp
                        {{ $product?->quantity ?? '—' }}
                    @endif
                </td>

                <td class="align-middle text-center">
                    @if($transactionProduct['is_saved'])
                        <button type="button" wire:click="editProduct({{$index}})" class="btn btn-icon btn-outline-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                        </button>

                    @elseif($transactionProduct['product_id'])

                        <button type="button" wire:click="saveProduct({{$index}})" class="btn btn-icon btn-outline-success mr-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                        </button>
                    @endif

                    <button type="button" wire:click="removeProduct({{$index}})" class="btn btn-icon btn-outline-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </button>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4"></td>
                <td class="text-center">
                    <button type="button" wire:click="addProduct" class="btn btn-icon btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    </button>
                </td>
            </tr>

        </tbody>
    </table>
</div>

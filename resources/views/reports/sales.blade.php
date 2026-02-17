@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- FILTER FORM --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET">
                    <div class="row g-2 align-items-end">

                        {{-- Date From --}}
                        <div class="col-md-2">
                            <label class="form-label">From</label>
                            <input
                                type="date"
                                name="date_from"
                                value="{{ request('date_from') }}"
                                class="form-control"
                            >
                        </div>

                        {{-- Date To --}}
                        <div class="col-md-2">
                            <label class="form-label">To</label>
                            <input
                                type="date"
                                name="date_to"
                                value="{{ request('date_to') }}"
                                class="form-control"
                            >
                        </div>

                        {{-- Shop --}}
                        <div class="col-md-2">
                            <label class="form-label">Shop</label>
                            <select name="shop_id" class="form-select">
                                <option value="">All Shops</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}"
                                        @selected(request('shop_id') == $shop->id)>
                                        {{ $shop->invoice_prefix }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product --}}
                        <div class="col-md-3">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-select">
                                <option value="">All Products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        @selected(request('product_id') == $product->id)>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Submit --}}
                        <div class="col-md-1 d-grid">
                            <button class="btn btn-primary">
                                Filter
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- SALES TABLE --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sales Report (Products)</h3>
                <div class="card-actions">
                    <x-button.print
                        class="btn-icon"
                        route="{{ route('sales.export', request()->query()) }}"
                        target="_blank"
                    />
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Order No</th>
                            <th>Product</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="text-center">
                                    {{ $item->order->order_number ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->product->name ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $item->order->payment_type ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $item->quantity }}
                                </td>

                                <td class="text-center">
                                    {{ Number::currency(
                                        $item->quantity * $item->product->price,
                                        'PHP'
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No results found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($items->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">
                                    Grand Total
                                </th>
                                <th class="text-center">
                                    {{ Number::currency(
                                        $items->sum(fn ($i) =>
                                            $i->quantity * $i->product->price
                                        ),
                                        'PHP'
                                    ) }}
                                </th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- FILTER FORM --}}
        <div class="card mb-3" style="height: auto;">
            <div class="card-body" style="height: auto;">
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
                                        {{ $shop->name }}
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
                <h3 class="card-title">Returned Order Report</h3>
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
                            <th class="text-center">Order Date</th>
                            <th class="text-center">Order Details</th>
                            <th class="text-center">Date Returned</th>
                            <th class="text-center">Returned By</th>
                            <th class="text-center">Shop</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $order)
                                            
                            @php
                                $returnlog = $order->statusLogs->first();
                            @endphp
                        
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>
                            
                                <td class="text-center">
                                    {{ $order->order_number }}
                                </td>
                            
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') }}
                                </td>

                                {{-- 🆕 ORDER DETAILS --}}
                                <td>
                                    @foreach ($order->details as $detail)
                                        <div>
                                            <small>
                                                {{ $detail->product->name ?? '-' }}
                                                |
                                                Status: {{ $detail->status_id ?? '-' }}
                                            </small>
                                        </div>
                                    @endforeach
                                </td>
                            
                                <td class="text-center">
                                    {{ optional($returnlog)->created_at
                                        ? $returnlog->created_at->format('Y-m-d H:i')
                                        : '-' }}
                                </td>
                        
                            
                                {{-- RETURNED BY --}}
                                <td class="text-center">
                                    {{ optional($returnlog->actor)->name ?? '-' }}
                                </td>
                            
                                {{-- SHOP --}}
                                <td class="text-center">
                                    {{ $order->shopName->invoice_prefix ?? '-' }}
                                </td>
                            </tr>
                        
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No Returned orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

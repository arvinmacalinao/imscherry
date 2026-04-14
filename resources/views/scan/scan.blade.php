@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- ✅ Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ✅ Scan Form --}}
        <form action="{{ route('order.scan_' . $type) }}" method="POST">
            @csrf
            <x-card>
                <x-slot:header>
                    <x-slot:title>
                        {{ __('Scan Parcel') . ' (' . $type . ')' }}
                    </x-slot:title>
                    <x-slot:actions>
                        <x-action.close route="{{ route('dashboard') }}" />
                    </x-slot:actions>
                </x-slot:header>

                <x-slot:content>
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">{{ __('Order Number/Tracking Number') }}</label>
                        <input type="text" name="tracking_number" id="tracking_number" class="form-control"
                               placeholder="Scan or type order/tracking number..." autofocus required>
                        <small class="form-hint">
                            Scan barcode or type manually.
                        </small>
                    </div>
                </x-slot:content>

                <x-slot:footer class="text-end">
                    <x-button type="submit">{{ __('Add to List') }}</x-button>
                </x-slot:footer>
            </x-card>
        </form>

        {{-- ✅ Scanned Orders Table --}}
       @php
            use Gloudemans\Shoppingcart\Facades\Cart;
            $cartItems = Cart::instance('order_' . $type)->content();
        @endphp

        @if($cartItems->isNotEmpty())
            <x-card class="mt-4">
                <x-slot:header>
                    <x-slot:title>{{ __('Scanned Orders') }}</x-slot:title>
                </x-slot:header>

                <x-slot:content>
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tracking Number</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartItems as $index => $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->options->customer ?? 'N/A' }}</td>
                                    <td>{{ $item->options->status_id ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('order.remove', ['type' => $type, 'rowId' => $item->rowId]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <form action="{{ route('order.confirm', $type) }}" method="POST" target="_blank" onsubmit="setTimeout(() => location.reload(), 500);">
                        @csrf
                        <x-button type="submit" class="btn btn-success">
                            {{ __('Confirm') }}
                        </x-button>
                    </form>
                </x-slot:content>
            </x-card>
        @else
            <p class="mt-4 text-muted">No scanned orders yet.</p>
        @endif
    </div>
</div>
@endsection

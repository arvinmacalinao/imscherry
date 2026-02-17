@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

        {{-- Flash Messages --}}
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

        {{-- Scan Form --}}
        <form action="{{ route('warehouse.scan.process') }}" method="POST">
            @csrf
            <x-card>
                <x-slot:header>
                    <x-slot:title>Scan Product for Warehouse Pull</x-slot:title>
                </x-slot:header>

                <x-slot:content>
                    <div class="mb-3">
                        <label for="product_code" class="form-label">Product Code</label>
                        <input type="text" name="sku" id="product_code" class="form-control"
                       placeholder="Scan or type product code..." autofocus required>

                        <label for="quantity" class="form-label mt-3">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required>

                        <small class="form-hint">Scan barcode or type manually.</small>
                    </div>
                </x-slot:content>

                <x-slot:footer class="text-end">
                    <x-button type="submit">Add to Pull List</x-button>
                </x-slot:footer>
            </x-card>
        </form>

        {{-- Scanned Products Table --}}
        @php
            $cartItems = Cart::instance('warehouse_pull')->content();
        @endphp

        @if($cartItems->isNotEmpty())
            <x-card class="mt-4">
                <x-slot:header>
                    <x-slot:title>Scanned Products</x-slot:title>
                </x-slot:header>

                <x-slot:content>
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartItems as $index => $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->qty }}</td>
                                    {{-- <td>{{ $item->options->status ?? 'Pending' }}</td>
                                    <td>
                                        <form action="{{ route('warehouse.scan.remove', ['rowId' => $item->rowId]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <form action="{{ route('warehouse.scan.confirm') }}" method="POST">
                        @csrf
                        <x-button type="submit" class="btn btn-success">
                            Confirm Pull
                        </x-button>
                    </form>
                </x-slot:content>
            </x-card>
        @else
            <p class="mt-4 text-muted">No products scanned yet.</p>
        @endif
    </div>
</div>
@endsection

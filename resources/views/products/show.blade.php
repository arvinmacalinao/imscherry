@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl mb-3">
        <div class="row g-2 align-items-center mb-3">
            <div class="col">
                <h2 class="page-title">
                    {{ __('Edit Product') }}
                </h2>
            </div>
        </div>

        @include('partials._breadcrumbs', ['model' => $product])
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ __('Product Details') }}
                            </h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                                <tbody>
                                    <tr>
                                        <td>Name</td>
                                        <td>{{ $product->name }}</td>
                                    </tr>
                                   <tr>
                                        <td><span class="text-secondary">SKU</span></td>
                                        <td>{{ $product->sku }}</td>
                                    </tr>
                                   <tr>
                                        <td>Category</td>
                                        <td>
                                            <a href="{{ route('categories.show', $product->category) }}" class="badge bg-blue-lt">
                                                {{ $product->category->name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Quantity</td>
                                        <td>{{ $product->quantity }}</td>
                                    </tr>
                                    <tr>
                                        <td>Price</td>
                                        <td>{{ $product->price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer text-end">
                            <x-button.edit route="{{ route('products.edit', $product) }}">
                                {{ __('Edit') }}
                            </x-button.edit>

                            <x-button.back route="{{ route('products.index') }}">
                                {{ __('Cancel') }}
                            </x-button.back>
                        </div>
                    </div>
                    <br>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ __('Product Transaction History') }}
                            </h3>
                        </div>
                        @if($transactions->isEmpty())
                        <div class="p-1">
                            <p>No transaction history found for this product.</p>
                        </div>    
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Batch ID</th>
                                            <th>Type</th>
                                            <th>Change</th>
                                            <th>Old Quantity</th>
                                            <th>Updated Quantity</th>
                                            <th>User</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $batch)
                                            @php
                                                // Filter items in this batch for this product only
                                                $productItems = $batch->items->where('product_id', $product->id);
                                            @endphp
                                            @foreach($productItems as $item)
                                                <tr>
                                                    <td>{{ $batch->id }}</td>
                                                    <td>{{ ucfirst($batch->type->name) }}</td>
                                                    <td>{{ $item->quantity_change }}</td>
                                                    <td>{{ $item->before_quantity }}</td>
                                                    <td>{{ $item->after_quantity }}</td>
                                                    <td>{{ $batch->user_created->name ?? 'N/A' }}</td>
                                                    <td>{{ $batch->created_at->format('Y-m-d H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

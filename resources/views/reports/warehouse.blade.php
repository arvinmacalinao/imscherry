@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        <div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Warehouse') }}
            </h3>
        </div>
        <div class="card-actions btn-group">
            <x-button.print class="btn-icon" route="{{ route('warehouse.export') }}" target="_blank"/>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
                <tr>
                    <th class="align-middle text-center w-1">
                        {{ __('No.') }}
                    </th>
                    <th scope="col" class="align-middle text-center">
                            {{ __('Name') }}
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                            {{ __('SKU') }}
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                            {{ __('Category') }}
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                            {{ __('Quantity') }}  
                    </th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td class="align-middle text-center">
                        {{ $loop->iteration }}
                    </td>
                    <td class="align-middle">
                        {{ $product->name }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $product->sku }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $product->category->name }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $product->quantity }}
                    </td>
                   
                </tr>
            @empty
                <tr>
                    <td class="align-middle text-center" colspan="7">
                        No results found
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>


    </div>
</div>
@endsection
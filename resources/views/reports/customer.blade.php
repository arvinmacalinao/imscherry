@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ __('Customer Report') }}</h3>
                </div>
                <div class="card-actions btn-group">
                    <x-button.print class="btn-icon" route="{{ route('customer.export') }}" />
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center w-1">No.</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">Address</th>
                            <th class="text-center">Total Orders</th>
                            <th class="text-center">Order List</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $customer->name }}</td>
                                <td class="text-center">{{ $customer->email }}</td>
                                <td class="text-center">{{ $customer->phone }}</td>
                                <td>{{ $customer->address }}</td>

                                {{-- Count of orders --}}
                                <td class="text-center">
                                    {{ $customer->orders->count() }}
                                </td>

                                {{-- Order list (joined by comma) --}}
                                <td>
                                    @if ($customer->orders->count())
                                        {{ $customer->orders->pluck('shopName.name')->unique()->join(', ') }}
                                    @else
                                        <span class="text-muted">No orders</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="7">No results found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>
@endsection

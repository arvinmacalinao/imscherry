@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if($orders->isEmpty())
    <div class="container-fluid">
        <x-alert/>
        @include('orders.partials.import-result')
    </div>
    <x-empty
        title="No orders found"
        message="Try adjusting your search or filter to find what you're looking for."
        button_label="{{ __('Add your first Order') }}"
        button_route="{{ route('orders.create') }}"
        button_label2="{{ __('Import Orders') }}"
        button_route2="{{ route('orders.import.view') }}"
    />
    @else
    <div class="container-fluid">
        <x-alert/>
        @include('orders.partials.import-result')

        <livewire:tables.order-table />
    </div>
    @endif
</div>
@endsection
@push('page-scripts')
<script>
     Livewire.on('redirectToUrl', data => {
        window.location.href = data.url;
    });
</script>

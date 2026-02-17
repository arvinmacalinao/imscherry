@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if($orders->isEmpty())
    <x-empty
        title="No orders found"
    />
    @else
    <div class="container-xl">
        <x-alert/>

        <livewire:tables.report.categories-table />
    </div>
    @endif
</div>
@endsection

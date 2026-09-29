@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <h2 class="page-title mb-3">{{ __('Sales Report') }}</h2>
        <livewire:tables.report.sales-table />
    </div>
</div>
@endsection

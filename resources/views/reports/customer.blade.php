@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <h2 class="page-title mb-3">{{ __('Customer Report') }}</h2>
        <livewire:tables.report.customer-report-table />
    </div>
</div>
@endsection

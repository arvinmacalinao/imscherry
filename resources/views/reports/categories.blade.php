@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <h2 class="page-title mb-3">{{ __('Category Report') }}</h2>
        <livewire:tables.report.category-report-table />
    </div>
</div>
@endsection

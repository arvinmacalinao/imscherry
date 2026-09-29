@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <h2 class="page-title mb-3">{{ __('Cancellation Report') }}</h2>
        <livewire:tables.report.cancellation-table />
    </div>
</div>
@endsection

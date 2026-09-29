@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <h2 class="page-title mb-3">{{ __('Returned Order Report') }}</h2>
        <livewire:tables.report.return-table />
    </div>
</div>
@endsection

@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- ✅ Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ✅ Scan box + scanned list: each scan is added without reloading the page --}}
        <livewire:scan-list :type="$type" />
    </div>
</div>
@endsection

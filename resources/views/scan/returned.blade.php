@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container container-xl">

        <h1 class="page-title">Return Scan</h1>

        @livewire('scan.returned-scan')  
        {{-- This loads the QC scan Livewire component --}}

    </div>
</div>
@endsection

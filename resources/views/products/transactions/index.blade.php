@extends('layouts.tabler')

@section('content')
<div class="page-body">
        <div class="container container-xl">
            <x-alert/>

            @livewire('product-transactions-table')
        </div>
</div>
@endsection

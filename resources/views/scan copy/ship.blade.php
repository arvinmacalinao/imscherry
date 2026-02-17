@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        {{-- ✅ Flash messages here --}}
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
        
        <form action="{{ route('order.scan1') }}" method="POST">
            @csrf
            <x-card>
                <x-slot:header>
                    <x-slot:title>
                        {{ __('Scan Parcels') }}
                    </x-slot:title>
                    <x-slot:actions>
                        <x-action.close route="{{ route('dashboard') }}" />
                    </x-slot:actions>
                </x-slot:header>

                <x-slot:content>
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">
                            {{ __('Tracking Number') }}
                        </label>
                        <input type="text" name="tracking_number" id="tracking_number"
                               class="form-control"
                               placeholder="Scan or type tracking number..."
                               autofocus required>
                        <small class="form-hint">
                            You can scan the barcode or type manually.
                        </small>
                    </div>
                </x-slot:content>

                <x-slot:footer class="text-end">
                    <x-button type="submit">
                        {{ __('Confirm Shipment') }}
                    </x-button>
                    <x-button.back route="{{ route('dashboard') }}">
                        {{ __('Cancel') }}
                    </x-button.back>
                </x-slot:footer>
            </x-card>
        </form>
    </div>
</div>
@endsection

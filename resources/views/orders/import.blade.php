@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        <form action="" method="POST" enctype="multipart/form-data">
            @csrf
            <x-card>
                <x-slot:header>
                    <x-slot:title>
                        {{ __('Import Orders') }}
                    </x-slot:title>

                    <x-slot:actions>
                        <x-action.close route="{{ route('orders.index') }}" />
                    </x-slot:actions>
                </x-slot:header>

                <x-slot:content>
                    <div class="mb-3">
                        <label for="platform_id" class="form-label">Select Shop</label>
                        <select id="shop_name_id"
                                name="shop_name_id"
                                class="form-select @error('shop_name_id') is-invalid @enderror"
                                required>
                            <option value="">-- Select Shop --</option>
                            @foreach($shopNames as $shop)
                                <option value="{{ $shop->id }}" {{ old('shop_name_id') == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->name }} ({{ $shop->platform->name }})
                                </option>
                            @endforeach
                        </select>

                        @error('shop_name_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                
                    {{-- File Upload --}}
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload Order Import</label>
                        <input type="file"
                               id="file"
                               name="file"
                               class="form-control @error('file') is-invalid @enderror"
                               accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                               required>
                    
                        @error('file')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </x-slot:content>

                <x-slot:footer class="text-end">
                    <x-button type="submit">
                        {{ __('Import') }}
                    </x-button>

                    <x-button.back route="{{ route('orders.index') }}">
                        {{ __('Cancel') }}
                    </x-button.back>
                </x-slot:footer>
            </x-card>
        </form>
    </div>
</div>
@endsection

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce

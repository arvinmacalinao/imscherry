@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        <x-alert/>

        <div class="row row-cards">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">

                            {{-- HEADER --}}
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">
                                        {{ __('Create Product Transaction') }}
                                    </h3>
                                </div>

                                <div class="card-actions btn-actions">
                                    <a href="{{ route('transactions.index') }}" class="btn-action">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                             height="24" viewBox="0 0 24 24" stroke-width="2"
                                             stroke="currentColor" fill="none"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M18 6l-12 12"></path>
                                            <path d="M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {{-- BODY --}}
                            <div class="card-body">

                                <div class="row gx-3 mb-3">

                                    {{-- Transaction Date --}}
                                    <div class="col-md-2">
                                        <label for="transaction_date" class="form-label required">
                                            {{ __('Transaction Date') }}
                                        </label>

                                        <input name="transaction_date"
                                               id="transaction_date"
                                               type="date"
                                               class="form-control @error('transaction_date') is-invalid @enderror"
                                               value="{{ old('transaction_date') ?? now()->format('Y-m-d') }}"
                                               required
                                        >

                                        @error('transaction_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Transaction Type --}}
                                    <x-tom-select
                                        label="Transaction Type"
                                        id="transaction_type_id"
                                        name="transaction_type_id"
                                        placeholder="Select Type"
                                        :data="$types"
                                        :options="[
                                            'searchField' => ['text'],
                                            'maxOptions' => 50
                                        ]"
                                    />
                                    <div class="col-md-3">
                                        <label for="note" class="form-label">
                                            {{ __('Reference No.') }}
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               id="reference_no"
                                               name="reference_no"
                                               value="{{ old('reference_no') }}"
                                        >
                                    </div>
                                    
                                    {{-- Note --}}
                                    <div class="col-md-3">
                                        <label for="note" class="form-label">
                                            {{ __('Note (Optional)') }}
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               id="note"
                                               name="note"
                                               value="{{ old('note') }}"
                                        >
                                    </div>
                                </div>

                                {{-- LIVEWIRE TRANSACTION ITEMS --}}
                                <livewire:product-transaction-form :cart-instance="'txn_cart'" />

                            </div>

                            {{-- FOOTER --}}
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Save Transaction') }}
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

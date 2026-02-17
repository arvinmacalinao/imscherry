@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        <x-alert/>

        <div class="row row-cards">
            <form action="{{ route('orders.store') }}" method="POST">
                @csrf
                <div class="row">

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">
                                        {{ __('Create Order') }}
                                    </h3>
                                </div>

                                <div class="card-actions btn-actions">
                                    {{--- {{ URL::previous() }} ---}}
                                    <a href="{{ route('orders.index') }}" class="btn-action">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M18 6l-12 12"></path><path d="M6 6l12 12"></path></svg>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                              <div class="row gx-3 mb-3">
                                    {{-- ORDER DATE --}}
                                    <div class="col-md-3">
                                        <label for="order_date" class="form-label required">Order Date</label>
                                        <input name="order_date" id="order_date" type="date"
                                               class="form-control @error('order_date') is-invalid @enderror"
                                               value="{{ old('order_date') ?? now()->format('Y-m-d') }}"
                                               required>
                                        @error('order_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                
                                    {{-- INVOICE NUMBER --}}
                                    <div class="col-md-3">
                                        <label for="invoice_no" class="form-label required">Invoice No.</label>
                                        <input type="text" class="form-control"
                                               id="invoice_no"
                                               name="invoice_no"
                                               value="ORDR"
                                               readonly>
                                        @error('invoice_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                
                                    {{-- PAYMENT TYPE --}}
                                    <div class="col-md-3">
                                        <label class="form-label required">Payment Type</label>
                                        <select name="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                                            <option value="">-- Select Payment Type --</option>
                                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="credit_debit" {{ old('payment_type') == 'credit_debit' ? 'selected' : '' }}>Credit/Debit Card</option>
                                            <option value="ewallet" {{ old('payment_type') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                                            <option value="salary_advance" {{ old('payment_type') == 'salary_advance' ? 'selected' : '' }}>Salary Advance</option>
                                            <option value="foc" {{ old('payment_type') == 'foc' ? 'selected' : '' }}>FOC (Free of Charge)</option>
                                        </select>
                                        @error('payment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ========================= --}}
                                {{-- X-TOM-SELECT COMPONENTS  --}}
                                {{-- ========================= --}}
                                <div class="row gx-3 mb-3">
                                    <div class="mb-3">
                                        <x-tom-select
                                            class="w-100"
                                            label="Shop Name"
                                            id="shop_name_id"
                                            name="shop_name_id"
                                            placeholder="Select Shop"
                                            :data="$shops"
                                            :options="[
                                                'searchField' => ['text'],
                                                'create' => true,
                                                'maxOptions' => 50
                                            ]"
                                        />
                                    </div>
                                    
                                    <div class="mb-3">
                                        <x-tom-select
                                            class="w-100"
                                            label="Customer"
                                            id="customer_id"
                                            name="customer_id"
                                            placeholder="Select Customer"
                                            :data="$customers"
                                            :options="[
                                                'searchField' => ['text'],
                                                'create' => true,
                                                'maxOptions' => 50
                                            ]"
                                        />
                                    </div>
                                </div>

                                {{-- REMARKS --}}
                                <div class="mb-3">
                                    <label class="form-label">Remarks / Notes</label>
                                    <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3"
                                              placeholder="Enter notes or remarks">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                    

                                <livewire:order-form :cart-instance="'order'" />
                                {{-- livewire:product-cart :cartInstance="'orders'"/>--}}
                            </div>

                            <div class="card-footer text-end">
                                {{--- onclick="return confirm('Are you sure you want to purchase?')" ---}}
                                {{--- @disabled($errors->isNotEmpty()) ---}}
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Create Invoice') }}
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

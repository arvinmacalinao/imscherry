@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">
                        {{ __('Product Transaction Details') }}
                    </h3>
                </div>

                <div class="card-actions btn-actions">
                    <x-action.close route="{{ route('transactions.index') }}"/>
                </div>
            </div>

            <div class="card-body">
                <div class="row row-cards mb-3">
                    <div class="col">
                        <label for="transaction_date" class="form-label">Transaction Date</label>
                        <input type="text"
                            class="form-control"
                            value="{{ $transaction->transaction_date }}"
                            disabled>
                    </div>

                    <div class="col">
                        <label for="type" class="form-label">Transaction Type</label>
                        <input type="text"
                            class="form-control"
                            value="{{ $transaction->type->name ?? '' }}"
                            disabled>
                    </div>

                    <div class="col">
                        <label for="note" class="form-label">Note</label>
                        <input type="text"
                            class="form-control"
                            value="{{ $transaction->note }}"
                            disabled>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Product Name</th>
                                <th class="text-center">Old Qty</th>
                                <th class="text-center">+/-</th>
                                <th class="text-center">Updated Stock</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($transaction->items as $item)
                                <tr>
                                    <td class="text-center">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->product->name }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->before_quantity }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->quantity_change }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->after_quantity }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

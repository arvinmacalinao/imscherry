@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header text-light {{ $statusColors[$order->status_id] ?? '' }}">
                    <div>
                        <h3 class="card-title">
                            {{ __('Order Details') }}
                        </h3>
                    </div>

                    <div class="card-actions btn-actions">
                        <div class="dropdown">
                            <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path></svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <form action="{{ route('orders.cancel', $order) }}"
                                      method="POST"
                                      onsubmit="return cancelWithRemarks(this)">
                                    @csrf
                                    @method('put')
                                                            
                                    <input type="hidden" name="remarks">
                                                            
                                    <button type="submit" class="dropdown-item text-danger">
                                        <!-- Cancel icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             width="24" height="24"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             fill="none">
                                            <path d="M18 6l-12 12"/>
                                            <path d="M6 6l12 12"/>
                                        </svg>
                                    
                                        {{ __('Cancel Order') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <x-action.close route="{{ route('orders.index') }}"/>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row row-cards mb-3">
                        <div class="col">
                            <label for="order_date" class="form-label required">
                                {{ __('Order Date') }}
                            </label>
                            <input type="text"
                                   id="order_date"
                                   class="form-control"
                                   value="{{ $order->order_date->format('d-m-Y') }}"
                                   disabled
                            >
                        </div>

                        <div class="col">
                            <label for="invoice_no" class="form-label required">
                                {{ __('Invoice No.') }}
                            </label>
                            <input type="text"
                                   id="invoice_no"
                                   class="form-control"
                                   value="{{ $order->invoice_no }}"
                                   disabled
                            >
                        </div>

                        <div class="col">
                            <label for="customer" class="form-label required">
                                {{ __('Customer') }}
                            </label>
                            <input type="text"
                                   id="customer"
                                   class="form-control"
                                   value="{{ $order->customer_name ?? '' }}"
                                   disabled
                            >
                        </div>

                        <div class="col">
                            <label for="payment_type" class="form-label required">
                                {{ __('Tracking Number') }}
                            </label>

                            <input type="text" id="payment_type" class="form-control" value="{{ $order->tracking_number ?? 'N/A' }}" disabled>
                        </div>

                        <div class="col">
                            <label for="status_id" class="form-label required">
                                {{ __('Status') }}
                            </label>

                            <input type="text" id="status_id" class="form-control" value="{{ $order->status->name ?? 'N/A' }}" disabled>
                        </div>
                        <div class="col">
                            <label for="remarks" class="form-label required">
                                {{ __('Remarks') }}
                            </label>

                            <input type="textarea" id="remarks" class="form-control" value="{{ $order->remarks ?? 'N/A' }}" disabled>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col" class="align-middle text-center">No.</th>
                                <th scope="col" class="align-middle text-center">Product Name</th>
                                <th scope="col" class="align-middle text-center">Product Code</th>
                                <th scope="col" class="align-middle text-center">Quantity</th>
                                <th scope="col" class="align-middle text-center">Price</th>
                                <th scope="col" class="align-middle text-center">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($order->details as $item)
                                <tr>
                                    <td class="align-middle text-center">
                                        {{ $loop->iteration  }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $item->product->name }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $item->product->sku }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ number_format($item->product->price, 2) ?? '' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ number_format($item->quantity * $item->product->price, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <br>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ __('Scan Items') }}
                    </h3>
                </div>
                <livewire:order-scanner :order="$order" />
            </div>
        </div>
    </div>
@endsection
<script>
function cancelWithRemarks(form) {
    const remarks = prompt('Please enter cancellation remarks:');

    if (remarks === null || remarks.trim() === '') {
        alert('Cancellation remarks are required.');
        return false;
    }

    form.querySelector('input[name="remarks"]').value = remarks;
    return true;
}
</script>

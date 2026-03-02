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
                    <div class="card-actions">
                        <div class="dropdown">
                            <a href="#" class="btn-action dropdown-toggle text-light" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <x-icon.vertical-dots/>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                            @if($order->status_id == 8)
                               {{-- ✅ QC DONE --}}
                               <form action="{{ route('orders.qcDone', $order) }}"
                                     method="POST"
                                     onsubmit="return submitWithRemarks(this, 'Please enter QC remarks:')">
                                   @csrf
                                   @method('put')

                                   <input type="hidden" name="remarks">

                                   <button type="submit"
                                           class="dropdown-item text-success d-flex align-items-center gap-2">

                                       {{-- Check icon --}}
                                       <svg xmlns="http://www.w3.org/2000/svg"
                                            width="18" height="18"
                                            viewBox="0 0 24 24"
                                            stroke-width="2"
                                            stroke="currentColor"
                                            fill="none">
                                           <path d="M5 12l5 5l10 -10"/>
                                       </svg>
                                   
                                       <span>QC Done</span>
                                   </button>
                               </form>
                            <div class="dropdown-divider"></div>
                            @endif
                            {{-- 🔴 Cancel Order --}}
                            <form action="{{ route('orders.cancel', $order) }}"
                                  method="POST"
                                  onsubmit="return submitWithRemarks(this, 'Please enter cancellation remarks:')">
                                @csrf
                                @method('put')
                        
                                <input type="hidden" name="remarks">
                        
                                <button type="submit"
                                        class="dropdown-item text-danger d-flex align-items-center gap-2">
                        
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         width="18" height="18"
                                         viewBox="0 0 24 24"
                                         stroke-width="2"
                                         stroke="currentColor"
                                         fill="none">
                                        <path d="M18 6l-12 12"/>
                                        <path d="M6 6l12 12"/>
                                    </svg>
                                
                                    <span>Cancel Order</span>
                                </button>
                            </form>
                        
                            <div class="dropdown-divider"></div>
                        
                            {{-- 🟡 Pending Order --}}
                            <form action="{{ route('orders.pending', $order) }}"
                                  method="POST"
                                  onsubmit="return submitWithRemarks(this, 'Please enter pending remarks:')">
                                @csrf
                                @method('put')
                        
                                <input type="hidden" name="remarks">
                        
                                <button type="submit"
                                        class="dropdown-item d-flex align-items-center gap-2">
                        
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         width="18" height="18"
                                         viewBox="0 0 24 24"
                                         stroke-width="2"
                                         stroke="currentColor"
                                         fill="none">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 7v5l3 3"/>
                                    </svg>
                                
                                    <span>Set as Pending</span>
                                </button>
                            </form>
                        </div>
                        </div>
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
                    <hr>
                    <div class="row row-cards mb-3">
                        <div class="col">
                            <label class="form-label required">
                                {{ __('Imported by') }}
                            </label>
                        
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $order->importLog?->actor?->name ?? '-' }}"
                                disabled
                            >
                        </div>
                        <div class="col">
                            <label class="form-label required">
                                {{ __('Invoiced By') }}
                            </label>
                        
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $order->invoicedLog?->actor?->name ?? '-' }}"
                                disabled
                            >
                        </div>
                        <div class="col">
                            <label class="form-label required">
                                {{ __('Picked By') }}
                            </label>
                        
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $order->pickedLog?->actor?->name ?? '-' }}"
                                disabled
                            >
                        </div>
                        <div class="col">
                            <label class="form-label required">
                                {{ __('QC By') }}
                            </label>
                        
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $order->qcLog?->actor?->name ?? '-' }}"
                                disabled
                            >
                        </div>
                        <div class="col">
                            <label class="form-label required">
                                {{ __('Packed/Shipped by') }}
                            </label>
                        
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $order->packshipLog?->actor?->name ?? '-' }}"
                                disabled
                            >
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-striped table-bordered align-middle w-100"
                               style="table-layout: fixed;">
                                        
                            <thead class="thead-light">
                            <tr>
                                <th class="text-center" style="width: 70px;">No.</th>
                                <th class="text-center" style="width: 240px;">Product Name</th>
                                <th class="text-center" style="width: 160px;">Product Code</th>
                                <th class="text-center" style="width: 110px;">Quantity</th>
                                <th class="text-center" style="width: 130px;">Price</th>
                                <th class="text-center" style="width: 150px;">Total</th>
                            
                                @if (in_array($order->status_id, [4, 5]))
                                    <th class="text-center" style="width: 160px;">Action</th>
                                @endif
                            </tr>
                            </thead>
                        
                            <tbody>
                            {{-- @php
                                dd($order->details);
                            @endphp --}}
                            @forelse ($order->details as $item)
                                <tr>
                                
                                    {{-- No --}}
                                    <td class="text-center">
                                        {{ $loop->iteration }}
                                    </td>
                                
                                    {{-- Product Name (wrap enabled) --}}
                                    <td class="text-start text-wrap"
                                        style="word-break: break-word;">
                                        {{ $item->product->name ?? '-' }}
                                    </td>
                                
                                    {{-- SKU --}}
                                    <td class="text-center">
                                        {{ $item->product->sku ?? '-' }}
                                    </td>
                                
                                    {{-- Quantity --}}
                                    <td class="text-center">
                                        {{ $item->quantity }}
                                    </td>
                                
                                    {{-- Price --}}
                                    <td class="text-center">
                                        {{ number_format($item->product->price ?? 0, 2) }}
                                    </td>
                                
                                    {{-- Total --}}
                                    <td class="text-center">
                                        {{ number_format(($item->quantity * ($item->product->price ?? 0)), 2) }}
                                    </td>
                                
                                    {{-- 🆕 ACTION COLUMN --}}
                                    @if ($order->status_id == 4)
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                Action
                                                                </button>
                                                            
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                
                                                {{-- Returned to Warehouse --}}
                                                <form method="POST"
                                                      action="">
                                                      {{-- route('returns.to-warehouse', $item->id) --}}
                                                    @csrf
                                                    <button type="submit"
                                                            class="dropdown-item d-flex align-items-center gap-2">
                                                        <span>📦</span>
                                                        <span>Returned to Warehouse</span>
                                                    </button>
                                                </form>
                                            
                                                <div class="dropdown-divider"></div>
                                            
                                                {{-- Claims --}}
                                                <form method="POST"
                                                      action="">
                                                      {{-- route('returns.claim', $item->id) --}}
                                                    @csrf
                                                    <button type="submit"
                                                            class="dropdown-item text-danger d-flex align-items-center gap-2">
                                                        <span>⚠️</span>
                                                        <span>Claims</span>
                                                    </button>
                                                </form>
                                                </div>
                                            </div>
                                        </td>
                                    {{-- Example: Status 5 (Invoiced) — Optional Actions --}}
                                    @elseif ($order->status_id == 5)
                                        <td class="text-center">
                                            <span class="badge bg-success">Invoiced</span>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No order items found
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                         @if ($order->status_id == 4)
                            <div style="height: 150px;"></div>
                        @endif
                    </div>
                </div>
            </div>
            <br>    
            @if($order->status_id == 5)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ __('Scan Items') }}
                    </h3>
                </div>
                <livewire:order-scanner :order="$order" />
            </div>
            @endif
        </div>
    </div>
@endsection
<script>
function submitWithRemarks(form, message) {
    const remarks = prompt(message);

    if (remarks === null || remarks.trim() === '') {
        alert('Remarks are required.');
        return false;
    }

    form.querySelector('input[name="remarks"]').value = remarks.trim();
    return true;
}
</script>

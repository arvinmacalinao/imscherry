<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Orders') }}
            </h3>
        </div>

        <div class="card-actions">
            <div class="dropdown">
                <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <x-icon.vertical-dots/>
                </a>
                <div class="dropdown-menu dropdown-menu-end" style="">
                    <a href="{{ route('orders.create') }}" class="dropdown-item">
                        <x-icon.plus/>
                        {{ __('Create Order') }}
                    </a>
                    <a href="{{ route('orders.import.view') }}" class="dropdown-item">
                        <x-icon.plus/>
                        {{ __('Import Orders') }}
                    </a>
                    <a href="" class="dropdown-item">
                        <x-icon.plus/>
                        {{ __('Export Orders') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body border-bottom py-3">
    <div class="d-flex flex-wrap gap-2">
        {{-- Status Filter --}}
            <div class="text-secondary d-flex me-3">
                Status: 
                <select wire:model.live="orderStatus" class="form-select form-select-sm">
                    <option value="">-- All Statuses --</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Shop Filter --}}
            <div class="text-secondary d-flex me-3">
                Shops: 
                <select wire:model.live="shopFilter" class="form-select form-select-sm">
                    <option value="">-- All Shops --</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Search --}}
            <div class="text-secondary me-3">
                Search:
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search"
                           class="form-control form-control-sm"
                           placeholder="Invoice / Customer / Order #">
                </div>
            </div>

            {{-- Date From --}}
            <div class="text-secondary d-flex me-3">
                <label class="small">From: </label>
                <input type="date"
                       wire:model.live="date_from"
                       class="form-control form-select-sm">
            </div>

            {{-- Date To --}}
            <div class="text-secondary d-flex me-3">
                <label class="small">To: </label>
                <input type="date"
                       wire:model.live="date_to"
                       class="form-control form-select-sm">
            </div>

            {{-- Clear Button --}}
            <div class="text-secondary d-flex align-items-end">
                <button class="btn btn-sm btn-outline-secondary"
                        wire:click="clearDateFilter">
                    Clear Dates
                </button>
            </div>

        </div>
    </div>



    <div class="card-body border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-wrap gap-2 my-2">
                <button class="btn btn-sm btn-outline-secondary"
                wire:click="printWarehouseSummarySelected"
                @disabled(count($selected) === 0)
                onclick="return confirm('Are you sure you want to print the order summary of these orders?')">
                Download Order Details ({{ count($selected) }})
                </button>

                <button class="btn btn-sm btn-outline-secondary"
                wire:click="printSummarySelected"
                @disabled(count($selected) === 0)
                onclick="return confirm('Are you sure you want to download order summary of these orders?')">
                Download Order Qty Summary  ({{ count($selected) }})
                </button>
            
                <button class="btn btn-sm btn-outline-secondary"
                    wire:click="printInvoiceSelected"
                    @disabled(count($selected) === 0)
                    onclick="return confirm('Are you sure you want to print the invoice of selected orders?')"
                >
                    Download Invoice ({{ count($selected) }})
                </button>

                {{-- <button 
                    class="btn btn-sm btn-outline-danger"
                    wire:click="deleteSelected"
                    @disabled(count($selected) === 0)
                    onclick="return confirm('Are you sure you want to delete the selected orders?')"
                >
                    Delete Orders ({{ count($selected) }})
                </button> --}}
            </div>

            <div class="text-secondary">
                Show
                <div class="mx-2 d-inline-block">
                    <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="result per page">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="25">50</option>
                    </select>
                </div>
                entries
            </div>
        </div>
    </div>


    <x-spinner.loading-spinner/>

    <div class="table-responsive">
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
                <tr>
                    <th class="align-middle text-center w-1">
                        <input type="checkbox" wire:model.live="selectAll">
                    </th>
                    <th class="align-middle text-center w-1">
                        {{ __('No.') }}
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('order_number')" href="#" role="button">
                            {{ __('Order No.') }}
                            @include('inclues._sort-icon', ['field' => 'order_number'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('invoice_no')" href="#" role="button">
                            {{ __('Invoice No.') }}
                            @include('inclues._sort-icon', ['field' => 'invoice_no'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('invoice_no')" href="#" role="button">
                            {{ __('Tracking No.') }}
                            @include('inclues._sort-icon', ['field' => 'invoice_no'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('customer_id')" href="#" role="button">
                            {{ __('Customer') }}
                            @include('inclues._sort-icon', ['field' => 'customer_id'])
                        </a>
                    </th>
                     <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('customer_id')" href="#" role="button">
                            {{ __('Shope Name') }}
                            @include('inclues._sort-icon', ['field' => 'customer_id'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('order_date')" href="#" role="button">
                            {{ __('Date') }}
                            @include('inclues._sort-icon', ['field' => 'order_date'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('total')" href="#" role="button">
                            {{ __('Total') }}
                            @include('inclues._sort-icon', ['field' => 'total'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('quantity')" href="#" role="button">
                            {{ __('No. of Items') }}
                            @include('inclues._sort-icon', ['field' => 'quantity'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('status_id')" href="#" role="button">
                            {{ __('Status') }}
                            @include('inclues._sort-icon', ['field' => 'status_id'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        {{ __('Action') }}
                    </th>
                </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr wire:key="order-{{ $order->id }}"
                    class="{{ $statusColors[$order->status_id] ?? '' }} '">
                    <td class="align-middle text-center" onclick="event.stopPropagation();">
                        <input type="checkbox" wire:model.live="selected" value="{{ $order->id }}" onclick="event.stopPropagation();">
                    </td>
                    <td class="align-middle text-center">
                        {{ $loop->iteration }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->order_number }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->invoice_no }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->tracking_number ?? '' }}
                    </td>
                    <td class="align-middle">
                        @php
                            $parts = explode(' ', trim($order->customer_name ?? ''));
                        @endphp
                        {{ $parts[0] }}{{ isset($parts[1]) ? ' ' . strtoupper(substr($parts[1], 0, 1)) . '.' : '' }}
                    </td>
                     <td class="align-middle">
                        {{ $order->shopName->invoice_prefix ?? '' }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->created_at->format('d-m-Y') }}
                    </td>
                    <td class="align-middle text-center">
                        {{ Number::currency($order->total, 'PHP') }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->total_quantity ?? '' }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->status->name ?? 'N/A' }}
                    </td>
                    <td class="align-middle text-center" style="width: 5%">
                            <x-button.show class="btn-icon" route="{{ route('orders.show', $order) }}"/>
                        @php
                        @endphp
                        @if(auth()->user()->hasRole('accounting'))
                            <x-button.print class="btn-icon" route="{{ route('order.downloadInvoice', $order->id) }}?download=1" target="_blank"/>
                        @endif
                        @if(is_null($order->tracking_number))
                            <button 
                                class="btn btn-warning btn-icon"
                                wire:click="$dispatch('openTrackingModal', { orderId: {{ $order->id }} })"
                                title="Add Tracking Number">
                                ➕
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="align-middle text-center" colspan="8">
                        No results found
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            Showing <span>{{ $orders->firstItem() }}</span> to <span>{{ $orders->lastItem() }}</span> of <span>{{ $orders->total() }}</span> entries
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $orders->links() }}
        </ul>
    </div>
    <livewire:order.add-tracking-modal />
</div>

@push('page-scripts')
<script>
document.addEventListener('livewire:init', () => {
    window.addEventListener('bulk-download-invoices', e => {
        const ids = e.detail.ids || [];
        console.log('📦 bulk-download-invoices event received', ids);

        if (!ids.length) {
            alert('No invoices selected.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('orders.downloadMultiple') }}";
        form.style.display = 'none';

        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf) {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf.getAttribute('content');
            form.appendChild(tokenInput);
        }

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endpush



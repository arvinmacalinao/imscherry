<div>
    @include('livewire.tables.report.partials.filters', [
        'show' => ['dates', 'shop', 'platform'],
        'dateLabel' => __('Cancelled on'),
        'extra' => 'livewire.tables.report.partials.picked-filter',
    ])

    @php($pickedTotal = $summary->sum('picked_qty'))
    @if ($pickedTotal > 0)
        <div class="alert alert-warning">
            {{ number_format($pickedTotal) }} unit(s) had already been picked for these cancelled orders.
            Cancelling does not put stock back, so check they were returned to the shelf.
        </div>
    @endif

    {{-- SUMMARY BY SHOP --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">{{ __('Cancellations by Shop') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered card-table table-vcenter text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Shop') }}</th>
                        <th class="text-center">{{ __('Cancelled Orders') }}</th>
                        <th class="text-end">{{ __('Order Value') }}</th>
                        <th class="text-center">{{ __('Units Picked') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td class="text-center">{{ number_format($row->orders_count) }}</td>
                            <td class="text-end">{{ Number::currency($row->total ?? 0, 'PHP') }}</td>
                            <td class="text-center">{{ number_format($row->picked_qty) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No cancelled orders found</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($summary->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th>{{ __('Grand Total') }}</th>
                            <th class="text-center">{{ number_format($summary->sum('orders_count')) }}</th>
                            <th class="text-end">{{ Number::currency($summary->sum('total'), 'PHP') }}</th>
                            <th class="text-center">{{ number_format($pickedTotal) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- CANCELLED ORDERS --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Cancellation Report') }}</h3>
        </div>

        @include('livewire.tables.report.partials.toolbar', ['placeholder' => 'Order, invoice, tracking, customer, reason'])

        <x-spinner.loading-spinner/>

        <div class="table-responsive">
            <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center w-1">{{ __('No.') }}</th>
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'event_at', 'title' => 'Cancelled On', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'order_number', 'title' => 'Order No.', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'order_date', 'title' => 'Order Date', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'shop_name', 'title' => 'Shop'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'items_qty', 'title' => 'Items', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'total', 'title' => 'Order Value', 'class' => 'text-end'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'picked_qty', 'title' => 'Picked', 'class' => 'text-center'])
                        <th>{{ __('Cancelled By') }}</th>
                        <th>{{ __('Reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr wire:key="cancel-{{ $order->id }}">
                            <td class="text-center">{{ $orders->firstItem() + $loop->index }}</td>
                            <td class="text-center">{{ $order->event_at ? \Carbon\Carbon::parse($order->event_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('orders.show', $order->id) }}">{{ $order->order_number }}</a>
                                <div class="text-secondary small">{{ $order->invoice_no }}</div>
                            </td>
                            <td class="text-center">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $order->shop_name ?? '-' }}</td>
                            <td class="text-center">{{ $order->items_qty }}</td>
                            <td class="text-end">{{ Number::currency($order->total ?? 0, 'PHP') }}</td>
                            <td class="text-center">
                                @if ($order->picked_qty > 0)
                                    <span class="badge bg-warning-lt" title="{{ __('Stock was picked before cancelling') }}">{{ $order->picked_qty }}</span>
                                @else
                                    <span class="text-secondary">0</span>
                                @endif
                            </td>
                            <td>{{ $order->event_by ?? '-' }}</td>
                            <td class="text-wrap" style="min-width: 200px">{{ $order->event_remarks ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No cancelled orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('livewire.tables.report.partials.footer', ['rows' => $orders])
    </div>
</div>

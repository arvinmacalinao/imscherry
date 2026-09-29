<div>
    @include('livewire.tables.report.partials.filters')

    @if ($missingPrice > 0)
        <div class="alert alert-warning">
            {{ number_format($missingPrice) }} order line(s) in this report have no unit price from the platform
            import, so they are counted as ₱0.00.
        </div>
    @endif

    {{-- SUMMARY BY GROUP --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">{{ __('Sales by') }} {{ $groupLabel }}</h3>
            <div class="card-actions">
                <div class="btn-group" role="group" aria-label="Group sales by">
                    @foreach (\App\Reports\SalesReport::GROUPS as $key => [$label])
                        <button type="button" wire:click="$set('groupBy', '{{ $key }}')"
                            @class(['btn btn-sm', 'btn-primary' => $groupBy === $key, 'btn-outline-primary' => $groupBy !== $key])>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered card-table table-vcenter text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>{{ $groupLabel }}</th>
                        <th class="text-center">{{ __('Orders') }}</th>
                        <th class="text-center">{{ __('Qty Sold') }}</th>
                        <th class="text-end">{{ __('Sales') }}</th>
                        <th class="text-end w-1">{{ __('Share') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td class="text-center">{{ number_format($row->orders_count) }}</td>
                            <td class="text-center">{{ number_format($row->qty) }}</td>
                            <td class="text-end">{{ Number::currency($row->total, 'PHP') }}</td>
                            <td class="text-end">
                                {{ $grandTotal > 0 ? number_format($row->total / $grandTotal * 100, 1) : '0.0' }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No sales found</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($summary->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th>{{ __('Grand Total') }}</th>
                            <th></th>
                            <th class="text-center">{{ number_format($grandQty) }}</th>
                            <th class="text-end">{{ Number::currency($grandTotal, 'PHP') }}</th>
                            <th class="text-end">100%</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ORDER LINES --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Sales Report (Products)') }}</h3>
        </div>

        @include('livewire.tables.report.partials.toolbar', ['placeholder' => 'Order, invoice, tracking, product, SKU'])

        <x-spinner.loading-spinner/>

        <div class="table-responsive">
            <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center w-1">{{ __('No.') }}</th>
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'order_date', 'title' => 'Date', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'order_number', 'title' => 'Order No.', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'invoice_no', 'title' => 'Invoice No.', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'shop_name', 'title' => 'Shop'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'item_name', 'title' => 'Product'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'category_name', 'title' => 'Category'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'quantity', 'title' => 'Qty', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'unit_price', 'title' => 'Unit Price', 'class' => 'text-end'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'line_total', 'title' => 'Total', 'class' => 'text-end'])
                        <th class="text-center">{{ __('Payment') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lines as $line)
                        <tr wire:key="sale-line-{{ $line->id }}">
                            <td class="text-center">{{ $lines->firstItem() + $loop->index }}</td>
                            <td class="text-center">{{ $line->order_date ? \Carbon\Carbon::parse($line->order_date)->format('d-m-Y') : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('orders.show', $line->order_id) }}">{{ $line->order_number }}</a>
                            </td>
                            <td class="text-center">{{ $line->invoice_no ?? '-' }}</td>
                            <td>{{ $line->shop_name ?? '-' }}</td>
                            <td>
                                {{ $line->item_name }}
                                <div class="text-secondary small">{{ $line->sku }}</div>
                            </td>
                            <td>
                                {{ $line->category_name }}
                                <div class="text-secondary small">{{ $line->brand }}</div>
                            </td>
                            <td class="text-center">{{ $line->quantity }}</td>
                            <td class="text-end">{{ Number::currency($line->unit_price ?? 0, 'PHP') }}</td>
                            <td class="text-end">{{ Number::currency($line->line_total, 'PHP') }}</td>
                            <td class="text-center">{{ $line->payment_type ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No results found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('livewire.tables.report.partials.footer', ['rows' => $lines])
    </div>
</div>

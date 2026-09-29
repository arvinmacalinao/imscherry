<div>
    @include('livewire.tables.report.partials.filters', [
        'show' => ['dates', 'brand', 'category', 'shop', 'platform'],
        'dateLabel' => __('Returned on'),
        'extra' => 'livewire.tables.report.partials.outcome-filter',
    ])

    @php
        $badge = [0 => 'bg-warning-lt', 9 => 'bg-green-lt', 10 => 'bg-azure-lt', 11 => 'bg-purple-lt', 12 => 'bg-red-lt'];
    @endphp

    {{-- SUMMARY BY OUTCOME --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">{{ __('Returned Items by Status') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered card-table table-vcenter text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Item Status') }}</th>
                        <th class="text-center">{{ __('Items') }}</th>
                        <th class="text-center">{{ __('Qty') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $row)
                        <tr>
                            <td>
                                <a href="#" wire:click.prevent="$set('outcome', '{{ $row->outcome }}')">
                                    <span class="badge {{ $badge[$row->outcome] ?? '' }}">{{ \App\Reports\ReturnReport::outcomeLabel($row->outcome) }}</span>
                                </a>
                            </td>
                            <td class="text-center">{{ number_format($row->items_count) }}</td>
                            <td class="text-center">{{ number_format($row->qty) }}</td>
                            <td class="text-end">{{ Number::currency($row->total, 'PHP') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No returned items found</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($summary->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th>{{ __('Grand Total') }}</th>
                            <th class="text-center">{{ number_format($summary->sum('items_count')) }}</th>
                            <th class="text-center">{{ number_format($summary->sum('qty')) }}</th>
                            <th class="text-end">{{ Number::currency($summary->sum('total'), 'PHP') }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- RETURNED ITEMS --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Returned Order Report') }}</h3>
        </div>

        @include('livewire.tables.report.partials.toolbar', ['placeholder' => 'Order, invoice, tracking, product, SKU'])

        <x-spinner.loading-spinner/>

        <div class="table-responsive">
            <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center w-1">{{ __('No.') }}</th>
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'returned_at', 'title' => 'Returned On', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'order_number', 'title' => 'Order No.', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'shop_name', 'title' => 'Shop'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'item_name', 'title' => 'Product'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'quantity', 'title' => 'Qty', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'line_total', 'title' => 'Amount', 'class' => 'text-end'])
                        <th>{{ __('Returned By') }}</th>
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'outcome', 'title' => 'Item Status'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'action_at', 'title' => 'Action On', 'class' => 'text-center'])
                        <th>{{ __('Action By') }}</th>
                        <th>{{ __('Remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lines as $line)
                        <tr wire:key="return-line-{{ $line->id }}">
                            <td class="text-center">{{ $lines->firstItem() + $loop->index }}</td>
                            <td class="text-center">{{ $line->returned_at ? \Carbon\Carbon::parse($line->returned_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('orders.show', $line->order_id) }}">{{ $line->order_number }}</a>
                                <div class="text-secondary small">{{ $line->invoice_no }}</div>
                            </td>
                            <td>{{ $line->shop_name ?? '-' }}</td>
                            <td>
                                {{ $line->item_name }}
                                <div class="text-secondary small">{{ $line->sku }}</div>
                            </td>
                            <td class="text-center">{{ $line->quantity }}</td>
                            <td class="text-end">{{ Number::currency($line->line_total, 'PHP') }}</td>
                            <td>{{ $line->returned_by ?? '-' }}</td>
                            <td><span class="badge {{ $badge[$line->outcome] ?? '' }}">{{ \App\Reports\ReturnReport::outcomeLabel($line->outcome) }}</span></td>
                            <td class="text-center">{{ $line->outcome && $line->action_at ? \Carbon\Carbon::parse($line->action_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td>{{ $line->outcome ? ($line->action_by ?? '-') : '-' }}</td>
                            <td class="text-wrap" style="min-width: 200px">{{ $line->outcome ? ($line->action_remarks ?? '-') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted">No returned items found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('livewire.tables.report.partials.footer', ['rows' => $lines])
    </div>
</div>

<div>
    @include('livewire.tables.report.partials.filters', ['show' => ['dates', 'brand', 'shop', 'platform']])

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ __('Sales by Brand and Category') }}</h3>
                <p class="card-subtitle">
                    {{ __('Sold = Packed/Shipped orders. Returned = returned orders, whatever happened to the items. Amounts use the platform price.') }}
                </p>
            </div>
        </div>

        <x-spinner.loading-spinner/>

        <div class="table-responsive">
            <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="2" class="align-middle">{{ __('Brand / Category') }}</th>
                        <th colspan="2" class="text-center">{{ __('Sold') }}</th>
                        <th colspan="2" class="text-center">{{ __('Returned') }}</th>
                        <th colspan="2" class="text-center">{{ __('Cancelled') }}</th>
                    </tr>
                    <tr>
                        @foreach (['sold', 'returned', 'cancelled'] as $group)
                            <th class="text-center">{{ __('Qty') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brandGroups as $brandName => $rows)
                        @php($subtotal = \App\Reports\CategoryReport::totals($rows))
                        <tr class="table-active fw-bold">
                            <td>{{ $brandName }}</td>
                            @foreach (['sold', 'returned', 'cancelled'] as $group)
                                <td class="text-center">{{ number_format($subtotal[$group . '_qty']) }}</td>
                                <td class="text-end">{{ Number::currency($subtotal[$group . '_amount'], 'PHP') }}</td>
                            @endforeach
                        </tr>
                        @foreach ($rows as $row)
                            <tr wire:key="category-{{ $brandName }}-{{ $row->category_id }}">
                                <td class="ps-4">
                                    @if ($row->category_id)
                                        <a href="{{ route('sales.report', array_filter(['category_id' => $row->category_id, 'date_from' => $date_from, 'date_to' => $date_to, 'shop_id' => $shop_id, 'platform_id' => $platform_id])) }}"
                                            title="{{ __('Open the sales lines of this category') }}">
                                            {{ $row->category }}
                                        </a>
                                    @else
                                        {{ $row->category }}
                                    @endif
                                </td>
                                @foreach (['sold', 'returned', 'cancelled'] as $group)
                                    <td class="text-center">{{ number_format($row->{$group . '_qty'}) }}</td>
                                    <td class="text-end">{{ Number::currency($row->{$group . '_amount'}, 'PHP') }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No results found</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($brandGroups->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th>{{ __('Grand Total') }}</th>
                            @foreach (['sold', 'returned', 'cancelled'] as $group)
                                <th class="text-center">{{ number_format($grand[$group . '_qty']) }}</th>
                                <th class="text-end">{{ Number::currency($grand[$group . '_amount'], 'PHP') }}</th>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

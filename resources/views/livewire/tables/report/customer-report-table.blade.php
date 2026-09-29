<div>
    @include('livewire.tables.report.partials.filters', ['show' => ['dates', 'shop', 'platform']])

    <div class="alert alert-info">
        {{ __('Platforms hide buyer names (e.g. "m****** b*****"), and orders are not linked to a customer record, so a customer here is a name within one shop. Different buyers with the same masked name are counted together.') }}
    </div>

    {{-- SUMMARY BY SHOP --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">{{ __('Buyers by Shop') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered card-table table-vcenter text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Shop') }}</th>
                        <th class="text-center">{{ __('Buyers') }}</th>
                        <th class="text-center">{{ __('Orders') }}</th>
                        <th class="text-end">{{ __('Order Value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td class="text-center">{{ number_format($row->buyers) }}</td>
                            <td class="text-center">{{ number_format($row->orders_count) }}</td>
                            <td class="text-end">{{ Number::currency($row->value ?? 0, 'PHP') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No orders found</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($summary->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th>{{ __('Grand Total') }}</th>
                            <th class="text-center">{{ number_format($summary->sum('buyers')) }}</th>
                            <th class="text-center">{{ number_format($summary->sum('orders_count')) }}</th>
                            <th class="text-end">{{ Number::currency($summary->sum('value'), 'PHP') }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- CUSTOMERS --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ __('Customer Report') }}</h3>
                <p class="card-subtitle">{{ __('Orders and value exclude cancelled orders.') }}</p>
            </div>
        </div>

        @include('livewire.tables.report.partials.toolbar', ['placeholder' => 'Name, phone, city'])

        <x-spinner.loading-spinner/>

        <div class="table-responsive">
            <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center w-1">{{ __('No.') }}</th>
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'customer', 'title' => 'Customer'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'shop_name', 'title' => 'Shop'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'orders_count', 'title' => 'Orders', 'class' => 'text-center'])
                        <th class="text-center">{{ __('Shipped') }}</th>
                        <th class="text-center">{{ __('Returned') }}</th>
                        <th class="text-center">{{ __('Cancelled') }}</th>
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'value', 'title' => 'Order Value', 'class' => 'text-end'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'first_order', 'title' => 'First Order', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'last_order', 'title' => 'Last Order', 'class' => 'text-center'])
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr wire:key="customer-{{ $loop->index }}-{{ md5($customer->customer . $customer->shop_name) }}">
                            <td class="text-center">{{ $customers->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ route('orders.index', ['search' => $customer->customer]) }}" title="{{ __('Find these orders') }}">{{ $customer->customer }}</a>
                                @if ($customer->city)
                                    <div class="text-secondary small">{{ $customer->city }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $customer->shop_name ?? '-' }}
                                <div class="text-secondary small">{{ $customer->platform_name }}</div>
                            </td>
                            <td class="text-center">{{ $customer->orders_count }}</td>
                            <td class="text-center">{{ $customer->shipped_count }}</td>
                            <td class="text-center">{{ $customer->returned_count }}</td>
                            <td class="text-center">{{ $customer->cancelled_count }}</td>
                            <td class="text-end">{{ Number::currency($customer->value ?? 0, 'PHP') }}</td>
                            <td class="text-center">{{ $customer->first_order ? \Carbon\Carbon::parse($customer->first_order)->format('d-m-Y') : '-' }}</td>
                            <td class="text-center">{{ $customer->last_order ? \Carbon\Carbon::parse($customer->last_order)->format('d-m-Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No results found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('livewire.tables.report.partials.footer', ['rows' => $customers])
    </div>
</div>

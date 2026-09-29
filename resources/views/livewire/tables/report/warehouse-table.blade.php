<div>
    @include('livewire.tables.report.partials.filters', [
        'show' => ['dates', 'brand', 'category'],
        'dateLabel' => __('Movement date'),
        'extra' => 'livewire.tables.report.partials.movement-filters',
    ])

    {{-- TOTALS PER MOVEMENT TYPE --}}
    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="text-secondary">{{ __('Total In') }}</div>
                    <div class="h2 mb-0 text-green">+{{ number_format($byType->sum('qty_in')) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="text-secondary">{{ __('Total Out') }}</div>
                    <div class="h2 mb-0 text-red">-{{ number_format($byType->sum('qty_out')) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="text-secondary mb-1">{{ __('By movement') }}</div>
                    @forelse ($byType as $row)
                        <span class="badge bg-secondary-lt me-1 mb-1">
                            {{ $row->movement }}:
                            @if ($row->qty_in > 0)<span class="text-green">+{{ number_format($row->qty_in) }}</span>@endif
                            @if ($row->qty_out > 0)<span class="text-red">-{{ number_format($row->qty_out) }}</span>@endif
                        </span>
                    @empty
                        <span class="text-secondary">{{ __('No movements') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- PER PRODUCT --}}
    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ __('Stock In / Out by Product') }}</h3>
                <p class="card-subtitle">{{ __('In and out for the selected dates; Stock Now is the current quantity.') }}</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered card-table table-vcenter text-nowrap">
                <thead class="thead-light">
                    <tr>
                        @foreach (['product' => ['Product', ''], 'qty_in' => ['In', 'text-center'], 'qty_out' => ['Out', 'text-center'], 'net' => ['Net', 'text-center'], 'stock' => ['Stock Now', 'text-center']] as $field => [$title, $class])
                            <th class="{{ $class }}">
                                <a wire:click.prevent="sortProductsBy('{{ $field }}')" href="#" role="button">
                                    {{ __($title) }}
                                    @if ($productSort === $field)
                                        @if ($productSortAsc) <x-icon.chevron-up/> @else <x-icon.chevron-down/> @endif
                                    @else
                                        <x-icon.selector/>
                                    @endif
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr wire:key="stock-product-{{ $product->product_id }}">
                            <td>
                                <a href="#" wire:click.prevent="$set('search', @js($product->sku ?: $product->product_name))" title="{{ __('Show only this product\'s movements') }}">
                                    {{ $product->product_name }}
                                </a>
                                <div class="text-secondary small">{{ $product->sku }} · {{ $product->category_name }}</div>
                            </td>
                            <td class="text-center text-green">{{ $product->qty_in > 0 ? '+' . number_format($product->qty_in) : '0' }}</td>
                            <td class="text-center text-red">{{ $product->qty_out > 0 ? '-' . number_format($product->qty_out) : '0' }}</td>
                            <td class="text-center">{{ $product->net > 0 ? '+' : '' }}{{ number_format($product->net) }}</td>
                            <td @class(['text-center fw-bold', 'text-red' => $product->stock <= 0])>{{ number_format($product->stock) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No stock movements found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('livewire.tables.report.partials.footer', ['rows' => $products])
    </div>

    {{-- EVERY MOVEMENT --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Stock Movements') }}</h3>
        </div>

        @include('livewire.tables.report.partials.toolbar', ['placeholder' => 'Product, SKU, order no., reference'])

        <x-spinner.loading-spinner/>

        <div class="table-responsive">
            <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center w-1">{{ __('No.') }}</th>
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'moved_at', 'title' => 'Date', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'product', 'title' => 'Product'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'movement', 'title' => 'Movement'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'qty_in', 'title' => 'In', 'class' => 'text-center'])
                        @include('livewire.tables.report.partials.sort-th', ['field' => 'qty_out', 'title' => 'Out', 'class' => 'text-center'])
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('By') }}</th>
                        <th>{{ __('Note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $move)
                        <tr wire:key="move-{{ $movements->firstItem() + $loop->index }}">
                            <td class="text-center">{{ $movements->firstItem() + $loop->index }}</td>
                            <td class="text-center">{{ $move->moved_at ? \Carbon\Carbon::parse($move->moved_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td>
                                {{ $move->product_name }}
                                <div class="text-secondary small">{{ $move->sku }} · {{ $move->category_name }}</div>
                            </td>
                            <td>{{ $move->movement }}</td>
                            <td class="text-center text-green">{{ $move->qty_in > 0 ? '+' . number_format($move->qty_in) : '' }}</td>
                            <td class="text-center text-red">{{ $move->qty_out > 0 ? '-' . number_format($move->qty_out) : '' }}</td>
                            <td>
                                @if ($move->order_id)
                                    <a href="{{ route('orders.show', $move->order_id) }}">{{ $move->reference }}</a>
                                @else
                                    {{ $move->reference ?? '-' }}
                                @endif
                            </td>
                            <td>{{ $move->user_name ?? '-' }}</td>
                            <td class="text-wrap" style="min-width: 160px">{{ $move->note ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No stock movements found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('livewire.tables.report.partials.footer', ['rows' => $movements])
    </div>
</div>

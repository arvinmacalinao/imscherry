{{-- Details of the last order import (set by OrderImportController::importFinished) --}}
@php($result = session('import_result'))
@if ($result && (count($result['duplicates']) || count($result['missing_orders']) || count($result['failed'])))
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">{{ $result['platform'] }} import details</h3>
        </div>
        <div class="card-body">
            @if (count($result['missing_skus']))
                <div class="mb-3">
                    <strong class="text-danger">SKUs not found in Products</strong>
                    <span class="text-secondary">- add these products (or fix the SKU), then import the file again:</span>
                    <div class="mt-1">
                        @foreach ($result['missing_skus'] as $sku)
                            <span class="badge bg-red-lt me-1 mb-1">{{ $sku }}</span>
                        @endforeach
                    </div>
                    <div class="text-secondary small mt-1">
                        Orders skipped because of them ({{ count($result['missing_orders']) }}):
                        {{ implode(', ', $result['missing_orders']) }}
                    </div>
                </div>
            @endif

            @if (count($result['failed']))
                <div class="mb-3">
                    <strong class="text-danger">Orders that failed ({{ count($result['failed']) }})</strong>
                    <span class="text-secondary">- nothing was saved for these; the other orders were imported:</span>
                    <ul class="mb-0 mt-1">
                        @foreach ($result['failed'] as $orderNumber => $error)
                            <li><strong>{{ $orderNumber }}</strong>: <span class="text-secondary">{{ \Illuminate\Support\Str::limit($error, 200) }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (count($result['duplicates']))
                <div>
                    <strong>Already imported ({{ count($result['duplicates']) }})</strong>
                    <span class="text-secondary">- skipped, nothing changed:</span>
                    <div class="text-secondary small mt-1">{{ implode(', ', $result['duplicates']) }}</div>
                </div>
            @endif
        </div>
    </div>
@endif

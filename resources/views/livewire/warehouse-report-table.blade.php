<div class="card">
    <div class="card-header">
        <h3 class="card-title">Warehouse Report</h3>
        <div class="card-actions">
            <div class="dropdown">
                <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <x-icon.vertical-dots />
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a href="" class="dropdown-item">
                        {{-- <x-icon.export /> --}}
                        {{ __('Export Report') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="d-flex mb-3">
            <div class="ms-auto text-secondary">
                Search:
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search" class="form-control form-control-sm" placeholder="Search Product Name/SKU/ID" aria-label="Search warehouse report">
                </div>
            </div>
        </div>

        {{-- Report Table --}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Quantity Pulled</th>
                        <th>Employee</th>
                        <th>Pull Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productPulls as $index => $productPull)
                        <tr>
                            <td>{{ $productPulls->firstItem() + $index }}</td>
                            <td>{{ $productPull->product->name }}</td>
                            <td>{{ $productPull->product->sku }}</td>
                            <td>{{ $productPull->product->category->name }}</td>
                            <td>{{ $productPull->quantity }}</td>
                            <td>{{ $productPull->employee->name }}</td>
                            <td>{{ $productPull->pulled_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No pulls recorded in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">
                Showing <span>{{ $productPulls->firstItem() }}</span> to <span>{{ $productPulls->lastItem() }}</span> of <span>{{ $productPulls->total() }}</span> entries
            </p>
            <ul class="pagination ms-auto m-0">
                {{ $productPulls->links() }}
            </ul>
        </div>
    </div>
</div>

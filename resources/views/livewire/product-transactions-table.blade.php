<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Product Transactions') }}
            </h3>
        </div>
        <div class="card-actions">
            <div class="dropdown">
                <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <x-icon.vertical-dots/>
                </a>
                <div class="dropdown-menu dropdown-menu-end" style="">
                    <a href="{{ route('transactions.create') }}" class="dropdown-item">
                        <x-icon.plus/>
                        {{ __('Create Transaction') }}
                    </a>
                    <a href="{{ route('orders.import.view') }}" class="dropdown-item">
                        <x-icon.plus/>
                        {{ __('Download Report') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body btransaction-bottom py-3">
        <div class="card-actions btn-group">
                    <input wire:model.live="search" 
                        type="text" 
                        class="form-control w-auto" 
                        placeholder="Search product name...">

                    <select wire:model.live="type" class="form-select w-auto">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type->name }}">{{ ucfirst($type->name) }}</option>
                        @endforeach
                    </select>
                
                    <input wire:model.live="date_from" 
                        type="date" 
                        class="form-control w-auto">
                
                    <input wire:model.live="date_to" 
                        type="date" 
                        class="form-control w-auto">
        </div>
    </div>
    <div class="card-body border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
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
                        {{ __('No.') }}
                    </th>
                    <th class="align-middle text-center w-1">
                        {{ __('Transaction Date') }}
                    </th>
                    <th class="align-middle text-center w-1">
                        {{ __('Type') }}
                    </th>
                    <th class="align-middle text-center w-1">
                        {{ __('Items') }}
                    </th>
                    <th scope="col" class="align-middle text-center">
                        {{ __('Action') }}
                    </th>
                </tr>
            </thead>
            <tbody>
            @forelse ($transactions as $transaction)
                <tr wire:key="transaction-{{ $transaction->id }}">
                    <td class="align-middle text-center">
                        {{ $loop->iteration }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $transaction->transaction_date }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $transaction->type->name ?? '' }}
                    </td>
                    <td class="align-middle text-center">
                         @php
                            // Get all product names in this batch
                            $names = $transaction->items->pluck('product.name')->toArray();

                            // Limit to first 5
                            $displayNames = count($names) > 5 
                                ? implode(', ', array_slice($names, 0, 5)) . ' ...'
                                : implode(', ', $names);
                        @endphp

                        {{ $displayNames }}
                    </td>
                    <td class="align-middle text-center" style="width: 5%">
                        <x-button.show class="btn-icon" route="{{ route('transactions.show', $transaction) }}"/>
                        {{-- <x-button.print class="btn-icon" route=""/>
                        <a href="" class="btn btn-warning btn-icon">
                            <i class="fas fa-ban"></i>
                        </a> --}}
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
            Showing <span>{{ $transactions->firstItem() }}</span> to <span>{{ $transactions->lastItem() }}</span> of <span>{{ $transactions->total() }}</span> entries
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $transactions->links() }}
        </ul>
    </div>
</div>

@push('page-scripts')

@endpush



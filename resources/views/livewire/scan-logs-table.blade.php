<div>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">My Scanned Items</h3>

            <div>
                <input type="text" wire:model.debounce.300ms="search"
                    class="form-control" style="width: 250px;"
                    placeholder="Search by Order No...">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered table-vcenter">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Latest Status</th>
                        <th>Last Scanned At</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($logs as $log)
                        @php
                            $order = $log->order;
                        @endphp

                        <tr>
                            <td>#{{ $order->order_number }}</td>

                            <td>
                                <span class="badge text-light bg-primary">
                                    {{ $log->toStatus->name ?? '-' }}
                                </span>
                            </td>

                            <td>{{ $log->created_at->format('d-m-Y h:i A') }}</td>

                            <td>
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-primary">
                                    View Order
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted p-4">
                                You haven't scanned any orders yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
</div>

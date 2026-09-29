<?php

namespace App\Livewire\Tables;

use App\Models\Order;
use Livewire\Component;
use App\Models\ShopName;
use App\Models\OrderStatus;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Exports\OrderSummaryExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderSummaryExportWarehouse;
use Illuminate\Database\Eloquent\Builder;

class OrderTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    #[Url(except: '')]
    public $search = '';
    public $sortField = 'invoice_no';
    public $sortAsc = false;
    public $orderStatus = '';
    public $statuses = [];
    public $shopFilter = '';
    public $shops = [];
    public $selected = [];
    public $selectAll = false;
    public $date_from;
    public $date_to;

    /** Columns the table may be sorted by (anything else falls back to invoice no.) */
    private const SORTABLE = [
        'order_number', 'invoice_no', 'tracking_number', 'customer_name',
        'shop_name_id', 'order_date', 'total', 'status_id',
    ];

    /** Changing any of these filters starts again on page 1 and clears the ticked orders */
    private const FILTERS = ['search', 'orderStatus', 'shopFilter', 'date_from', 'date_to', 'perPage'];

    public $statusColors = [
        1 => 'table-info',      // Imported
        2 => 'table-info',      // QC Done
        3 => 'table-success',   // Packed/Shipped
        4 => 'table-danger',    // Returned
        5 => 'table-info',      // Invoiced
        6 => 'table-danger',    // Cancelled
        7 => 'table-warning',   // Pending
        8 => 'table-info',      // Picked
    ];

    /**
     * Statuses the current user's role may see; null = all, [] = none (user without a role).
     */
    private function allowedStatuses(): ?array
    {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('ecommerce-assistant')) {
            return null; // see everything
        }
        if ($user->hasRole('accounting')) {
            return [1, 5]; // Imported + Invoiced
        }
        if ($user->hasRole('warehouse')) {
            return [5, 8, 7];
        }
        if ($user->hasRole('qc')) {
            return [8, 2];
        }
        if ($user->hasRole('packer')) {
            return [2, 3];
        }

        return []; // no role yet: no orders until an admin assigns one
    }

    /**
     * The orders the table shows: role restriction + every filter + search.
     * "Select all" uses the same query, so it ticks exactly the orders on screen.
     */
    private function ordersQuery(): Builder
    {
        $allowedStatuses = $this->allowedStatuses();

        return Order::query()
            // 🔒 ROLE-BASED RESTRICTION
            ->when($allowedStatuses !== null, fn ($q) => $q->whereIn('status_id', $allowedStatuses))
            // 🎯 UI status filter (within the allowed statuses)
            ->when($this->orderStatus, fn ($q) => $q->where('status_id', $this->orderStatus))
            ->when($this->shopFilter, fn ($q) => $q->where('shop_name_id', $this->shopFilter))
            // each date works on its own; both = a range
            ->when($this->date_from, fn ($q) => $q->whereDate('order_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('order_date', '<=', $this->date_to))
            ->search($this->search)
            ->orderBy(in_array($this->sortField, self::SORTABLE, true) ? $this->sortField : 'invoice_no', $this->sortAsc ? 'asc' : 'desc')
            ->orderByDesc('id');
    }

    public function updatedSelectAll($value)
    {
        $this->selected = $value
            ? $this->ordersQuery()
                ->paginate($this->perPage)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray()
            : [];
    }

    public function mount()
    {
        // only offer the statuses this role can actually see, in workflow order
        $allowed = $this->allowedStatuses();
        $this->statuses = OrderStatus::when($allowed !== null, fn ($q) => $q->whereIn('id', $allowed))->orderBy('id')->get();
        $this->shops = ShopName::orderBy('name')->get();
    }

    public function updated($field)
    {
        if (in_array($field, self::FILTERS, true)) {
            $this->resetPage();
            $this->reset(['selected', 'selectAll']);
        }
    }

    // a new page shows different orders: "select all" no longer applies
    public function updatedPage()
    {
        $this->selectAll = false;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'orderStatus', 'shopFilter', 'date_from', 'date_to', 'selected', 'selectAll']);
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = ! $this->sortAsc;

        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function printInvoiceSelected()
    {
        if (empty($this->selected)) {
            $this->dispatch('notify', type: 'error', message: 'No orders selected.');
            return;
        }

        // send selected order IDs to the browser
        $this->dispatch('bulk-download-invoices', ids: $this->selected);
    }


    public function printSummarySelected()
    {
        if (empty($this->selected)) {
            $this->dispatch('notify', type: 'error', message: 'No orders selected.');
            return;
        }

        $orders = Order::with('details.product')
            ->whereIn('id', $this->selected)
            ->get();

        if ($orders->isEmpty()) {
            $this->dispatch('notify', type: 'error', message: 'No valid orders found.');
            return;
        }

        // Direct download, no storage
        return Excel::download(
            new OrderSummaryExport($orders),
            'order_summary_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function printWarehouseSummarySelected()
    {
        if (empty($this->selected)) {
            $this->dispatch('notify', type: 'error', message: 'No orders selected.');
            return;
        }

        $orders = Order::with('details.product')
            ->whereIn('id', $this->selected)
            ->get();

        if ($orders->isEmpty()) {
            $this->dispatch('notify', type: 'error', message: 'No valid orders found.');
            return;
        }

        // Direct download, no storage
        return Excel::download(
            new OrderSummaryExportWarehouse($orders),
            'order_summary_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.tables.order-table', [
            'orders' => $this->ordersQuery()
                ->with(['status', 'details', 'shopName'])
                ->paginate($this->perPage),
        ]);
    }

    //WORKING BEFORE USER ROLES
    // public function render()
    // {
    //     \Log::info('Filtering by status ID: ' . $this->orderStatus);

    //     $orders = Order::query()
    //         ->with(['status', 'customer', 'details', 'shopName'])
    //         ->when($this->orderStatus, function ($query) {
    //             $query->where('status_id', $this->orderStatus);
    //         })
    //         ->when($this->shopFilter, fn($q) =>
    //                 $q->where('shop_name_id', $this->shopFilter)
    //             )

    //         ->when($this->date_from && $this->date_to, function ($query) {
    //         $query->whereBetween('order_date', [
    //             $this->date_from . ' 00:00:00',
    //                 $this->date_to . ' 23:59:59'
    //             ]);
    //         })

    //         ->search($this->search)
    //         ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
    //         ->paginate($this->perPage);

    //     return view('livewire.tables.order-table', [
    //         'orders' => $orders,
    //     ]);
    // }



    // public function render()
    // {
    //     return view('livewire.tables.order-table', [
    //         'orders' => Order::query()
    //             ->with(['status', 'customer', 'details'])
    //             ->search($this->search)
    //             ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
    //             ->paginate($this->perPage),
    //     ]);
    // }
}

<?php

namespace App\Livewire\Tables;

use Log;
use App\Models\Order;
use Livewire\Component;
use App\Models\ShopName;
use App\Models\OrderStatus;
use Livewire\WithPagination;
use App\Exports\OrderSummaryExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\OrderSummaryExportWarehouse;

class OrderTable extends Component
{
    use WithPagination;

    public $perPage = 10;
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

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = Order::query()
                ->when($this->orderStatus, function ($query) {
                    $query->where('status_id', $this->orderStatus);
                })
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage)
                ->pluck('id')
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = false; // if user toggles individually, disable "select all"
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            session()->flash('error', 'No orders selected.');
            return;
        }

        Order::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('success', 'Selected orders deleted successfully.');

        $this->resetPage();
    }

    public function mount()
    {
        $this->statuses = OrderStatus::orderBy('name')->get();
        $this->shops = ShopName::orderBy('name')->get();
        
    }   

    public function updatedOrderStatus()
    {
        $this->resetPage();
    }

    public function updatedShopFilter()
    {
        $this->resetPage();
    }

    public function updated($field)
    {
        if (in_array($field, ['date_from', 'date_to'])) {
            $this->resetPage();
        }
    }

    public function clearDateFilter()
    {
        $this->date_from = null;
        $this->date_to = null;
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
        // Log::info('printInvoiceSelected called', ['selected' => $this->selected]);
    
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
        \Log::info('Filtering by status ID: ' . $this->orderStatus);
    
        $user = auth()->user();
    
        // ✅ Default = all statuses
        $allowedStatuses = null;
    
        // Accounting restriction
        if ($user->hasRole('admin') || $user->hasRole('ecom')) {
        $allowedStatuses = null; // see everything
        }
        else if ($user->hasRole('accounting')) {
            $allowedStatuses = [1, 5]; // Imported + Invoiced
        }
        else if($user->hasRole('warehouse')){
            $allowedStatuses = [5, 8];
        }
        else if($user->hasRole('qc')){
            $allowedStatuses = [8, 2];
        }
        else if($user->hasRole('Packer')){
            $allowedStatuses = [2, 3];
        }
    
        $orders = Order::query()
            ->with(['status', 'customer', 'details', 'shopName'])
    
            // 🔒 ROLE-BASED RESTRICTION
            ->when($allowedStatuses, function ($query) use ($allowedStatuses) {
                $query->whereIn('status_id', $allowedStatuses);
            })
    
            // 🎯 UI status filter (still works but within allowed)
            ->when($this->orderStatus, function ($query) {
                $query->where('status_id', $this->orderStatus);
            })
    
            ->when($this->shopFilter, fn($q) =>
                $q->where('shop_name_id', $this->shopFilter)
            )
    
            ->when($this->date_from && $this->date_to, function ($query) {
                $query->whereBetween('order_date', [
                    $this->date_from . ' 00:00:00',
                    $this->date_to . ' 23:59:59'
                ]);
            })
    
            ->search($this->search)
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);
    
        return view('livewire.tables.order-table', [
            'orders' => $orders,
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

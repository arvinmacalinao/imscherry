<?php

namespace App\Livewire\Tables\Report;

use App\Models\Order;
use Livewire\Component;
use App\Models\ShopName;
use App\Models\OrderStatus;
use Livewire\WithPagination;

class CategoriesTable extends Component
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



    public function render()
    {
        $orders = Order::query()
            ->with(['status', 'customer', 'details', 'shopName'])
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

        return view('livewire.tables.report.categories-table', [
            'orders' => $orders,
        ]);
    }
}

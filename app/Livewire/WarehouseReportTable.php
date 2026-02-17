<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Product;
use Livewire\Component;
use App\Models\ProductPull;
use Livewire\WithPagination;

class WarehouseReportTable extends Component
{
    use WithPagination;

    public $perPage = 25;

    public $search = '';

    public $sortField = 'id';

    public $sortAsc = false;

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
        return view('livewire.tables.warehouse-table', [
            'products' => Product::query()
                ->with(['category'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}


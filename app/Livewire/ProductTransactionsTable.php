<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\ProductTransactionBatch;
use App\Models\ProductTransactionType;
use Livewire\Component;
use Livewire\WithPagination;

class ProductTransactionsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';
    public $product_id = '';
    public $date_from = '';
    public $date_to = '';
    public $products;


    public function updating($field)
    {
        $this->resetPage(); // reset page on filter change
    }

     public function mount()
    {
        $this->products = Product::get();
    }   

    public function render()
    {
        $query = ProductTransactionBatch::with('items.product', 'user_created')->with('type')->latest();

        $types = ProductTransactionType::all();

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

       if ($this->type) {
            $query->whereHas('type', function ($q) {
                $q->where('name', $this->type);
            });
        }


        if ($this->product_id) {
            $query->where('product_id', $this->product_id);
        }

        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        return view('livewire.product-transactions-table', [
            'transactions' => $query->paginate(20),
            'types' => $types,
        ]);
    }
}

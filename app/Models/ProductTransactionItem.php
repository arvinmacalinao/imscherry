<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductTransactionBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'product_id',
        'quantity_change',
        'before_quantity',
        'after_quantity',
    ];

    // -----------------------------
    // Relationships
    // -----------------------------

    public function batch()
    {
        return $this->belongsTo(ProductTransactionBatch::class, 'batch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

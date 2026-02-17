<?php

namespace App\Models;

use App\Models\ProductTransactionBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductTransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // -----------------------------
    // Relationships
    // -----------------------------

    public function batches()
    {
        return $this->hasMany(ProductTransactionBatch::class, 'transaction_type_id');
    }

    // -----------------------------
    // Optional helpers
    // -----------------------------
    public function scopeName($query, $name)
    {
        return $query->where('name', $name);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity_change',
        'before_quantity',
        'after_quantity',
        'note',
        'created_by',
        'updated_by'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

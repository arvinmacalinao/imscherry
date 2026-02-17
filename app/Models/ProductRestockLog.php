<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductRestockLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $fillable = [
        'id', 'product_id', 'old_quantity', 'added_quantity', 'new_quantity', 'user_id', 'created_at', 'updated_at'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

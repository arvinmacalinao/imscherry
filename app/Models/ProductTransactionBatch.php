<?php

namespace App\Models;

use App\Models\User;
use App\Models\ProductTransactionItem;
use App\Models\ProductTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductTransactionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type_id',
        'note',
        'created_by',
        'transaction_date',
        'reference_no'
    ];

    // -----------------------------
    // Relationships
    // -----------------------------

    public function type()
    {
        return $this->belongsTo(ProductTransactionType::class, 'transaction_type_id');
    }

    public function items()
    {
        return $this->hasMany(ProductTransactionItem::class, 'batch_id');
    }

    public function user_created()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user_updated()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    // -----------------------------
    // Automatically fill created_by / updated_by
    // -----------------------------
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}

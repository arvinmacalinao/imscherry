<?php

namespace App\Models;

use App\Models\OrderStatus;
use App\Models\OrderDetailsStatusLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetails extends Model
{
    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'id', 'order_id', 'product_id', 'product_name', 'sku', 'quantity', 'unit_price', 'created_at', 'updated_at', 'deleted_at', 'scanned_qty', 'tracking_number', 'status_id', 'remarks'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
         'qty' => 'integer',
    'scanned_qty' => 'integer',
    ];

    protected $with = ['product'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function detailsstatusLogs()
    {
        return $this->hasMany(OrderDetailsStatusLog::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }
}


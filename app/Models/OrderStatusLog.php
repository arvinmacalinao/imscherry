<?php

namespace App\Models;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status_id',
        'acted_by',
        'acted_at',
        'remarks',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}

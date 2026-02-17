<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScanLog extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'order_id', 'user_id', 'from_status_id', 'to_status_id', 'created_at', 'updated_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'to_status_id');
    }
}

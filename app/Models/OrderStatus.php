<?php

namespace App\Models;

use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $table = 'order_statuses';

	protected $fillable = [
		'name',
	];

	public function order()
	{
	    return $this->hasMany(Order::class);
	}

	public function logs()
	{
	    return $this->hasMany(OrderStatusLog::class, 'status_id');
	}

	
}

<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}

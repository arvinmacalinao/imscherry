<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopName extends Model
{
    use HasFactory;

    protected $table = 'shop_names';

    protected $fillable = [
        'name',
        'platform_id',
        'invoice_prefix'
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'shop_name_id');
    }
}

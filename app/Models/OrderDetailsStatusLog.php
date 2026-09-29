<?php

namespace App\Models;

use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailsStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_details_id',
        'status_id',
        'acted_by',
        'acted_at',
        'remarks',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    /**
     * Always set the time from the app (Philippine time), never from the database's
     * CURRENT_TIMESTAMP default, which uses the database server's own timezone.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->acted_at ??= now();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function orderdetails()
    {
        return $this->belongsTo(OrderDetails::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}

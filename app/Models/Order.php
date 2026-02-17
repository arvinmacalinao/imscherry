<?php

namespace App\Models;

use App\Models\ScanLog;
use App\Models\Customer;
use App\Models\Platform;
use App\Models\ShopName;
use App\Models\OrderStatus;
use App\Models\OrderDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
      'order_number', 'shop_name_id', 'invoice_no', 'order_date', 'total_products', 'sub_total', 'discount', 'shipping_fee', 'vat', 'total', 'platform_id', 'status_id', 'payment_type', 'customer_name', 'customer_phone', 'customer_email', 'shipping_address', 'shipping_city', 'shipping_postcode', 'shipping_country', 'tracking_number', 'courier', 'platform_created_at', 'platform_updated_at', 'created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at', 'remarks'
    ];
    // add remarks

    protected $casts = [
        'order_date'    => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
        
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function scopeSearch($query, $value): void
    {
        if (! $value) return;

        $query->where(function ($q) use ($value) {
            $q->where('invoice_no', 'like', "%{$value}%")
              ->orWhere('status_id', 'like', "%{$value}%")
              ->orWhere('customer_name', 'like', "%{$value}%")
              ->orWhere('created_at', 'like', "%{$value}%")
              ->orWhere('order_number', 'like', "%{$value}%")

              // 🔍 Search by SHOP NAME (shop_names table)
              ->orWhereHas('shopName', function ($qs) use ($value) {
                  $qs->where('name', 'like', "%{$value}%");
              })

              // 🔍 Search by PRODUCT inside order_details table
              ->orWhereHas('details.product', function ($qp) use ($value) {
                  $qp->where('name', 'like', "%{$value}%")
                     ->orWhere('sku', 'like', "%{$value}%");
              });
        });
    }
    
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'status_id', 'id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function shopName()
    {
        return $this->belongsTo(ShopName::class, 'shop_name_id');
    }

    public function scanLogs()
    {
        return $this->hasMany(ScanLog::class);
    }
}

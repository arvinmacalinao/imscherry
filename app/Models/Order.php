<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductRestockLog;
use App\Models\ScanLog;
use App\Models\ShopName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    /**
     * No tracking number is always NULL, never '' (importers read empty cells as ''),
     * so "has no tracking yet" checks and the Add Tracking button work for every platform.
     */
    public function setTrackingNumberAttribute($value): void
    {
        $value = is_string($value) ? trim($value) : $value;

        $this->attributes['tracking_number'] = ($value === '' || $value === null) ? null : $value;
    }

    /** Statuses at which the goods are still in the warehouse (not shipped or returned) */
    public const IN_WAREHOUSE_STATUSES = [1, 2, 5, 7, 8]; // Imported, QC Done, Invoiced, Pending, Picked

    /**
     * Put stock that was picked for this order back on the shelf (used when an order is
     * cancelled or set to Pending before it ships). Resets the picked count so a later
     * pick deducts again, and logs each line for the warehouse report. Call inside a transaction.
     *
     * @return int units put back
     */
    public function restockPickedItems(): int
    {
        $units = 0;

        foreach ($this->details()->where('scanned_qty', '>', 0)->lockForUpdate()->get() as $line) {
            $product = Product::withTrashed()->whereKey($line->product_id)->lockForUpdate()->first();

            if ($product) {
                $old = (int) $product->quantity;
                $product->increment('quantity', $line->scanned_qty);
                ProductRestockLog::record($product, $old, $old + $line->scanned_qty, 'cancel', $this->id);
            }

            $units += $line->scanned_qty;
            $line->update(['scanned_qty' => 0]);
        }

        return $units;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function orderDetails(): HasMany
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

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function currentStatus()
    {
        return $this->belongsTo(OrderStatusLog::class, 'status_id');
    }

    public function importLog()
    {
        return $this->hasOne(OrderStatusLog::class)
            ->where('status_id', 1)
            ->latest();
    }

    public function pickedLog()
    {
        return $this->hasOne(OrderStatusLog::class)
            ->where('status_id', 8)
            ->latest();
    }

    public function qcLog()
    {
        return $this->hasOne(OrderStatusLog::class)
            ->where('status_id', 2)
            ->latest();
    }

    public function packshipLog()
    {
        return $this->hasOne(OrderStatusLog::class)
            ->where('status_id', 3)
            ->latest();
    }

    public function invoicedLog()
    {
        return $this->hasOne(OrderStatusLog::class)
            ->where('status_id', 5)
            ->latest();
    }

    public function getTotalQuantityAttribute()
    {
       return $this->details->sum('quantity');
    }
}

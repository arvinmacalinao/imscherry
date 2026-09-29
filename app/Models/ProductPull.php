<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPull extends Model
{
    use HasFactory;

    protected $table = 'product_pulls';

    protected $fillable = [
        'product_id',
        'order_id',    // set when the pull is order picking; empty for the warehouse pull page
        'employee_id',
        'quantity',    
        'pulled_at',   
        'status',      
    ];

    
    protected $casts = [
        'pulled_at' => 'datetime', 
    ];

    /**
     * Always set the time from the app (Philippine time), never from the database's
     * CURRENT_TIMESTAMP default, which uses the database server's own timezone.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->pulled_at ??= now();
        });
    }

    

    /**
     * A product pull belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * A product pull belongs to an employee (User).
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    
    
    /**
     * Scope to filter pulls by product ID.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter pulls by employee ID.
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope to filter pulls within a certain time range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('pulled_at', [$startDate, $endDate]);
    }
}

<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPull extends Model
{
    use HasFactory;

    // Define the table if it's not automatically inferred
    protected $table = 'product_pulls';

    // Fillable attributes to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'product_id',  // The product being pulled
        'employee_id', // The employee pulling the product
        'quantity',    // The quantity of the product pulled
        'pulled_at',   // The date and time the product was pulled
        'status',      // The status of the pull, such as 'completed', 'pending', etc.
    ];

    // Casting to handle data types properly
    protected $casts = [
        'pulled_at' => 'datetime', // Ensure pulled_at is treated as a date/time
    ];

    // Relationships

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
    
    // Scopes (Optional)
    
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PowerComponents\LivewirePowerGrid\Concerns\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'platform_id', 'platform_customer_id', 'name', 'email', 'phone', 'address', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function quotations(): HasMany
    {
        return $this->HasMany(Quotation::class);
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone', 'like', "%{$value}%");
    }
}

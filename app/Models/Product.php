<?php

namespace App\Models;

use App\Enums\TaxType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use Softdeletes;

    protected $guarded = ['id'];

    public $fillable = [
        'name',
        'sku',
        'quantity',
        'price',
        'category_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
        
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // public function getRouteKeyName(): string
    // {
    //     return 'slug';
    // }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // public function unit(): BelongsTo
    // {
    //     return $this->belongsTo(Unit::class);
    // }

    // protected function buyingPrice(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => $value / 100,
    //         set: fn ($value) => $value * 100,
    //     );
    // }

    // protected function sellingPrice(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => $value / 100,
    //         set: fn ($value) => $value * 100,
    //     );
    // }

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('sku', 'like', "%{$value}%");
    }

    public function transactions()
    {
        return $this->hasMany(ProductTransaction::class);
    }

}

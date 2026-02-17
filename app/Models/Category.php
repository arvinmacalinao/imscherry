<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'name',
        'slug',
        'short_code',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('slug', 'like', "%{$value}%");
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

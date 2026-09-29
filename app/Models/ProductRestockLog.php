<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A direct change of a product's quantity. source = 'import' (product Excel import),
 * 'manual' (product create / edit form) or 'cancel' (picked stock put back when an order
 * was cancelled or set to Pending; order_id says which). Shown in the warehouse stock report.
 */
class ProductRestockLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $fillable = [
        'id', 'product_id', 'source', 'order_id', 'old_quantity', 'added_quantity', 'new_quantity', 'user_id', 'created_at', 'updated_at'
    ];

    /** Log a quantity change from $old to $new; nothing is logged when it did not change */
    public static function record(Product $product, int $old, int $new, string $source, ?int $orderId = null): void
    {
        if ($old === $new) {
            return;
        }

        static::create([
            'product_id'     => $product->id,
            'source'         => $source,
            'order_id'       => $orderId,
            'old_quantity'   => $old,
            'added_quantity' => $new - $old,
            'new_quantity'   => $new,
            'user_id'        => auth()->id(),
        ]);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

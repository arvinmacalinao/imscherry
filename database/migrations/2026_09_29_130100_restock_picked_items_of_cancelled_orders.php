<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Before cancelling / setting Pending restocked automatically, stock picked for those orders
     * was lost. Put it back once for the orders that are Cancelled (6) or Pending (7) now,
     * the same way Order::restockPickedItems() does from here on.
     */
    public function up(): void
    {
        $lines = DB::table('order_details AS d')
            ->join('orders AS o', 'o.id', '=', 'd.order_id')
            ->whereIn('o.status_id', [6, 7])
            ->where('d.scanned_qty', '>', 0)
            ->whereNull('o.deleted_at')
            ->select('d.id', 'd.order_id', 'd.product_id', 'd.scanned_qty')
            ->get();

        foreach ($lines as $line) {
            DB::transaction(function () use ($line) {
                $old = (int) DB::table('products')->where('id', $line->product_id)->lockForUpdate()->value('quantity');

                DB::table('products')->where('id', $line->product_id)->increment('quantity', $line->scanned_qty);

                DB::table('product_restock_logs')->insert([
                    'product_id'     => $line->product_id,
                    'source'         => 'cancel',
                    'order_id'       => $line->order_id,
                    'old_quantity'   => $old,
                    'added_quantity' => $line->scanned_qty,
                    'new_quantity'   => $old + $line->scanned_qty,
                    'user_id'        => null,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                DB::table('order_details')->where('id', $line->id)->update(['scanned_qty' => 0]);
            });
        }
    }

    public function down(): void
    {
        // stock movements are not reversed automatically
    }
};

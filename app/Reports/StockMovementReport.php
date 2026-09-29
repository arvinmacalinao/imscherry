<?php

namespace App\Reports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Warehouse stock in / out, built from every place the system records a stock change:
 *
 *   product_restock_logs       IN/OUT  product Excel import (restock), manual quantity edits
 *   product_transaction_items  IN/OUT  stock batches (transfer in/out, PO, audit in/out)
 *   product_pulls              OUT     order picking (with order) and the warehouse pull page
 *   order_details_status_logs  IN      items returned to warehouse (status 9)
 *
 * Not recorded anywhere (so not shown): a product's starting quantity when it was created.
 * Pulls saved before picking recorded its order show as "Warehouse pull".
 */
class StockMovementReport
{
    /** Movement type key => label */
    public const TYPES = [
        'import'     => 'Product import',
        'batch'      => 'Stock batch',
        'return'     => 'Returned to warehouse',
        'picking'    => 'Order picking',
        'pull'       => 'Warehouse pull',
        'adjustment' => 'Manual adjustment',
    ];

    public const SORTABLE = [
        'moved_at' => 'm.moved_at',
        'product'  => 'product_name',
        'movement' => 'm.movement',
        'qty_in'   => 'm.qty_in',
        'qty_out'  => 'm.qty_out',
    ];

    public const PRODUCT_SORTABLE = [
        'product'  => 'product_name',
        'qty_in'   => 'qty_in',
        'qty_out'  => 'qty_out',
        'net'      => 'net',
        'stock'    => 'stock',
    ];

    public function __construct(private array $filters = [])
    {
    }

    /** Every recorded stock change as one row: moved_at, product, type, qty in / out, reference */
    private function union(): Builder
    {
        $restocks = DB::table('product_restock_logs')->select([
            'created_at AS moved_at',
            'product_id',
            DB::raw("CASE WHEN source = 'manual' THEN 'adjustment' ELSE 'import' END AS type_key"),
            DB::raw("CASE WHEN source = 'manual' THEN 'Manual adjustment' ELSE 'Product import' END AS movement"),
            DB::raw('GREATEST(added_quantity, 0) AS qty_in'),
            DB::raw('GREATEST(-added_quantity, 0) AS qty_out'),
            DB::raw('NULL AS reference'),
            DB::raw('NULL AS order_id'),
            'user_id',
            DB::raw('NULL AS note'),
        ]);

        $batches = DB::table('product_transaction_items AS i')
            ->join('product_transaction_batches AS b', 'b.id', '=', 'i.batch_id')
            ->leftJoin('product_transaction_types AS t', 't.id', '=', 'b.transaction_type_id')
            ->select([
                DB::raw('COALESCE(b.transaction_date, i.created_at) AS moved_at'),
                'i.product_id',
                DB::raw("'batch' AS type_key"),
                DB::raw("CONCAT('Stock batch: ', COALESCE(t.name, '-')) AS movement"),
                DB::raw('GREATEST(i.after_quantity - i.before_quantity, 0) AS qty_in'),
                DB::raw('GREATEST(i.before_quantity - i.after_quantity, 0) AS qty_out'),
                'b.reference_no AS reference',
                DB::raw('NULL AS order_id'),
                'b.created_by AS user_id',
                'b.note',
            ]);

        $pulls = DB::table('product_pulls AS p')
            ->leftJoin('orders AS o', 'o.id', '=', 'p.order_id')
            ->select([
                DB::raw('COALESCE(p.pulled_at, p.created_at) AS moved_at'),
                'p.product_id',
                DB::raw("CASE WHEN p.order_id IS NULL THEN 'pull' ELSE 'picking' END AS type_key"),
                DB::raw("CASE WHEN p.order_id IS NULL THEN 'Warehouse pull' ELSE 'Order picking' END AS movement"),
                DB::raw('0 AS qty_in'),
                'p.quantity AS qty_out',
                'o.order_number AS reference',
                'p.order_id',
                'p.employee_id AS user_id',
                DB::raw('NULL AS note'),
            ]);

        $returns = DB::table('order_details_status_logs AS l')
            ->join('order_details AS d', 'd.id', '=', 'l.order_details_id')
            ->leftJoin('orders AS o', 'o.id', '=', 'd.order_id')
            ->where('l.status_id', 9)
            ->select([
                DB::raw('COALESCE(l.acted_at, l.created_at) AS moved_at'),
                'd.product_id',
                DB::raw("'return' AS type_key"),
                DB::raw("'Returned to warehouse' AS movement"),
                'd.quantity AS qty_in',
                DB::raw('0 AS qty_out'),
                'o.order_number AS reference',
                'd.order_id',
                'l.acted_by AS user_id',
                'l.remarks AS note',
            ]);

        return $restocks->unionAll($batches)->unionAll($pulls)->unionAll($returns);
    }

    /** One row per stock movement, with product / category / user joined and the filters applied */
    public function movements(): Builder
    {
        $f = $this->filters;

        return DB::query()
            ->fromSub($this->union(), 'm')
            ->join('products', 'products.id', '=', 'm.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('users', 'users.id', '=', 'm.user_id')
            ->when($f['date_from'] ?? null, fn ($q, $d) => $q->whereDate('m.moved_at', '>=', $d))
            ->when($f['date_to'] ?? null, fn ($q, $d) => $q->whereDate('m.moved_at', '<=', $d))
            ->when($f['category_id'] ?? null, fn ($q, $id) => $q->where('products.category_id', $id))
            ->when($f['brand'] ?? null, fn ($q, $brand) => $brand === 'Other'
                ? $q->whereNull('categories.brand')
                : $q->where('categories.brand', $brand))
            ->when($f['type'] ?? null, fn ($q, $type) => $q->where('m.type_key', $type))
            ->when($f['direction'] ?? null, fn ($q, $dir) => $q->where($dir === 'in' ? 'm.qty_in' : 'm.qty_out', '>', 0))
            ->when(trim($f['search'] ?? ''), function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(fn ($w) => $w
                    ->where('products.name', 'like', $like)
                    ->orWhere('products.sku', 'like', $like)
                    ->orWhere('m.reference', 'like', $like)
                    ->orWhere('m.note', 'like', $like));
            })
            ->select([
                'm.*',
                'products.name AS product_name',
                'products.sku',
                'products.quantity AS stock',
                DB::raw("COALESCE(categories.name, 'Uncategorized') AS category_name"),
                'users.name AS user_name',
            ]);
    }

    /** Per product: total in, total out and current stock for the filtered movements */
    public function products(): Builder
    {
        return DB::query()
            ->fromSub($this->movements(), 'mv')
            ->groupBy('mv.product_id', 'mv.product_name', 'mv.sku', 'mv.category_name', 'mv.stock')
            ->select([
                'mv.product_id',
                'mv.product_name',
                'mv.sku',
                'mv.category_name',
                DB::raw('SUM(mv.qty_in) AS qty_in'),
                DB::raw('SUM(mv.qty_out) AS qty_out'),
                DB::raw('SUM(mv.qty_in) - SUM(mv.qty_out) AS net'),
                'mv.stock',
            ]);
    }

    /** Per movement type: total in and out */
    public function byType()
    {
        return DB::query()
            ->fromSub($this->movements(), 'mv')
            ->groupBy('mv.movement')
            ->select([
                'mv.movement',
                DB::raw('SUM(mv.qty_in) AS qty_in'),
                DB::raw('SUM(mv.qty_out) AS qty_out'),
            ])
            ->orderBy('mv.movement')
            ->get();
    }

    public static function sortColumn(?string $field): string
    {
        return self::SORTABLE[$field] ?? self::SORTABLE['moved_at'];
    }

    public static function productSortColumn(?string $field): string
    {
        return self::PRODUCT_SORTABLE[$field] ?? self::PRODUCT_SORTABLE['qty_out'];
    }
}

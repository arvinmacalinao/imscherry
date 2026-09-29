<?php

namespace App\Reports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Sales = order lines of Packed/Shipped orders (status 3), valued at the price the
 * platform sold them for (order_details.unit_price), not the master product price.
 *
 * The page (Livewire) and the Excel export both build their data here, so they always match.
 */
class SalesReport
{
    public const SOLD_STATUS = 3; // Packed/Shipped

    /** "Group by" options: key => [label, SQL expression] */
    public const GROUPS = [
        'brand'    => ['Brand',    OrderLines::BRAND],
        'category' => ['Category', "COALESCE(categories.name, 'Uncategorized')"],
        'shop'     => ['Shop',     "COALESCE(shop_names.name, '-')"],
        'platform' => ['Platform', "COALESCE(platforms.name, '-')"],
        'product'  => ['Product',  'COALESCE(products.name, order_details.product_name)'],
    ];

    /** Sort keys the lines table accepts => SQL column (anything else falls back to the date) */
    public const SORTABLE = [
        'order_date'    => 'orders.order_date',
        'order_number'  => 'orders.order_number',
        'invoice_no'    => 'orders.invoice_no',
        'item_name'     => 'item_name',
        'category_name' => 'category_name',
        'shop_name'     => 'shop_name',
        'quantity'      => 'order_details.quantity',
        'unit_price'    => 'order_details.unit_price',
        'line_total'    => 'line_total',
    ];

    public function __construct(private array $filters = [])
    {
    }

    /** One row per sold order line */
    public function lines(): Builder
    {
        return OrderLines::query($this->filters)
            ->where('orders.status_id', self::SOLD_STATUS)
            ->select(OrderLines::columns());
    }

    /** Totals per group (brand, category, shop, platform or product), biggest sales first */
    public function summary(string $groupBy)
    {
        $expr = (self::GROUPS[$groupBy] ?? self::GROUPS['category'])[1];

        return $this->lines()
            ->reorder()
            ->select([
                DB::raw("$expr AS label"),
                DB::raw('COUNT(DISTINCT orders.id) AS orders_count'),
                DB::raw('SUM(order_details.quantity) AS qty'),
                DB::raw('SUM(' . OrderLines::AMOUNT . ') AS total'),
            ])
            ->groupBy(DB::raw($expr))
            ->orderByDesc('total')
            ->toBase()
            ->get();
    }

    public static function sortColumn(?string $field): string
    {
        return self::SORTABLE[$field] ?? self::SORTABLE['order_date'];
    }
}

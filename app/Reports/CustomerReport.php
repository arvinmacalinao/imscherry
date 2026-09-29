<?php

namespace App\Reports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Buyers per shop: how many orders each customer name placed in a shop and what they were worth.
 *
 * Orders are not linked to the customers table, and platforms mask buyer names
 * (e.g. "m****** b*****"), so a "customer" here is a name within one shop. Different
 * buyers with the same masked name are counted together.
 */
class CustomerReport
{
    public const SORTABLE = [
        'customer'     => 'customer',
        'shop_name'    => 'shop_name',
        'orders_count' => 'orders_count',
        'value'        => 'value',
        'first_order'  => 'first_order',
        'last_order'   => 'last_order',
    ];

    public function __construct(private array $filters = [])
    {
    }

    private function orders(): Builder
    {
        $f = $this->filters;

        return DB::table('orders')
            ->leftJoin('shop_names', 'shop_names.id', '=', 'orders.shop_name_id')
            ->leftJoin('platforms', 'platforms.id', '=', 'orders.platform_id')
            ->whereNull('orders.deleted_at')
            ->whereNotNull('orders.customer_name')
            ->where('orders.customer_name', '<>', '')
            ->when($f['date_from'] ?? null, fn ($q, $d) => $q->whereDate('orders.order_date', '>=', $d))
            ->when($f['date_to'] ?? null, fn ($q, $d) => $q->whereDate('orders.order_date', '<=', $d))
            ->when($f['shop_id'] ?? null, fn ($q, $id) => $q->where('orders.shop_name_id', $id))
            ->when($f['platform_id'] ?? null, fn ($q, $id) => $q->where('orders.platform_id', $id))
            ->when(trim($f['search'] ?? ''), function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(fn ($w) => $w
                    ->where('orders.customer_name', 'like', $like)
                    ->orWhere('orders.customer_phone', 'like', $like)
                    ->orWhere('orders.shipping_city', 'like', $like));
            });
    }

    /** One row per customer name per shop */
    public function customers(): Builder
    {
        $cancelled = ReportStatus::CANCELLED;
        $returned = implode(',', ReportStatus::RETURNED_ORDER);

        return $this->orders()
            ->groupBy('orders.customer_name', 'orders.shop_name_id', 'shop_names.name', 'platforms.name')
            ->select([
                DB::raw('TRIM(orders.customer_name) AS customer'),
                'shop_names.name AS shop_name',
                'platforms.name AS platform_name',
                DB::raw("SUM(orders.status_id <> $cancelled) AS orders_count"),
                DB::raw('SUM(orders.status_id = ' . ReportStatus::SOLD . ') AS shipped_count'),
                DB::raw("SUM(orders.status_id IN ($returned)) AS returned_count"),
                DB::raw("SUM(orders.status_id = $cancelled) AS cancelled_count"),
                DB::raw("SUM(CASE WHEN orders.status_id <> $cancelled THEN orders.total ELSE 0 END) AS value"),
                DB::raw('MIN(orders.order_date) AS first_order'),
                DB::raw('MAX(orders.order_date) AS last_order'),
                DB::raw('MAX(orders.shipping_city) AS city'),
            ]);
    }

    /** Per shop: distinct buyers, orders and value (cancelled orders excluded) */
    public function summary()
    {
        $cancelled = ReportStatus::CANCELLED;

        return $this->orders()
            ->groupBy('shop_names.name')
            ->select([
                DB::raw("COALESCE(shop_names.name, '-') AS label"),
                DB::raw('COUNT(DISTINCT orders.customer_name) AS buyers'),
                DB::raw("SUM(orders.status_id <> $cancelled) AS orders_count"),
                DB::raw("SUM(CASE WHEN orders.status_id <> $cancelled THEN orders.total ELSE 0 END) AS value"),
            ])
            ->orderByDesc('value')
            ->get();
    }

    public static function sortColumn(?string $field): string
    {
        return self::SORTABLE[$field] ?? self::SORTABLE['value'];
    }
}

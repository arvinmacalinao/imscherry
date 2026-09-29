<?php

namespace App\Reports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Cancelled orders: when, by whom and why, and whether stock had already been picked for them.
 * Cancelling does not put picked stock back, so "picked" units are missing from the shelf
 * until someone returns them manually.
 */
class CancellationReport
{
    public const SORTABLE = [
        'event_at'     => 'event_at',
        'order_date'   => 'orders.order_date',
        'order_number' => 'orders.order_number',
        'shop_name'    => 'shop_name',
        'total'        => 'orders.total',
        'items_qty'    => 'items_qty',
        'picked_qty'   => 'picked_qty',
    ];

    public function __construct(private array $filters = [])
    {
    }

    public function orders(): Builder
    {
        $items = DB::table('order_details')
            ->groupBy('order_id')
            ->select('order_id', DB::raw('SUM(quantity) AS items_qty'), DB::raw('SUM(scanned_qty) AS picked_qty'));

        $f = $this->filters;

        return OrderEvents::query(ReportStatus::CANCELLED, $f)
            ->where('orders.status_id', ReportStatus::CANCELLED)
            ->leftJoinSub($items, 'items', 'items.order_id', '=', 'orders.id')
            ->when(($f['picked'] ?? '') === 'yes', fn ($q) => $q->where('items.picked_qty', '>', 0))
            ->select([
                ...OrderEvents::columns(),
                DB::raw('COALESCE(items.items_qty, 0) AS items_qty'),
                DB::raw('COALESCE(items.picked_qty, 0) AS picked_qty'),
            ]);
    }

    /** Per shop: cancelled orders, their value and picked units that were not put back */
    public function summary()
    {
        return DB::query()
            ->fromSub($this->orders(), 'c')
            ->groupBy('c.shop_name')
            ->select([
                DB::raw("COALESCE(c.shop_name, '-') AS label"),
                DB::raw('COUNT(*) AS orders_count'),
                DB::raw('SUM(c.total) AS total'),
                DB::raw('SUM(c.picked_qty) AS picked_qty'),
            ])
            ->orderByDesc('orders_count')
            ->get();
    }

    public static function sortColumn(?string $field): string
    {
        return self::SORTABLE[$field] ?? self::SORTABLE['event_at'];
    }
}

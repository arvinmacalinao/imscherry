<?php

namespace App\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Quantity and amount sold, returned and cancelled per brand and category.
 */
class CategoryReport
{
    public function __construct(private array $filters = [])
    {
    }

    /** One row per category that had activity, ordered brand > category */
    public function rows(): Collection
    {
        $amount = OrderLines::AMOUNT;
        $returned = implode(',', ReportStatus::RETURNED_ORDER);
        $sum = fn (string $condition, string $value, string $alias) => DB::raw("SUM(CASE WHEN $condition THEN $value ELSE 0 END) AS $alias");

        return OrderLines::query($this->filters)
            ->whereIn('orders.status_id', [ReportStatus::SOLD, ReportStatus::CANCELLED, ...ReportStatus::RETURNED_ORDER])
            ->select([
                DB::raw(OrderLines::BRAND . ' AS brand'),
                'categories.id AS category_id',
                DB::raw("COALESCE(categories.name, 'Uncategorized') AS category"),
                $sum('orders.status_id = ' . ReportStatus::SOLD, 'order_details.quantity', 'sold_qty'),
                $sum('orders.status_id = ' . ReportStatus::SOLD, $amount, 'sold_amount'),
                $sum("orders.status_id IN ($returned)", 'order_details.quantity', 'returned_qty'),
                $sum("orders.status_id IN ($returned)", $amount, 'returned_amount'),
                $sum('orders.status_id = ' . ReportStatus::CANCELLED, 'order_details.quantity', 'cancelled_qty'),
                $sum('orders.status_id = ' . ReportStatus::CANCELLED, $amount, 'cancelled_amount'),
            ])
            ->groupBy(DB::raw(OrderLines::BRAND), 'categories.id', 'categories.name')
            // CHERRY, LUXELLE, then Other; categories A-Z inside each brand
            ->orderByRaw("brand = 'Other', brand, category")
            ->toBase()
            ->get();
    }

    /** Totals of the given rows (for brand subtotals and the grand total) */
    public static function totals(Collection $rows): array
    {
        $keys = ['sold_qty', 'sold_amount', 'returned_qty', 'returned_amount', 'cancelled_qty', 'cancelled_amount'];

        return collect($keys)->mapWithKeys(fn ($key) => [$key => $rows->sum($key)])->all();
    }
}

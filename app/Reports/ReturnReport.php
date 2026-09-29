<?php

namespace App\Reports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Returned items: when the order came back and who scanned it, and what has been done
 * with each item since (returned to warehouse, for claims, refunded, claim rejected, or nothing yet).
 */
class ReturnReport
{
    public const RETURNED_AT = 'COALESCE(return_log.created_at, orders.updated_at)';

    /** Item outcome key: the item status for handled items, 0 for items nobody has acted on */
    public const OUTCOME = 'CASE WHEN order_details.status_id IN (9, 10, 11, 12) THEN order_details.status_id ELSE 0 END';

    public const SORTABLE = [
        'returned_at'  => 'returned_at',
        'order_number' => 'orders.order_number',
        'shop_name'    => 'shop_name',
        'item_name'    => 'item_name',
        'quantity'     => 'order_details.quantity',
        'line_total'   => 'line_total',
        'outcome'      => 'outcome',
        'action_at'    => 'action_at',
    ];

    public function __construct(private array $filters = [])
    {
    }

    public function lines(): Builder
    {
        $f = $this->filters;

        $latestReturn = DB::table('order_status_logs')
            ->where('status_id', ReportStatus::RETURNED)
            ->groupBy('order_id')
            ->select('order_id', DB::raw('MAX(id) AS log_id'));

        $latestItemLog = DB::table('order_details_status_logs')
            ->groupBy('order_details_id')
            ->select('order_details_id', DB::raw('MAX(id) AS log_id'));

        // dates filter on the return date, not the order date
        return OrderLines::query(Arr::except($f, ['date_from', 'date_to']))
            ->whereIn('orders.status_id', ReportStatus::RETURNED_ORDER)
            ->leftJoinSub($latestReturn, 'rl', 'rl.order_id', '=', 'orders.id')
            ->leftJoin('order_status_logs AS return_log', 'return_log.id', '=', 'rl.log_id')
            ->leftJoin('users AS return_user', 'return_user.id', '=', 'return_log.acted_by')
            ->leftJoinSub($latestItemLog, 'il', 'il.order_details_id', '=', 'order_details.id')
            ->leftJoin('order_details_status_logs AS item_log', 'item_log.id', '=', 'il.log_id')
            ->leftJoin('users AS item_user', 'item_user.id', '=', 'item_log.acted_by')
            ->when($f['date_from'] ?? null, fn ($q, $d) => $q->whereRaw('DATE(' . self::RETURNED_AT . ') >= ?', [$d]))
            ->when($f['date_to'] ?? null, fn ($q, $d) => $q->whereRaw('DATE(' . self::RETURNED_AT . ') <= ?', [$d]))
            ->when(($f['outcome'] ?? '') !== '', fn ($q) => $q->whereRaw(self::OUTCOME . ' = ?', [(int) $f['outcome']]))
            ->select([
                ...OrderLines::columns(),
                DB::raw(self::OUTCOME . ' AS outcome'),
                DB::raw(self::RETURNED_AT . ' AS returned_at'),
                'return_user.name AS returned_by',
                DB::raw('COALESCE(item_log.acted_at, item_log.created_at) AS action_at'),
                'item_user.name AS action_by',
                DB::raw('COALESCE(item_log.remarks, order_details.remarks) AS action_remarks'),
            ]);
    }

    /** Per outcome: items, quantity and amount */
    public function summary()
    {
        return $this->lines()
            ->reorder()
            ->select([
                DB::raw(self::OUTCOME . ' AS outcome'),
                DB::raw('COUNT(*) AS items_count'),
                DB::raw('SUM(order_details.quantity) AS qty'),
                DB::raw('SUM(' . OrderLines::AMOUNT . ') AS total'),
            ])
            ->groupBy(DB::raw(self::OUTCOME))
            ->orderBy('outcome')
            ->toBase()
            ->get();
    }

    /** Label for an outcome key from OUTCOME */
    public static function outcomeLabel($outcome): string
    {
        return ReportStatus::itemOutcome((int) $outcome ?: null);
    }

    public static function sortColumn(?string $field): string
    {
        return self::SORTABLE[$field] ?? self::SORTABLE['returned_at'];
    }
}

<?php

namespace App\Reports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Orders joined with shop, platform and the LATEST status-log entry for one status
 * (when it happened, who did it, the remarks). Used by the cancellation and return reports.
 *
 * The date filter applies to when the event happened (the log date), falling back to the
 * order's last update for old orders that have no log entry.
 */
class OrderEvents
{
    public const EVENT_AT = 'COALESCE(event_log.created_at, orders.updated_at)';

    public static function query(int $logStatus, array $f = []): Builder
    {
        $latestLog = DB::table('order_status_logs')
            ->where('status_id', $logStatus)
            ->groupBy('order_id')
            ->select('order_id', DB::raw('MAX(id) AS log_id'));

        return DB::table('orders')
            ->leftJoin('shop_names', 'shop_names.id', '=', 'orders.shop_name_id')
            ->leftJoin('platforms', 'platforms.id', '=', 'orders.platform_id')
            ->leftJoinSub($latestLog, 'latest_log', 'latest_log.order_id', '=', 'orders.id')
            ->leftJoin('order_status_logs AS event_log', 'event_log.id', '=', 'latest_log.log_id')
            ->leftJoin('users AS event_user', 'event_user.id', '=', 'event_log.acted_by')
            ->whereNull('orders.deleted_at')
            ->when($f['date_from'] ?? null, fn ($q, $d) => $q->whereRaw('DATE(' . self::EVENT_AT . ') >= ?', [$d]))
            ->when($f['date_to'] ?? null, fn ($q, $d) => $q->whereRaw('DATE(' . self::EVENT_AT . ') <= ?', [$d]))
            ->when($f['shop_id'] ?? null, fn ($q, $id) => $q->where('orders.shop_name_id', $id))
            ->when($f['platform_id'] ?? null, fn ($q, $id) => $q->where('orders.platform_id', $id))
            ->when(trim($f['search'] ?? ''), function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(fn ($w) => $w
                    ->where('orders.order_number', 'like', $like)
                    ->orWhere('orders.invoice_no', 'like', $like)
                    ->orWhere('orders.tracking_number', 'like', $like)
                    ->orWhere('orders.customer_name', 'like', $like)
                    ->orWhere('event_log.remarks', 'like', $like));
            });
    }

    /** Order + event columns every event listing shows */
    public static function columns(): array
    {
        return [
            'orders.id',
            'orders.order_number',
            'orders.invoice_no',
            'orders.tracking_number',
            'orders.order_date',
            'orders.customer_name',
            'orders.total',
            'shop_names.name AS shop_name',
            'platforms.name AS platform_name',
            DB::raw(self::EVENT_AT . ' AS event_at'),
            'event_user.name AS event_by',
            DB::raw('COALESCE(event_log.remarks, orders.remarks) AS event_remarks'),
        ];
    }
}

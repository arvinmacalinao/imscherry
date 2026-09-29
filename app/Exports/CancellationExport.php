<?php

namespace App\Exports;

use App\Reports\CancellationReport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Cancellation report workbook, built from the same filters as the page.
 */
class CancellationExport implements WithMultipleSheets
{
    public function __construct(private array $filters)
    {
    }

    public function sheets(): array
    {
        $report = new CancellationReport($this->filters);

        $summary = $report->summary()->map(fn ($row) => [
            $row->label, (int) $row->orders_count, (float) $row->total, (int) $row->picked_qty,
        ]);
        $summary->push(['Grand Total', $summary->sum(1), $summary->sum(2), $summary->sum(3)]);

        return [
            new ReportSheet(
                'By Shop',
                ['Shop', 'Cancelled Orders', 'Order Value', 'Units Picked'],
                $summary,
                fn ($row) => $row,
                text: [1],
                money: [3],
            ),
            new ReportSheet(
                'Cancelled Orders',
                ['Cancelled On', 'Order No.', 'Invoice No.', 'Tracking No.', 'Order Date', 'Shop', 'Platform',
                    'Customer', 'Items', 'Order Value', 'Units Picked', 'Cancelled By', 'Reason'],
                $report->orders()->orderBy('event_at')->orderBy('orders.id'),
                fn ($o) => [
                    $o->event_at ? Carbon::parse($o->event_at)->format('Y-m-d H:i') : '',
                    $o->order_number,
                    $o->invoice_no,
                    $o->tracking_number,
                    $o->order_date ? Carbon::parse($o->order_date)->format('Y-m-d') : '',
                    $o->shop_name,
                    $o->platform_name,
                    $o->customer_name,
                    (int) $o->items_qty,
                    (float) ($o->total ?? 0),
                    (int) $o->picked_qty,
                    $o->event_by,
                    $o->event_remarks,
                ],
                text: [2, 3, 4, 6, 7, 8, 12, 13],
                money: [10],
            ),
        ];
    }
}

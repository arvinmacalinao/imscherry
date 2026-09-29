<?php

namespace App\Exports;

use App\Reports\ReturnReport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Returned items workbook, built from the same filters as the page.
 */
class ReturnExport implements WithMultipleSheets
{
    public function __construct(private array $filters)
    {
    }

    public function sheets(): array
    {
        $report = new ReturnReport($this->filters);
        $date = fn ($value, $format = 'Y-m-d H:i') => $value ? Carbon::parse($value)->format($format) : '';

        $summary = $report->summary()->map(fn ($row) => [
            ReturnReport::outcomeLabel($row->outcome), (int) $row->items_count, (int) $row->qty, (float) $row->total,
        ]);
        $summary->push(['Grand Total', $summary->sum(1), $summary->sum(2), $summary->sum(3)]);

        return [
            new ReportSheet(
                'By Item Status',
                ['Item Status', 'Items', 'Qty', 'Amount'],
                $summary,
                fn ($row) => $row,
                text: [1],
                money: [4],
            ),
            new ReportSheet(
                'Returned Items',
                ['Returned On', 'Order No.', 'Invoice No.', 'Order Date', 'Shop', 'Platform', 'Customer', 'SKU',
                    'Product', 'Category', 'Brand', 'Qty', 'Amount', 'Returned By', 'Item Status', 'Action On',
                    'Action By', 'Remarks'],
                $report->lines()->orderBy('returned_at')->orderBy('order_details.id'),
                fn ($l) => [
                    $date($l->returned_at),
                    $l->order_number,
                    $l->invoice_no,
                    $date($l->order_date, 'Y-m-d'),
                    $l->shop_name,
                    $l->platform_name,
                    $l->customer_name,
                    $l->sku,
                    $l->item_name,
                    $l->category_name,
                    $l->brand,
                    (int) $l->quantity,
                    (float) $l->line_total,
                    $l->returned_by,
                    ReturnReport::outcomeLabel($l->outcome),
                    $l->outcome ? $date($l->action_at) : '',
                    $l->outcome ? $l->action_by : '',
                    $l->outcome ? $l->action_remarks : '',
                ],
                text: [2, 3, 5, 6, 7, 8, 9, 10, 11, 14, 15, 17, 18],
                money: [13],
            ),
        ];
    }
}

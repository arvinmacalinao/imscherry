<?php

namespace App\Exports;

use App\Reports\SalesReport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Sales report workbook, built from the same filters as the page:
 *   sheet 1 = totals per group (brand / category / shop / platform / product)
 *   sheet 2 = every sold order line
 */
class SalesExport implements WithMultipleSheets
{
    public function __construct(private array $filters, private string $groupBy = 'brand')
    {
    }

    public function sheets(): array
    {
        $report = new SalesReport($this->filters);
        $label = (SalesReport::GROUPS[$this->groupBy] ?? SalesReport::GROUPS['brand'])[0];

        $summary = $report->summary($this->groupBy)->map(fn ($row) => [
            $row->label, (int) $row->orders_count, (int) $row->qty, (float) $row->total,
        ]);
        $summary->push(['Grand Total', null, $summary->sum(2), $summary->sum(3)]);

        return [
            new ReportSheet(
                'By ' . $label,
                [$label, 'Orders', 'Qty Sold', 'Sales'],
                $summary,
                fn ($row) => $row,
                text: [1],
                money: [4],
            ),
            new ReportSheet(
                'Order Lines',
                ['Date', 'Order No.', 'Invoice No.', 'Shop', 'Platform', 'Customer', 'SKU', 'Product',
                    'Category', 'Brand', 'Qty', 'Unit Price', 'Total', 'Payment'],
                $report->lines()->orderBy('orders.order_date')->orderBy('order_details.id'),
                fn ($line) => [
                    $line->order_date ? Carbon::parse($line->order_date)->format('Y-m-d') : '',
                    $line->order_number,
                    $line->invoice_no,
                    $line->shop_name,
                    $line->platform_name,
                    $line->customer_name,
                    $line->sku,
                    $line->item_name,
                    $line->category_name,
                    $line->brand,
                    (int) $line->quantity,
                    (float) ($line->unit_price ?? 0),
                    (float) $line->line_total,
                    $line->payment_type,
                ],
                text: [2, 3, 4, 5, 6, 7, 8, 9, 10, 14],
                money: [12, 13],
            ),
        ];
    }
}

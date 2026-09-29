<?php

namespace App\Exports;

use App\Reports\CustomerReport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Customer (buyers per shop) workbook, built from the same filters as the page.
 */
class CustomerExport implements WithMultipleSheets
{
    public function __construct(private array $filters)
    {
    }

    public function sheets(): array
    {
        $report = new CustomerReport($this->filters);
        $date = fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : '';

        $summary = $report->summary()->map(fn ($row) => [
            $row->label, (int) $row->buyers, (int) $row->orders_count, (float) $row->value,
        ]);
        $summary->push(['Grand Total', $summary->sum(1), $summary->sum(2), $summary->sum(3)]);

        return [
            new ReportSheet(
                'By Shop',
                ['Shop', 'Buyers', 'Orders', 'Order Value'],
                $summary,
                fn ($row) => $row,
                text: [1],
                money: [4],
            ),
            new ReportSheet(
                'Customers',
                ['Customer', 'City', 'Shop', 'Platform', 'Orders', 'Shipped', 'Returned', 'Cancelled',
                    'Order Value', 'First Order', 'Last Order'],
                $report->customers()->orderByDesc('value')->orderBy('customer'),
                fn ($c) => [
                    $c->customer,
                    $c->city,
                    $c->shop_name,
                    $c->platform_name,
                    (int) $c->orders_count,
                    (int) $c->shipped_count,
                    (int) $c->returned_count,
                    (int) $c->cancelled_count,
                    (float) $c->value,
                    $date($c->first_order),
                    $date($c->last_order),
                ],
                text: [1, 2, 3, 4],
                money: [9],
            ),
        ];
    }
}

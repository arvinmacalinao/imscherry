<?php

namespace App\Exports;

use App\Reports\CategoryReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Brand / category report workbook, built from the same filters as the page.
 */
class CategoryReportExport implements WithMultipleSheets
{
    public function __construct(private array $filters)
    {
    }

    public function sheets(): array
    {
        $rows = (new CategoryReport($this->filters))->rows();
        $cells = fn ($label, array $t) => [
            $label,
            (int) $t['sold_qty'], (float) $t['sold_amount'],
            (int) $t['returned_qty'], (float) $t['returned_amount'],
            (int) $t['cancelled_qty'], (float) $t['cancelled_amount'],
        ];

        $out = collect();
        foreach ($rows->groupBy('brand') as $brand => $brandRows) {
            $out->push($cells($brand, CategoryReport::totals($brandRows)));
            foreach ($brandRows as $row) {
                $out->push($cells('    ' . $row->category, (array) $row));
            }
        }
        $out->push($cells('Grand Total', CategoryReport::totals($rows)));

        return [
            new ReportSheet(
                'By Brand and Category',
                ['Brand / Category', 'Sold Qty', 'Sold Amount', 'Returned Qty', 'Returned Amount', 'Cancelled Qty', 'Cancelled Amount'],
                $out,
                fn ($row) => $row,
                text: [1],
                money: [3, 5, 7],
            ),
        ];
    }
}

<?php

namespace App\Exports;

use App\Reports\StockMovementReport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Warehouse stock in / out workbook, built from the same filters as the page:
 *   sheet 1 = in / out / net / current stock per product
 *   sheet 2 = every stock movement
 */
class WarehouseExport implements WithMultipleSheets
{
    public function __construct(private array $filters)
    {
    }

    public function sheets(): array
    {
        $report = new StockMovementReport($this->filters);

        return [
            new ReportSheet(
                'By Product',
                ['Product', 'SKU', 'Category', 'In', 'Out', 'Net', 'Stock Now'],
                $report->products()->orderByDesc('qty_out')->orderBy('mv.product_name'),
                fn ($p) => [
                    $p->product_name,
                    $p->sku,
                    $p->category_name,
                    (int) $p->qty_in,
                    (int) $p->qty_out,
                    (int) $p->net,
                    (int) $p->stock,
                ],
                text: [1, 2, 3],
            ),
            new ReportSheet(
                'Stock Movements',
                ['Date', 'Product', 'SKU', 'Category', 'Movement', 'In', 'Out', 'Reference', 'By', 'Note'],
                $report->movements()->orderBy('m.moved_at')->orderBy('products.name'),
                fn ($m) => [
                    $m->moved_at ? Carbon::parse($m->moved_at)->format('Y-m-d H:i') : '',
                    $m->product_name,
                    $m->sku,
                    $m->category_name,
                    $m->movement,
                    (int) $m->qty_in,
                    (int) $m->qty_out,
                    $m->reference,
                    $m->user_name,
                    $m->note,
                ],
                text: [2, 3, 4, 5, 8, 9, 10],
            ),
        ];
    }
}

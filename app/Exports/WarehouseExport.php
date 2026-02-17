<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WarehouseExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct(Collection $products)
    {
        $this->products = $products;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Name',
            'SKU',
            'Category',
            'Quantity',
        ];
    }

    public function collection()
    {
        return $this->products->map(function ($product, $index) {
            return [
                $index + 1,
                $product->name,
                $product->sku,
                optional($product->category)->name,
                $product->quantity,
            ];
        });
    }
}

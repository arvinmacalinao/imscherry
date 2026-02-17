<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesExport implements FromCollection, WithHeadings
{
    protected $orders;

    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Order No.',
            'Invoice No.',
            'Tracking No.',
            'Customer',
            'Shop Name',
            'Date',
            'Total',
        ];
    }

    public function collection()
    {
        return $this->orders->map(function ($order, $index) {
            return [
                $index + 1,
                $order->order_number,
                $order->invoice_no,
                $order->tracking_number,
                $order->customer_name,
                optional($order->shopName)->invoice_prefix,
                $order->order_date->format('d-m-Y'),
                $order->total,
            ];
        });
    }
}

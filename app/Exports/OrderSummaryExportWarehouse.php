<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderSummaryExportWarehouse implements FromView
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function view(): View
    {
        $rows = [];

        foreach ($this->orders as $order) {
        
            // First row: order info (no product yet)
            $rows[] = [
                'order_number'   => $order->invoice_no ?? '-',
                'tracking_number'=> $order->tracking_number ?? '-',
                'date_order'     => $order->order_date ?? '-',
                'customer_name'    => $order->customer_name ?? '-',
                'product_name'   => '',
                'sku'            => '',
                'quantity'       => '',
            ];
        
            // Child rows: product list under this order
            foreach ($order->details as $detail) {
            
                $product = $detail->product;
                if (!$product) continue;
            
                $rows[] = [
                    'order_number'   => '', // leave blank below
                    'tracking_number'=> '',
                    'date_order'     => '',
                    'customer_name'  => '',
                    'product_name'   => $product->name,
                    'sku'            => $product->sku ?? '-',
                    'quantity'       => $detail->quantity,
                ];
            }
        }

        return view('exports.warehouse_order_summary', [
            'rows' => $rows,
        ]);

    }
}

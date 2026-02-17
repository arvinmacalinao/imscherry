<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrderSummaryExport implements FromView
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function view(): View
    {
        $items = [];

        foreach ($this->orders as $order) {
            foreach ($order->details as $detail) {
                $product = $detail->product;
                if (!$product) continue;

                $productId = $product->id;

                if (!isset($items[$productId])) {
                    $items[$productId] = [
                        'product_name' => $product->name,
                        'product_code' => $product->sku ?? '-',
                        'total_quantity' => 0,
                        'orders' => [],
                    ];
                }

                $items[$productId]['total_quantity'] += $detail->quantity;
                $items[$productId]['orders'][] = $order->invoice_no ?? $order->tracking_number ?? '-';
                $items[$productId]['order_names'][] = $order->customer_name ?? '-';
            }
        }

        return view('exports.order_summary', [
            'items' => $items,
        ]);
    }
}

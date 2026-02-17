<?php

namespace App\Http\Livewire\Scans; // <-- Correct namespace (add Http)

use App\Models\Order;

trait ScanTrait
{
    public $scanInput = '';
    public $scannedOrders = [];

    public function scan()
    {
        $tracking = trim($this->scanInput);
        $this->scanInput = '';

        if (!$tracking) {
            return;
        }

        $order = Order::where('tracking_number', $tracking)
                      ->orWhere('order_number', $tracking)
                      ->first();

        if (!$order) {
            $this->addError('scanInput', 'Order not found.');
            return;
        }

        $this->updateOrderStatus($order);

        // Push order into scanned list
        $this->scannedOrders[] = [
            'order_number'     => $order->order_number,
            'tracking_number'  => $order->tracking_number,
            'status'           => $order->status,
            'scanned_at'       => now()->format("Y-m-d H:i:s"),
        ];
    }
}

<?php

namespace App\Http\Livewire\Scan;

use App\Models\Order;
use Livewire\Component;

class CancelledScan extends Component
{
    public $scanInput = '';
    public $scannedOrders = [];

    public function scan()
    {
        $this->validate([
            'scanInput' => 'required',
        ]);

        $tracking = trim($this->scanInput);
        $this->scanInput = '';

        $order = Order::where('tracking_number', $tracking)
                      ->orWhere('order_number', $tracking)
                      ->first();

        if (!$order) {
            $this->addError('scanInput', 'Order not found.');
            return;
        }

        // Update order status
        $order->status = 'cancelled';
        $order->save();

        // Push scanned order into table
        $this->scannedOrders[] = [
            'order_number'     => $order->order_number,
            'tracking_number'  => $order->tracking_number,
            'status'           => $order->status,
            'scanned_at'       => now()->format("Y-m-d H:i:s"),
        ];
    }

    public function render()
    {
        return view('livewire.scans.cancelled-scan');
    }
}

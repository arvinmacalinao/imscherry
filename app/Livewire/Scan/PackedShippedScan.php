<?php

namespace App\Livewire\Scan;

use App\Models\Order;
use Livewire\Component;
use App\Livewire\Scan\ScanTrait;

class PackedShippedScan extends Component
{
    use ScanTrait;

    public function confirm()
    {
        foreach ($this->scannedOrders as $item) {
            $order = Order::find($item['id']);
            if ($order) {
                $order->update(['status_id' => 6]); // Packed / Shipped
            }
        }

        $this->scannedOrders = [];
        $this->message = "📦 Orders marked as Packed / Shipped!";
    }

    public function render()
    {
         return view('scan.packed');
    }
}

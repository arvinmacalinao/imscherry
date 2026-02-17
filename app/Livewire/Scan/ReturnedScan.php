<?php

namespace App\Livewire\Scan;

use App\Models\Order;
use Livewire\Component;
use App\Livewire\Scan\ScanTrait;

class ReturnedScan extends Component
{
    use ScanTrait;

    public function confirm()
    {
        foreach ($this->scannedOrders as $item) {
            $order = Order::find($item['id']);

            if ($order) {
                $order->update(['status_id' => 5]); // QC completed
            }
        }

        $this->scannedOrders = [];
        $this->message = "🎉 QC Completed for selected orders!";
    }

    public function render()
    {
        return view('scan.returned');
    }
}

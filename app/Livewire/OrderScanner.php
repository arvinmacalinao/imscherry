<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Models\OrderDetail;

class OrderScanner extends Component
{
    public $order;
    public $barcode;
    public $message = '';
    public $scanQty = 1;

    public function mount(Order $order)
    {
        // Only warehouse staff allowed
        // if (!auth()->user()->hasRole('warehouse')) {
        //     abort(403);
        // }

        $this->order = $order->load('details.product');
    }

    // public function scanBarcode()
    //     {
    //         $this->validate([
    //             'barcode' => 'required',
    //         ]);
    
    //         // Find item by barcode in this order
    //         $detail = $this->order->details
    //             ->where('product.sku', $this->barcode)
    //             ->first();
    
    //         if (!$detail) {
    //             $this->message = "❌ Item not found in this order.";
    //             $this->barcode = '';
    //             return;
    //         }
    
    //         if ($detail->scanned_qty >= $detail->quantity) {
    //         $this->message = "⚠️ You already scanned all required quantity for: {$detail->product->name}";
    //         $this->barcode = '';
    //          return;
    //         }
    
    //         // Normal scanning
    //         $detail->update([
    //              'scanned_qty' => $detail->scanned_qty + 1
    //         ]);
    
    //         $this->message = "✔ Item scanned: {$detail->product->name}";
    //         $this->barcode = '';
    
    //         // Check if all are fully scanned
    //         if ($this->orderCompleted()) {
    //             $this->order->update(['status_id' => 8]); // 4 = Picked
    //             $this->message = "🎉 All items scanned! Order moved to QC.";
    //         }
    //     }

    public function scanBarcode()
    {
        $this->validate([
            'barcode' => 'required',
            'scanQty' => 'required|integer|min:1',
        ]);
    
        $detail = $this->order->details
            ->where('product.sku', $this->barcode)
            ->first();
    
        if (!$detail) {
            $this->message = "❌ Item not found in this order.";
            $this->reset(['barcode', 'scanQty']);
            $this->scanQty = 1;
            return;
        }
    
        $remaining = $detail->quantity - $detail->scanned_qty;
    
        if ($remaining <= 0) {
            $this->message = "⚠️ {$detail->product->name} alrea
            dy completed.";
            $this->reset(['barcode']);
            $this->scanQty = 1;
            return;
        }
    
        if ($this->scanQty > $remaining) {
            $this->message = "⚠️ You can only scan {$remaining} more for {$detail->product->name}.";
            return;
        }
    
        // Bulk scan
        $detail->increment('scanned_qty', $this->scanQty);
    
        $this->message = "✔ Scanned {$this->scanQty} × {$detail->product->name}";
        $this->reset(['barcode']);
        $this->scanQty = 1;
    
        if ($this->orderCompleted()) {
            $this->order->update(['status_id' => 8]); // QC
            $this->message = "🎉 All items scanned! Order moved to QC.";
        }
    }


    public function orderCompleted()
    {
        foreach ($this->order->details as $item) {
            if ($item->scanned_qty < $item->qty) {
                return false;
            }
        }
        return true;
    }

    public function render()
    {
        return view('livewire.order-scanner');
    }
}
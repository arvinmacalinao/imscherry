<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Models\OrderDetail;
use App\Models\ProductPull;
use Illuminate\Support\Facades\DB;

class OrderScanner extends Component
{
    public $order;
    public $barcode;
    public $message = '';
    public $scanQty = 1;

    public function mount(Order $order)
    {
        $this->order = $order->load('details.product');
    }

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
            $this->message = "⚠️ {$detail->product->name} already completed.";
            $this->reset(['barcode']);
            $this->scanQty = 1;
            return;
        }

        if ($this->scanQty > $remaining) {
            $this->message = "⚠️ You can only scan {$remaining} more for {$detail->product->name}.";
            return;
        }

        $product = $detail->product;

        // STOCK CHECK
        if ($product->quantity < $this->scanQty) {
            $this->message = "❌ Not enough stock. Available: {$product->quantity}";
            return;
        }

        DB::transaction(function () use ($detail, $product) {
            // 1. UPDATE SCANNED QTY
            $detail->increment('scanned_qty', $this->scanQty);

            
            // 2. DEDUCT PRODUCT STOCK
            $product->decrement('quantity', $this->scanQty);

            // 3. RECORD PRODUCT PULL
            
            ProductPull::create([
                'product_id'  => $product->id,
                'employee_id' => auth()->id(),
                'quantity'    => $this->scanQty,
                'pulled_at'   => now(),
                'status'      => 'completed',
            ]);
        });

        $this->message = "✔ Pulled {$this->scanQty} × {$detail->product->name} | Stock left: {$product->fresh()->quantity}";
        $this->reset(['barcode']);
        $this->scanQty = 1;

        
        // 4. CHECK IF ORDER COMPLETE

        if ($this->orderCompleted()) {
            $this->order->update(['status_id' => 8]); // QC

            // 🔥 Log who completed the picking
            $this->order->statusLogs()->create([
                'status_id' => 8,
                'acted_by'  => auth()->id(),
                'remarks'   => 'All items scanned — moved to QC',
            ]);

            $this->message = "🎉 All items scanned! Order moved to QC.";
        }

        // Reload updated relations
        $this->order->refresh()->load('details.product');
    }

    public function orderCompleted()
    {
        foreach ($this->order->details as $item) {
            if ($item->scanned_qty < $item->quantity) {
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

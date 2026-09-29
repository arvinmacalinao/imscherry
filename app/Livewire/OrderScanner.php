<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
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

        // the same SKU can be on more than one line: use the first line that still needs units
        $lines = $this->order->details->filter(
            fn ($line) => ($line->product->sku ?? $line->sku) == $this->barcode
        );
        $detail = $lines->first(fn ($line) => $line->scanned_qty < $line->quantity) ?? $lines->first();


        if (!$detail) {
            $this->message = "❌ Item not found in this order.";
            $this->reset(['barcode', 'scanQty']);
            $this->scanQty = 1;
            return;
        }

        // checked again on fresh, locked rows: another station may have picked in the meantime
        $error = DB::transaction(function () use ($detail) {
            $order = Order::whereKey($this->order->id)->lockForUpdate()->first();
            $detail = $detail->newQuery()->whereKey($detail->id)->lockForUpdate()->first();
            $product = Product::whereKey($detail->product_id)->lockForUpdate()->first();
            $name = $product->name ?? $detail->product_name;

            if ($order->status_id != 5) {
                return "❌ This order is no longer Invoiced ({$order->status->name}); it cannot be picked.";
            }

            $remaining = $detail->quantity - $detail->scanned_qty;

            if ($remaining <= 0) {
                return "⚠️ {$name} already completed.";
            }

            if ($this->scanQty > $remaining) {
                return "⚠️ You can only scan {$remaining} more for {$name}.";
            }

            // STOCK CHECK
            if (! $product || $product->quantity < $this->scanQty) {
                return "❌ Not enough stock. Available: " . ($product->quantity ?? 0);
            }

            // 1. UPDATE SCANNED QTY
            $detail->increment('scanned_qty', $this->scanQty);

            // 2. DEDUCT PRODUCT STOCK
            $product->decrement('quantity', $this->scanQty);

            // 3. RECORD PRODUCT PULL
            ProductPull::create([
                'product_id'  => $product->id,
                'order_id'    => $this->order->id,
                'employee_id' => auth()->id(),
                'quantity'    => $this->scanQty,
                'pulled_at'   => now(),
                'status'      => 'completed',
            ]);

            return null;
        });

        if ($error) {
            $this->message = $error;
            $this->reset(['barcode']);
            $this->scanQty = 1;
            $this->order->refresh()->load('details.product');
            return;
        }

        // the lines were updated on fresh copies: reload before checking whether picking is complete
        $this->order->load('details.product');
        $product = $detail->product;

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

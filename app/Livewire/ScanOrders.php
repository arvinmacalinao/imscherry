<?php

namespace App\Livewire;

use Livewire\Component;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;

class ScanOrders extends Component
{
    public $scan_code;
    public $status_id; // 2 = packed, 3 = shipped, 4 = returned
    public $cart_instance;
    public $message;

    public function mount($status_id)
    {
        $this->status_id = $status_id;

        // unique cart per scan type
        switch ($status_id) {
            case 2:
                $this->cart_instance = 'packed_scans';
                break;
            case 3:
                $this->cart_instance = 'shipped_scans';
                break;
            case 4:
                $this->cart_instance = 'returned_scans';
                break;
            default:
                $this->cart_instance = 'scans';
        }
    }

    public function render()
    {
        $cart_items = Cart::instance($this->cart_instance)->content();
        return view('livewire.scan-cart', ['cart_items' => $cart_items]);
    }

    public function scan()
    {
        $order = Order::where('tracking_no', $this->scan_code)->first();

        if (!$order) {
            $this->message = '❌ Order not found.';
            $this->scan_code = '';
            return;
        }

        // check if already in cart
        $exists = Cart::instance($this->cart_instance)->search(function ($cartItem) use ($order) {
            return $cartItem->id == $order->id;
        });

        if ($exists->isNotEmpty()) {
            $this->message = '⚠️ Already scanned.';
            $this->scan_code = '';
            return;
        }

        // add to cart
        Cart::instance($this->cart_instance)->add([
            'id' => $order->id,
            'name' => $order->tracking_no,
            'qty' => 1,
            'price' => 0,
            'options' => [
                'customer' => $order->customer_name ?? 'N/A',
                'status' => $order->status_id,
            ]
        ]);

        $this->message = '✅ Added: ' . $order->tracking_no;
        $this->scan_code = '';
    }

    public function removeItem($rowId)
    {
        Cart::instance($this->cart_instance)->remove($rowId);
    }

    public function confirmScans()
    {
        $cart_items = Cart::instance($this->cart_instance)->content();

        if ($cart_items->isEmpty()) {
            $this->message = '⚠️ No scanned orders.';
            return;
        }

        foreach ($cart_items as $item) {
            $order = Order::find($item->id);
            if ($order) {
                $order->update(['status_id' => $this->status_id]);
            }
        }

        Cart::instance($this->cart_instance)->destroy();

        $this->message = '✅ All orders confirmed and updated!';
    }

    public function printList()
    {
        // You can redirect to a route for printing
        return redirect()->route('scan.print', ['type' => $this->cart_instance]);
    }
}


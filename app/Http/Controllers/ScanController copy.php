<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function scan_ship()
    {
        return view('scan.ship');
    }

    public function scan1(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $trackingNumber = trim($request->tracking_number);

        $order = Order::with('details.product')
            ->where('status_id', 1)
            ->where('tracking_number', $trackingNumber)
            ->first();

        if (!$order) {
            return back()->with('error', "Order with tracking number {$trackingNumber} not found.");
        }

        // Change status to "shipped"
        $order->update([
            'status_id' => 3, // assuming 2 = shipped
        ]);

        // Reduce stock for each product in this order
        foreach ($order->details as $detail) {
            if ($detail->product) {
                $detail->product->decrement('quantity', $detail->quantity);
            }
        }

        return back()->with('success', "Order with tracking number {$trackingNumber} is now shipped.");
    }


    public function scan_return()
    {
        return view('scan.return');
    }

    public function scan2(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $trackingNumber = trim($request->tracking_number);

        $order = Order::with('details.product')
            ->where('tracking_number', $trackingNumber)
            ->where('status_id', 3)
            ->first();

        if (!$order) {
            return back()->with('error', "Order with tracking number {$trackingNumber} not found or not yet shipped.");
        }

        // Change status to "returned"
        $order->update([
            'status_id' => 4, // assuming 2 = returned
        ]);

        // Reduce stock for each product in this order
        foreach ($order->details as $detail) {
        if ($detail->product) {
            $detail->product->increment('quantity', $detail->quantity);
        }
    }

        return back()->with('success', "Order with tracking number {$trackingNumber} is returned.");
    }

    public function scan_packed()
    {
        return view('scan.packed');
    }

    public function scan3()
    {
        $request->validate([
        'tracking_number' => 'required|string',
        ]);

        $trackingNumber = trim($request->tracking_number);

        $order = Order::with('details.product')
            ->where('status_id', 1)
            ->where('tracking_number', $trackingNumber)
            ->first();

        if (!$order) {
            return back()->with('error', "Order with tracking number {$trackingNumber} not found or already shipped.");
        }

        // Change status to "shipped"
        $order->update([
            'status_id' => 3, // Shipped
        ]);

        // Reduce stock
        foreach ($order->details as $detail) {
            if ($detail->product) {
                $detail->product->decrement('quantity', $detail->quantity);
            }
        }

        // 🧺 Add to scanned cart (session-based)
        Cart::instance('scanned')->add([
            'id' => $order->id,
            'name' => $order->tracking_number,
            'qty' => 1,
            'price' => 0,
            'options' => [
                'customer' => $order->customer_name ?? 'Unknown',
                'total_items' => $order->details->count(),
                'timestamp' => now()->toDateTimeString(),
            ]
        ]);

        return back()->with('success', "Order {$trackingNumber} scanned successfully!");
    }
}

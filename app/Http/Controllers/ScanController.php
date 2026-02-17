<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Order;
use App\Models\Product;
use App\Models\ScanLog;
use App\Models\ProductPull;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class ScanController extends Controller
{
    /**
     * Show scan page dynamically (ship / cancelled / return)
     */
    
    public function showScanPage($type)
    {
        $cartInstance = 'order_' . $type;

        // Destroy all other scan carts except the current type
    $allScanTypes = ['ship', 'cancelled', 'return'];
    foreach ($allScanTypes as $scanType) {
        if ($scanType !== $type) {
            Cart::instance('order_' . $scanType)->destroy();
        }
    }

        $cart = Cart::instance($cartInstance)->content();

        return view('scan.scan', compact('type', 'cart'));
    }

    // ✅ Updated methods
    public function scan_ship()      { return $this->showScanPage('ship'); }
    public function scan_cancelled() { return $this->showScanPage('cancelled'); } // replaced packed
    public function scan_return()    { return $this->showScanPage('return'); }

    /**
     * Process a scanned tracking number (dynamic)
     */
    public function scan_process(Request $request)
    {
        $path = $request->path(); // e.g. order/scan_ship

        // ✅ detect which scan type
        $type = str_contains($path, 'ship') ? 'ship' :
                (str_contains($path, 'cancelled') ? 'cancelled' : 'return'); // updated

        $cartInstance = 'order_' . $type;

        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $trackingOrOrder = trim($request->tracking_number);

        $order = Order::with('details.product')
            ->where('tracking_number', $trackingOrOrder)
            ->orWhere('order_number', $trackingOrOrder)
            ->first();

        if (!$order) {
            return back()->with('error', "Order with tracking number or order number {$trackingOrOrder} not found.");
        }

        // ✅ Check if already in cart
        $exists = Cart::instance($cartInstance)->search(function ($cartItem, $rowId) use ($order) {
            return $cartItem->id === $order->id;
        });

        if ($exists->isNotEmpty()) {
            return back()->with('error', "Order {$trackingOrOrder} already scanned.");
        }

        // ✅ Add to cart
        Cart::instance($cartInstance)->add([
            'id' => $order->id,
            'name' => $order->tracking_number,
            'qty' => 1,
            'price' => 0,
            'weight' => 0,
            'options' => [
                'customer' => $order->customer_name ?? 'N/A',
                'status_id' => $order->status_id,
            ],
        ]);

        return back()->with('success', "Order {$trackingOrOrder} added to {$type} scan list.");
    }

    /**
     * Confirm scanned orders dynamically (ship / cancelled / return)
     */
    public function confirm_scans(Request $request, $type)
    {
        $cartInstance = 'order_' . $type;
    
        $statusMap = [
            'ship'      => 3,
            'cancelled' => 6, // updated
            'return'    => 4,
        ];
    
        $fieldMap = [
            'ship'      => ['by' => 'shipped_by', 'at' => 'shipped_at'],
            'cancelled' => ['by' => 'cancelled_by', 'at' => 'cancelled_at'], // updated
            'return'    => ['by' => 'returned_by','at' => 'returned_at'],
        ];
    
        if (!isset($statusMap[$type]) || !isset($fieldMap[$type])) {
            return back()->with('error', 'Invalid scan type.');
        }
    
        $cart = Cart::instance($cartInstance)->content();
    
        if ($cart->isEmpty()) {
            return back()->with('error', 'No scanned orders to confirm.');
        }
    
        $updatedCount = 0;
        $userId = auth()->id();
    
        foreach ($cart as $item) {
            $order = Order::find($item->id);
                
            if ($order && $order->status_id != $statusMap[$type]) {
                $oldStatus = $order->status_id; // capture old status before update
            
                $order->update([
                    'status_id'            => $statusMap[$type],
                    $fieldMap[$type]['by'] => $userId,
                    $fieldMap[$type]['at'] => now(),
                ]);
            
                // Create a scan log for each order
                ScanLog::create([
                    'order_id'       => $order->id,
                    'user_id'        => $userId,
                    'from_status_id' => $oldStatus,
                    'to_status_id'   => $statusMap[$type],
                    'event_type'     => $type,
                ]);
            
                $updatedCount++;
            }
        }
        Cart::instance($cartInstance)->destroy();
    
        return back()->with('success', "{$updatedCount} {$type} order(s) confirmed successfully.");
    }


    /**
     * ✅ Remove from cart dynamically
     */
    public function removeFromCart($type, $rowId)
    {
        $cartInstance = 'order_' . $type;
        Cart::instance($cartInstance)->remove($rowId);

        return back()->with('success', ucfirst($type) . ' order removed from list.');
    }

    public function showWarehouseScanPage()
    {
        $cartInstance = 'warehouse_pull';
        $cart = Cart::instance($cartInstance)->content();

        return view('scan.warehouse', compact('cart'));
    }

    public function processProductPull(Request $request)
    {
        \Log::info('Request Data: ', $request->all());
    // Validate the scan input
    $request->validate([
        'sku' => 'required|string', // SKU or product ID
        'quantity' => 'required|integer|min:1', // Quantity being pulled
    ]);
    
    // Trim the input for any leading/trailing whitespace
    $sku = trim($request->sku);
    $quantity = (int) $request->quantity;
    
    // Find the product based on the SKU
    $product = Product::where('sku', $sku)->first();
    
    if (!$product) {
        \Log::info("SKU: {$sku}, Quantity: {$quantity}");
        return back()->with('error', "Product with SKU {$sku} not found.");
    }
    
    // Check if the quantity being pulled is available
    if ($quantity > $product->quantity) {
        \Log::error("Not enough quantity for SKU {$sku}. Available: {$product->quantity}");
        return back()->with('error', "Not enough quantity for SKU {$sku}. Only {$product->quantity} available.");
    }

     \Log::info("Product found: {$product->name}");
    
    // Check if the product is already in the pull list
    $exists = Cart::instance('warehouse_pull')->search(function ($cartItem) use ($product) {
        return $cartItem->id === $product->id;
    });

    \Log::info("Product exists in cart: " . ($exists->isNotEmpty() ? 'Yes' : 'No'));
    
    // If product already in the pull list, just update quantity
    if ($exists->isNotEmpty()) {
        Cart::instance('warehouse_pull')->update($exists->first()->rowId, $exists->first()->qty + $quantity);
    } else {
        // Add the product to the pull list if not already added
        Cart::instance('warehouse_pull')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $quantity,
            'price' => 0,
            'weight' => 0,
            'options' => [
                'sku' => $product->sku,
                'category_id' => $product->category_id,
            ]
        ]);
        \Log::info("Product added to pull list: {$product->name} ({$product->sku})");
    }
    
    return back()->with('success', "Product {$product->name} ({$sku}) added to the pull list.");
    }
        
    /**
     * Confirm the scanned pulls and record them.
     */
    public function confirmProductPulls()
    {
        $cartItems = Cart::instance('warehouse_pull')->content();
        
        if ($cartItems->isEmpty()) {
            return back()->with('error', 'No products scanned to confirm.');
        }
        
        $updatedCount = 0;
        $userId = auth()->id(); // Assuming the employee is logged in
        
        // Loop through the cart and record each pull
        foreach ($cartItems as $item) {
            // Record the product pull in the 'product_pulls' table
            ProductPull::create([
                'product_id' => $item->id,
                'employee_id' => $userId,
                'quantity' => $item->qty,
                'pulled_at' => now(),
                'status' => 'completed', // Adjust this based on your needs
            ]);
        
            // Optionally, reduce the stock in the products table (if necessary)
            $product = Product::find($item->id);
            $product->decrement('quantity', $item->qty); // Adjust stock based on quantity pulled
        
            $updatedCount++;
        }
        
        // Empty the pull list cart after processing the pulls
        Cart::instance('warehouse_pull')->destroy();
        
        return back()->with('success', "{$updatedCount} product pull(s) confirmed successfully.");
    }
        
    /**
     * Remove a product from the pull list.
     */
    public function removeProductFromPullList($rowId)
    {
        Cart::instance('warehouse_pull')->remove($rowId);
        
        return back()->with('success', 'Product removed from pull list.');
    }

}

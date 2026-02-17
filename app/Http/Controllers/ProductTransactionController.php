<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductTransactionItem;
use App\Models\ProductTransactionType;
use App\Models\ProductTransactionBatch;
use Gloudemans\Shoppingcart\Facades\Cart;

class ProductTransactionController extends Controller
{
    // LIST BATCHES
    public function index()
    {
         $batches = ProductTransactionBatch::with('type')->with('user_created')->get();

         $products = Product::get();

        return view('products.transactions.index', [
            'batches' => $batches,
            'products' => $products,
        ]);
    }


    // SHOW CREATE FORM + CART
    public function create()
    {
        Cart::instance('txn_cart')
            ->destroy();

        return view('products.transactions.form', [
            'txn_cart' => Cart::content(),
            'types' => ProductTransactionType::all(['id', 'name']),
            'products' => Product::with(['category'])->get(),
        ]);
    }

    public function store2(OrderStoreRequest $request)
    {   
        if (!is_numeric($request->customer_id)) {
        $customer = Customer::firstOrCreate(
            ['name' => $request->customer_id],   
            ['name' => $request->customer_id]    
        );
                $request->merge([
                    'customer_id'   => $customer->id,
                    'customer_name' => $customer->name, 
                ]);
        } else {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                $request->merge([
                    'customer_name' => $customer->name
                ]);
            }
        }
        // dd($request->all());
        
        $order = Order::create($request->all());

        // Create Order Details
        $contents = Cart::instance('order')->content();
        $oDetails = [];

        foreach ($contents as $content) {
            $oDetails['order_id'] = $order['id'];
            $oDetails['product_id'] = $content->id;
            $oDetails['quantity'] = $content->qty;
            $oDetails['unit_price'] = $content->price;
            $oDetails['created_at'] = Carbon::now();

            OrderDetails::insert($oDetails);
        }

        // Delete Cart Sopping History
        Cart::destroy();

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order has been created!');
    }


    // STORE FULL TRANSACTION BATCH
    public function store(Request $request)
    {
        $cart = Cart::instance('txn_cart')->content();

        $request->validate([
            'transaction_type_id' => 'required|exists:product_transaction_types,id',
            'note' => 'nullable|string',
        ]);

        if ($cart->isEmpty()) {
            return back()->with('error', 'Cart is empty.');
        }

        // Create batch
        $batch = ProductTransactionBatch::create([
            'transaction_type_id' => $request->transaction_type_id,
            'transaction_date' => Carbon::now(),
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        $type = $request->transaction_type_id;

        foreach ($cart as $item) {
            $product = Product::findOrFail($item->id);
            $oldQty = $product->quantity;
            $change = $item->qty;

            // Determine new quantity based on transaction type
            switch ($type) {
                case 1: // add
                case 3: // transfer_in
                case 6: // returned
                    $newQty = $oldQty + $change;
                    break;

                case 2: // remove
                case 4: // transfer_out
                case 5: // borrowed
                case 7: // free (deduction)
                    $newQty = $oldQty - $change;
                    break;

                default:
                    $newQty = $oldQty; // fallback - no change
                    break;
            }

            // Update stock and log
            $product->update(['quantity' => $newQty, 'updated_by' => auth()->id()]);

            ProductTransactionItem::create([
                'batch_id' => $batch->id,
                'product_id' => $product->id,
                'quantity_change' => $change,
                'before_quantity' => $oldQty,
                'after_quantity' => $newQty,
            ]);
        }

        Cart::destroy();

        return redirect()
            ->route('transactions.show', $batch->id)
            ->with('success', 'Transaction successfully recorded.');
    }



    public function show(ProductTransactionBatch $batch)
    {
        $batch->load(['type', 'user_created', 'items.product']);
    
        return view('products.transactions.show', [
            'transaction' => $batch,
        ]);
    }
}

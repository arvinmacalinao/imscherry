<?php

namespace App\Http\Controllers\Order;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ShopName;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Exports\OrderSummaryExportWarehouse;
use App\Http\Requests\Order\OrderStoreRequest;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('status')->latest()->get();

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function create()
    {
        Cart::instance('order')
            ->destroy();

        return view('orders.create', [
            'carts' => Cart::content(),
            'customers' => Customer::all(['id', 'name']),
            'products' => Product::with(['category'])->get(),
            'shops' => ShopName::with(['platform'])->get(),

        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        // --- Handle Customer ---
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
    
        // --- Generate Invoice Number per Shop ---
        $shop = ShopName::find($request->shop_name_id);
        $prefix = $shop->invoice_prefix; // e.g. SHPPL, LZDPL, HO
        // get last order for this prefix
        $lastOrder = Order::where('invoice_no', 'like', $prefix . '%')
            ->latest()
            ->first();
        // Extract last 6 digits
        $lastSeq = $lastOrder
            ? intval(substr($lastOrder->invoice_no, strlen($prefix)))
            : 0;
        // Increment
        $newSeq = str_pad($lastSeq + 1, 6, '0', STR_PAD_LEFT);
        // Combine prefix + number
        $invoiceNo = $prefix . $newSeq;
        // Add to request
        $request->merge(['invoice_no' => $invoiceNo]);
    
        do {
        $prefix = now()->format('ymd'); // YYMMDD
        $random = strtoupper(Str::random(8));
        $orderNumber = $prefix . $random;
        } while (Order::where('order_number', $orderNumber)->exists());
    
        $request->merge(['order_number' => $orderNumber]);
        // --- Create Order ---
        $order = Order::create($request->all([
            'customer_id',
            'customer_name',
            'order_date',
            'status_id',
            'shop_name_id',
            'payment_method',
            'remarks',
            'total_amount',
            'invoice_no',
            'order_number'
        ]));
    
    
        // --- Create Order Details + Stock Deduction ---
        $cartItems = Cart::instance('order')->content();
    
        foreach ($cartItems as $item) {
        
            // Insert order detail
            OrderDetails::create([
                'order_id'   => $order->id,
                'product_id' => $item->id,
                'quantity'   => $item->qty,
                'unit_price' => $item->price,
            ]);
        
            // // Deduct stock
            // $product = Product::find($item->id);
            // if ($product) {
            //     $product->quantity -= $item->qty;
            //     $product->save();
            // }
        }
    
    
        // --- Clear only the order cart ---
        Cart::instance('order')->destroy();
    
    
        return redirect()
            ->route('orders.index')
            ->with('success', 'Order has been created!');
    }


    public function show(Order $order)
    {
        $statusColors = [
            1 => 'bg-info',      // Imported
            2 => 'bg-info',      // QC Done
            3 => 'bg-success',   // Packed/Shipped
            4 => 'bg-danger',    // Returned
            5 => 'bg-info',      // Invoiced
            6 => 'bg-danger',    // Cancelled
            7 => 'bg-warning',   // Pending
            8 => 'bg-info',      // Picked
        ];

        $order->loadMissing(['customer', 'details'])->get();

        return view('orders.show', [
            'order' => $order,
            'statusColors' => $statusColors
        ]);
    }

    // public function cancel(Order $order)
    // {
    //     $order->update([
    //         'status_id' => 6 //cancelled
    //     ]);

    //     return redirect()->back()->with('success', 'Order has been cancelled.');
    // }

    public function cancel(Request $request, Order $order)
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);
    
        $order->update([
            'status_id' => 6, // cancelled
            'remarks'   => $request->remarks,
        ]);
    
        return redirect()
            ->back()
            ->with('success', 'Order has been cancelled.');
    }

    public function update(Order $order, Request $request)
    {
        // TODO refactoring

        // Reduce the stock
        $products = OrderDetails::where('order_id', $order)->get();
        
        foreach ($products as $product) {
            Product::where('id', $product->product_id)
                ->update(['quantity' => DB::raw('quantity-' . $product->quantity)]);
        }

        return redirect()
            ->route('orders.complete')
            ->with('success', 'Order has been completed!');
    }

    // public function destroy(Order $order)
    // {
    //     $id = Auth::id();
    //     $order->delete();

    //     $user = User::where('id', $id)->first();
    //     if(!$user) {
    //         $request->session()->put('session_msg', 'Record not found!');
    //         return redirect(route('employee.index'));
    //     } else {
    //         $user->deleted_at = Carbon::now();
    //         $user->update();
            
    //         $request->session()->put('session_msg', 'Record deleted!');
    //         return redirect(route('employee.index'));
    //     }
    // }

    

    // public function delete(Order $order)
    // {
    //     dd($order->id);
    //     $test = Order::where('id', $order)->first();
    //     dd($test);
        

    //     $order->delete();
    // }

    public function downloadInvoice($order)
    {
        $order = Order::with(['customer', 'details.product'])
        ->where('id', $order)
        ->firstOrFail();

        $order->update(['status_id' => 5]);

        $pdf = Pdf::loadView('orders.print-invoice-single', compact('order'))
        ->setPaper('letter', 'portrait');

        return $pdf->download('invoice-' . $order->invoice_no . '.pdf');
    }

    public function downloadMultipleInvoices(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No orders selected.');
        }

        $orders = Order::with('details.product')
            ->whereIn('id', $ids)
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'No invoices found for selected orders.');
        }

        Order::whereIn('id', $ids)->update(['status_id' => 5]);

        $pdf = Pdf::loadView('orders.print-invoice-pdf', compact('orders'));            ;

        return $pdf->download('invoices-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportOrderSummary(Request $request)
    {
        $orderIds = $request->input('order_ids'); // array of selected order IDs
    
        if (empty($orderIds)) {
            return back()->with('error', 'Please select at least one order.');
        }
    
        $orders = Order::with('details.product')
            ->whereIn('id', $orderIds)
            ->get();
    
        if ($orders->isEmpty()) {
            return back()->with('error', 'No valid orders found.');
        }
    
        return Excel::download(new OrderSummaryExport($orders), 'order_summary.xlsx');
    }

    public function exportOrderSummaryWarehouse(Request $request)
    {
        $orderIds = $request->input('order_ids'); // array of selected order IDs
    
        if (empty($orderIds)) {
            return back()->with('error', 'Please select at least one order.');
        }
    
        $orders = Order::with('details.product')
            ->whereIn('id', $orderIds)
            ->get();
    
        if ($orders->isEmpty()) {
            return back()->with('error', 'No valid orders found.');
        }
    
        return Excel::download(new OrderSummaryExportWarehouse($orders), 'order_summary.xlsx');
    }

    public function pending(Order $order)
    {
        $order->update([
            'status_id' => 7 //cancelled
        ]);

        return redirect()->back()->with('success', 'Order is on hold.');
    }

}

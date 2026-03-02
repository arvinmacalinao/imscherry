<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ShopName;
use App\Models\ProductPull;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function warehouse(Request $request)
    {
        $products = Product::with('category')->get();

        return view('reports.warehouse', compact('products'));
    }

    public function sales(Request $request)
    {
        $items = OrderDetails::with([
                'order.shopName',
                'product'
            ])
            ->whereHas('order', function ($query) use ($request) {
                $query->where('status_id', 3);

                // Date filter
                if ($request->filled('date_from')) {
                    $query->whereDate('order_date', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    $query->whereDate('order_date', '<=', $request->date_to);
                }

                // Shop filter
                if ($request->filled('shop_id')) {
                    $query->where('shop_name_id', $request->shop_id);
                }

            })

            // Product filter (belongs to order_details)
            ->when($request->filled('product_id'), function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })

            ->latest()
            ->get();

        return view('reports.sales', [
            'items' => $items,
            'shops' => ShopName::all(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function return(Request $request)
    {
        $items = Order::with([
                'shopName',
                'details.product',
                'statusLogs' => function ($q) {
                    $q->where('status_id', 4)->latest();
                }
            ])
            ->where('status_id', 4) // Cancelled orders only

            // -------------------------
            // DATE FILTER (ORDER DATE)
            // -------------------------
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->date_from);
            })

            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->date_to);
            })

            // -------------------------
            // SHOP FILTER
            // -------------------------
            ->when($request->filled('shop_id'), function ($q) use ($request) {
                $q->where('shop_name_id', $request->shop_id);
            })

            ->latest('order_date')
            ->get();

        return view('reports.return', [
            'items' => $items,
            'shops' => ShopName::all(),
        ]);
    }

    public function cancel(Request $request)
    {
        $items = Order::with([
                'shopName',
                'statusLogs' => function ($q) {
                    $q->where('status_id', 4)->latest();
                }
            ])
            ->where('status_id', 6) // Cancelled orders only

            // -------------------------
            // DATE FILTER (ORDER DATE)
            // -------------------------
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->date_from);
            })

            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->date_to);
            })

            // -------------------------
            // SHOP FILTER
            // -------------------------
            ->when($request->filled('shop_id'), function ($q) use ($request) {
                $q->where('shop_name_id', $request->shop_id);
            })

            ->latest('order_date')
            ->get();

        return view('reports.cancel', [
            'items' => $items,
            'shops' => ShopName::all(),
        ]);
    }

    public function customer(Request $request)
    { 
        $customers = Customer::all();
        $orders = Order::with('shopName')->whereNotNull('customer_name')->get();

            // Attach orders to each customer
            foreach ($customers as $customer) {
                $customer->orders = $orders->where('customer_name', $customer->name);
            }
        return view('reports.customer', compact('customers'));
    }

    public function export_warehouse(Request $request)
    {
        $products = Product::with('category')->get();
    
        return Excel::download(new \App\Exports\WarehouseExport($products), 'warehouse_report.xlsx');
    }

    public function export_sales(Request $request)
    {
        $orders = Order::with('shopName')
            ->where('status_id', 3)   // same filter as your page
            ->get();

        return Excel::download(new \App\Exports\SalesExport($orders), 'sales_report.xlsx');
    }

    public function export_customer(Request $request)
    {
        $customers = Customer::all();
        $orders = Order::whereNotNull('customer_name')->get();
    
        foreach ($customers as $customer) {
            $customer->orders = $orders->where('customer_name', $customer->name);
        }
    
        return Excel::download(
            new \App\Exports\CustomerExport($customers),
            'customer_report.xlsx'
        );
    }

    // public function categories(Request $request)
    // {
    //     $orders = Order::with('shopName')->latest()->get();
    //     return view('reports.categories', [
    //         'orders' => $orders,
    //     ]);
    // }
    public function categories()
    {
        $categories = DB::table('categories')
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'order_details.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->select(
                'categories.id',
                'categories.name',
    
                DB::raw("SUM(CASE WHEN orders.status_id = 3 THEN order_details.quantity ELSE 0 END) as sales"),
                DB::raw("SUM(CASE WHEN orders.status_id = 4 THEN order_details.quantity ELSE 0 END) as returns"),
                DB::raw("SUM(CASE WHEN orders.status_id = 6 THEN order_details.quantity ELSE 0 END) as cancelled")
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->get();
    
        return view('reports.categories', compact('categories'));
    }
}

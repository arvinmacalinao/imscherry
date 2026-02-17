<?php

namespace App\Http\Controllers\Dashboards;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::count();
        $completedOrders = Order::where('status_id', 3)
            ->count();

        $currentMonth = Carbon::now()->format('Y-m'); // e.g., "2025-09"
        $totalSales = Order::where('order_date', 'like', $currentMonth . '%')->where('status_id', 3) // matches "2025-09-01", etc.
        ->sum('total');

        $returnedOrders = Order::where('status_id', 4)->where('updated_at', 'like', $currentMonth . '%')
            ->count();

        $products = Product::count();

        $categories = Category::count();

        return view('dashboard', [
            'products' => $products,
            'orders' => $orders,
            'completedOrders' => $completedOrders,
            'categories' => $categories,
            'totalSales' =>$totalSales,
            'returnedOrders' => $returnedOrders,
        ]);
    }
}

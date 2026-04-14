<?php

namespace App\Http\Controllers\Dashboards;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // -------------------------
        // PERIOD FILTER (default = month)
        // -------------------------
        $period = $request->get('period', 'month');

        switch ($period) {

            case 'today':
                $start = Carbon::today();
                $end   = Carbon::today()->endOfDay();
                break;

            case 'custom':
                $start = Carbon::parse($request->start_date ?? Carbon::today());
                $end   = Carbon::parse($request->end_date ?? Carbon::today())->endOfDay();
                break;

            default: // month
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfMonth();
        }

        // -------------------------
        // ORDERS (THIS PERIOD)
        // -------------------------
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->count();

        // -------------------------
        // COMPLETED / SHIPPED
        // -------------------------
        $completedOrders = Order::where('status_id', 3)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // -------------------------
        // TOTAL SALES (THIS PERIOD)
        // -------------------------
        $totalSales = Order::where('status_id', 3)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        // -------------------------
        // PARCEL RETURNED (THIS PERIOD)
        // -------------------------
        $returnedOrders = Order::where('status_id', 4)
            ->whereBetween('updated_at', [$start, $end])
            ->count();

         // -------------------------
        // PENDING (status_id = 8)
        // -------------------------
        $pendingOrders = Order::where('status_id', 8)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // -------------------------
        // FOR CLAIMS (status_id = 9)
        // -------------------------
        $forClaimsOrders = Order::where('status_id', 9)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // -------------------------
        // REFUNDED (status_id = 10)
        // -------------------------
        $refundedOrders = Order::where('status_id', 10)
            ->whereBetween('updated_at', [$start, $end])
            ->count();


        // -------------------------
        // STATIC COUNTS
        // -------------------------
        $products   = Product::count();
        $categories = Category::count();

        return view('dashboard', compact(
            'products',
            'orders',
            'completedOrders',
            'pendingOrders',
            'forClaimsOrders',
            'refundedOrders',
            'categories',
            'totalSales',
            'returnedOrders',
            'period',
            'start',
            'end'
        ));
    }
}

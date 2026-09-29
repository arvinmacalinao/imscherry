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
        // returned orders, including those whose items were since restocked / claimed / refunded
        $returnedOrders = Order::whereIn('status_id', \App\Reports\ReportStatus::RETURNED_ORDER)
            ->whereBetween('updated_at', [$start, $end])
            ->count();

         // -------------------------
        // PENDING (status_id = 7)
        // -------------------------
        $pendingOrders = Order::where('status_id', 7)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // -------------------------
        // FOR CLAIMS (status_id = 10)
        // -------------------------
        $forClaimsOrders = Order::where('status_id', 10)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // -------------------------
        // REFUNDED (status_id = 11)
        // -------------------------
        $refundedOrders = Order::where('status_id', 11)
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

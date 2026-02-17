<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScanLogController extends Controller
{
    // public function index()
    // {
    //     $userId = Auth::id();

    //     // get unique orders the user scanned
    //     $logs = ScanLog::with(['order', 'toStatus'])
    //         ->where('user_id', $userId)
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->groupBy('order_id'); // group by order

    //     return view('scanlogs.my', [
    //         'logs' => $logs
    //     ]);
    // }
}

<?php

namespace App\Http\Controllers;

use App\Exports\CancellationExport;
use App\Exports\CategoryReportExport;
use App\Exports\CustomerExport;
use App\Exports\ReturnExport;
use App\Exports\SalesExport;
use App\Exports\WarehouseExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Report pages and their Excel exports.
 *
 * Each page is a Livewire table (App\Livewire\Tables\Report\*) and each export is built by the
 * same report class (App\Reports\*) from the same filters, which the page puts in the export URL.
 */
class ReportController extends Controller
{
    /** Filters every report understands; each report ignores the ones it does not use */
    private const FILTERS = [
        'search', 'date_from', 'date_to', 'shop_id', 'platform_id', 'category_id', 'brand',
        'picked', 'outcome', 'type', 'direction',
    ];

    private function filters(Request $request): array
    {
        return array_filter($request->only(self::FILTERS), fn ($v) => $v !== null && $v !== '');
    }

    private function download($export, string $name)
    {
        return Excel::download($export, $name . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function warehouse()
    {
        return view('reports.warehouse');
    }

    public function sales()
    {
        return view('reports.sales');
    }

    public function return()
    {
        return view('reports.return');
    }

    public function cancel()
    {
        return view('reports.cancel');
    }

    public function customer()
    {
        return view('reports.customer');
    }

    public function categories()
    {
        return view('reports.categories');
    }

    public function export_warehouse(Request $request)
    {
        return $this->download(new WarehouseExport($this->filters($request)), 'warehouse_report');
    }

    public function export_sales(Request $request)
    {
        return $this->download(
            new SalesExport($this->filters($request), $request->input('group_by', 'brand')),
            'sales_report'
        );
    }

    public function export_customer(Request $request)
    {
        return $this->download(new CustomerExport($this->filters($request)), 'customer_report');
    }

    public function export_cancel(Request $request)
    {
        return $this->download(new CancellationExport($this->filters($request)), 'cancellation_report');
    }

    public function export_return(Request $request)
    {
        return $this->download(new ReturnExport($this->filters($request)), 'returned_report');
    }

    public function export_categories(Request $request)
    {
        return $this->download(new CategoryReportExport($this->filters($request)), 'category_report');
    }
}

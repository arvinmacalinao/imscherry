<?php

namespace App\Livewire\Tables\Report;

use App\Livewire\Tables\Report\Concerns\ReportFilters;
use App\Reports\SalesReport;
use Livewire\Attributes\Url;
use Livewire\Component;

class SalesTable extends Component
{
    use ReportFilters;

    #[Url(except: 'brand')]
    public $groupBy = 'brand';

    public $sortField = 'order_date';

    public function render()
    {
        $report = new SalesReport($this->filters());
        $summary = $report->summary($this->groupBy);

        return view('livewire.tables.report.sales-table', [
            'lines' => $report->lines()
                ->orderBy(SalesReport::sortColumn($this->sortField), $this->sortAsc ? 'asc' : 'desc')
                ->orderByDesc('order_details.id')
                ->paginate($this->perPage),
            'summary'    => $summary,
            'grandQty'   => $summary->sum('qty'),
            'grandTotal' => $summary->sum('total'),
            'groupLabel' => (SalesReport::GROUPS[$this->groupBy] ?? SalesReport::GROUPS['brand'])[0],
            // lines imported without a platform price are counted as 0 - show how many so they get fixed
            'missingPrice' => $report->lines()->whereNull('order_details.unit_price')->count(),
            'exportUrl'  => route('sales.export', $this->filters() + ['group_by' => $this->groupBy]),
        ] + $this->filterOptions());
    }
}

<?php

namespace App\Livewire\Tables\Report;

use App\Livewire\Tables\Report\Concerns\ReportFilters;
use App\Reports\CustomerReport;
use Livewire\Component;

class CustomerReportTable extends Component
{
    use ReportFilters;

    public $sortField = 'value';

    public function render()
    {
        $report = new CustomerReport($this->filters());

        return view('livewire.tables.report.customer-report-table', [
            'customers' => $report->customers()
                ->orderBy(CustomerReport::sortColumn($this->sortField), $this->sortAsc ? 'asc' : 'desc')
                ->orderBy('customer')
                ->paginate($this->perPage),
            'summary'   => $report->summary(),
            'exportUrl' => route('customer.export', $this->filters()),
        ] + $this->filterOptions());
    }
}

<?php

namespace App\Livewire\Tables\Report;

use App\Livewire\Tables\Report\Concerns\ReportFilters;
use App\Reports\CategoryReport;
use Livewire\Component;

class CategoryReportTable extends Component
{
    use ReportFilters;

    public $sortField = 'category';

    public function render()
    {
        $rows = (new CategoryReport($this->filters()))->rows();

        return view('livewire.tables.report.category-report-table', [
            'brandGroups' => $rows->groupBy('brand'),
            'grand'       => CategoryReport::totals($rows),
            'exportUrl'   => route('categories.export', $this->filters()),
        ] + $this->filterOptions());
    }
}

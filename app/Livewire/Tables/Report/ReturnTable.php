<?php

namespace App\Livewire\Tables\Report;

use App\Livewire\Tables\Report\Concerns\ReportFilters;
use App\Reports\ReturnReport;
use Livewire\Attributes\Url;
use Livewire\Component;

class ReturnTable extends Component
{
    use ReportFilters {
        filters as baseFilters;
        resetFilters as baseResetFilters;
    }

    // '' = all returned items, 0 = nobody has acted on them yet, 9-12 = item status
    #[Url(except: '')]
    public $outcome = '';

    public $sortField = 'returned_at';

    public function filters(): array
    {
        return $this->baseFilters() + ($this->outcome !== '' ? ['outcome' => $this->outcome] : []);
    }

    public function resetFilters(): void
    {
        $this->baseResetFilters();
        $this->reset('outcome');
    }

    public function render()
    {
        $report = new ReturnReport($this->filters());

        return view('livewire.tables.report.return-table', [
            'lines' => $report->lines()
                ->orderBy(ReturnReport::sortColumn($this->sortField), $this->sortAsc ? 'asc' : 'desc')
                ->orderByDesc('order_details.id')
                ->paginate($this->perPage),
            'summary'   => $report->summary(),
            'exportUrl' => route('return.export', $this->filters()),
        ] + $this->filterOptions());
    }
}

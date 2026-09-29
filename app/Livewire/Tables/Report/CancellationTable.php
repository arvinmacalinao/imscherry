<?php

namespace App\Livewire\Tables\Report;

use App\Livewire\Tables\Report\Concerns\ReportFilters;
use App\Reports\CancellationReport;
use Livewire\Attributes\Url;
use Livewire\Component;

class CancellationTable extends Component
{
    use ReportFilters {
        filters as baseFilters;
        resetFilters as baseResetFilters;
    }

    // '' = all cancelled orders, 'yes' = only those whose stock had already been picked
    #[Url(except: '')]
    public $picked = '';

    public $sortField = 'event_at';

    public function filters(): array
    {
        return $this->baseFilters() + array_filter(['picked' => $this->picked]);
    }

    public function resetFilters(): void
    {
        $this->baseResetFilters();
        $this->reset('picked');
    }

    public function render()
    {
        $report = new CancellationReport($this->filters());

        return view('livewire.tables.report.cancellation-table', [
            'orders' => $report->orders()
                ->orderBy(CancellationReport::sortColumn($this->sortField), $this->sortAsc ? 'asc' : 'desc')
                ->orderByDesc('orders.id')
                ->paginate($this->perPage),
            'summary'   => $report->summary(),
            'exportUrl' => route('cancel.export', $this->filters()),
        ] + $this->filterOptions());
    }
}

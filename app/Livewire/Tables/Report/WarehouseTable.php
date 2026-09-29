<?php

namespace App\Livewire\Tables\Report;

use App\Livewire\Tables\Report\Concerns\ReportFilters;
use App\Reports\StockMovementReport;
use Livewire\Attributes\Url;
use Livewire\Component;

class WarehouseTable extends Component
{
    use ReportFilters {
        filters as baseFilters;
        resetFilters as baseResetFilters;
        updated as baseUpdated;
    }

    #[Url(except: '')]
    public $type = '';

    // '' = in and out, 'in' = stock coming in, 'out' = stock going out
    #[Url(except: '')]
    public $direction = '';

    public $sortField = 'moved_at';

    public $productSort = 'qty_out';

    public $productSortAsc = false;

    public function updated($property): void
    {
        $this->baseUpdated($property);
        $this->resetPage('productsPage');
    }

    public function sortProductsBy($field): void
    {
        $this->productSortAsc = $this->productSort === $field ? ! $this->productSortAsc : false;
        $this->productSort = $field;
    }

    public function filters(): array
    {
        return $this->baseFilters() + array_filter(['type' => $this->type, 'direction' => $this->direction]);
    }

    public function resetFilters(): void
    {
        $this->baseResetFilters();
        $this->reset(['type', 'direction']);
        $this->resetPage('productsPage');
    }

    public function render()
    {
        $report = new StockMovementReport($this->filters());

        return view('livewire.tables.report.warehouse-table', [
            'products' => $report->products()
                ->orderBy(StockMovementReport::productSortColumn($this->productSort), $this->productSortAsc ? 'asc' : 'desc')
                ->orderBy('mv.product_name')
                ->paginate(10, pageName: 'productsPage'),
            'movements' => $report->movements()
                ->orderBy(StockMovementReport::sortColumn($this->sortField), $this->sortAsc ? 'asc' : 'desc')
                ->orderByDesc('m.moved_at')
                ->paginate($this->perPage),
            'byType'    => $report->byType(),
            'types'     => StockMovementReport::TYPES,
            'exportUrl' => route('warehouse.export', $this->filters()),
        ] + $this->filterOptions());
    }
}

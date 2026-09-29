<?php

namespace App\Livewire\Tables\Report\Concerns;

use App\Models\Category;
use App\Models\Platform;
use App\Models\ShopName;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Filters, search, sorting and paging shared by every report table.
 * The filters are kept in the URL, so a filtered report can be bookmarked or shared.
 */
trait ReportFilters
{
    use WithPagination;

    public $perPage = 25;

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $date_from = '';

    #[Url(except: '')]
    public $date_to = '';

    #[Url(except: '')]
    public $shop_id = '';

    #[Url(except: '')]
    public $platform_id = '';

    #[Url(except: '')]
    public $category_id = '';

    #[Url(except: '')]
    public $brand = '';

    public $sortAsc = false;

    // any filter change starts again on page 1 (switching the summary grouping does not)
    public function updated($property): void
    {
        if ($property !== 'groupBy') {
            $this->resetPage();
        }
    }

    public function sortBy($field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'date_from', 'date_to', 'shop_id', 'platform_id', 'category_id', 'brand']);
        $this->resetPage();
    }

    /** The filters that are set, as passed to the report class and the export URL */
    public function filters(): array
    {
        return array_filter([
            'search'      => $this->search,
            'date_from'   => $this->date_from,
            'date_to'     => $this->date_to,
            'shop_id'     => $this->shop_id,
            'platform_id' => $this->platform_id,
            'category_id' => $this->category_id,
            'brand'       => $this->brand,
        ], fn ($v) => $v !== '' && $v !== null);
    }

    /** Options for the filter dropdowns */
    protected function filterOptions(): array
    {
        return [
            'shops'      => ShopName::orderBy('name')->get(),
            'platforms'  => Platform::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'brands'     => [...Category::BRANDS, 'Other'],
        ];
    }
}

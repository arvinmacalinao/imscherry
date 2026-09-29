{{-- "Show N entries" + search box above a report table. $placeholder describes what search matches. --}}
<div class="card-body border-bottom py-3">
    <div class="d-flex flex-wrap gap-2">
        <div class="text-secondary">
            Show
            <div class="mx-2 d-inline-block">
                <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="result per page">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            entries
        </div>
        <div class="ms-auto text-secondary">
            Search:
            <div class="ms-2 d-inline-block">
                <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm"
                    placeholder="{{ $placeholder ?? '' }}" aria-label="Search">
            </div>
        </div>
    </div>
</div>

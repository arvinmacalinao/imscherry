{{--
    Filter bar shared by every report.
    $show      : which filters to show - any of 'dates', 'brand', 'category', 'shop', 'platform'
    $dateLabel : what the date range filters on (e.g. "Order date", "Cancelled on")
    $exportUrl : Excel download with the current filters
    $extra     : optional view with report-specific filter columns
--}}
@php($show = $show ?? ['dates', 'brand', 'category', 'shop', 'platform'])
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            @if (in_array('dates', $show))
                <div class="col-6 col-md-2">
                    <label class="form-label">{{ __('From') }} <span class="text-secondary small">({{ $dateLabel ?? __('Order date') }})</span></label>
                    <input type="date" wire:model.live="date_from" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">{{ __('To') }}</label>
                    <input type="date" wire:model.live="date_to" class="form-control form-control-sm">
                </div>
            @endif
            @if (in_array('brand', $show))
                <div class="col-6 col-md">
                    <label class="form-label">{{ __('Brand') }}</label>
                    <select wire:model.live="brand" class="form-select form-select-sm">
                        <option value="">{{ __('All Brands') }}</option>
                        @foreach ($brands as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (in_array('category', $show))
                <div class="col-6 col-md">
                    <label class="form-label">{{ __('Category') }}</label>
                    <select wire:model.live="category_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (in_array('shop', $show))
                <div class="col-6 col-md">
                    <label class="form-label">{{ __('Shop') }}</label>
                    <select wire:model.live="shop_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Shops') }}</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (in_array('platform', $show))
                <div class="col-6 col-md">
                    <label class="form-label">{{ __('Platform') }}</label>
                    <select wire:model.live="platform_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Platforms') }}</option>
                        @foreach ($platforms as $platform)
                            <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @isset($extra)
                @include($extra)
            @endisset
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-outline-secondary">
                    {{ __('Clear') }}
                </button>
                <a href="{{ $exportUrl }}" class="btn btn-sm btn-success" title="{{ __('Download the filtered report as Excel') }}">
                    {{ __('Excel') }}
                </a>
            </div>
        </div>
    </div>
</div>

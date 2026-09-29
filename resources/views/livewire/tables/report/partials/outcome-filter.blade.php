<div class="col-6 col-md">
    <label class="form-label">{{ __('Item Status') }}</label>
    <select wire:model.live="outcome" class="form-select form-select-sm">
        <option value="">{{ __('All returned items') }}</option>
        @foreach (\App\Reports\ReportStatus::ITEM_OUTCOMES as $key => $label)
            <option value="{{ $key === 'pending' ? 0 : $key }}">{{ __($label) }}</option>
        @endforeach
    </select>
</div>

<div class="col-6 col-md">
    <label class="form-label">{{ __('Movement') }}</label>
    <select wire:model.live="type" class="form-select form-select-sm">
        <option value="">{{ __('All movements') }}</option>
        @foreach ($types as $key => $label)
            <option value="{{ $key }}">{{ __($label) }}</option>
        @endforeach
    </select>
</div>
<div class="col-6 col-md">
    <label class="form-label">{{ __('In / Out') }}</label>
    <select wire:model.live="direction" class="form-select form-select-sm">
        <option value="">{{ __('In and out') }}</option>
        <option value="in">{{ __('Coming in') }}</option>
        <option value="out">{{ __('Going out') }}</option>
    </select>
</div>

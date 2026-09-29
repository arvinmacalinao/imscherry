<div class="col-6 col-md">
    <label class="form-label">{{ __('Stock') }}</label>
    <select wire:model.live="picked" class="form-select form-select-sm">
        <option value="">{{ __('All cancelled orders') }}</option>
        <option value="yes">{{ __('Picked, not put back') }}</option>
    </select>
</div>

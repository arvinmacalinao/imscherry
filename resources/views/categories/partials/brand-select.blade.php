<div class="mb-3">
    <label for="brand" class="form-label">{{ __('Brand') }}</label>
    <select id="brand" name="brand" class="form-select @error('brand') is-invalid @enderror">
        <option value="">{{ __('Other') }}</option>
        @foreach (\App\Models\Category::BRANDS as $brand)
            <option value="{{ $brand }}" @selected($selected === $brand)>{{ $brand }}</option>
        @endforeach
    </select>
    <small class="form-hint">{{ __('Used to group the sales and category reports.') }}</small>
    @error('brand')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center mb-3">
            <div class="col">
                <h2 class="page-title">
                    {{ __('Create Product') }}
                </h2>
            </div>
        </div>

        @include('partials._breadcrumbs')
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <x-alert/>

        <div class="row row-cards">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
        
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">
                                        {{ __('Product Create') }}
                                    </h3>
                                </div>

                                <div class="card-actions">
                                    <a href="{{ route('products.index') }}" class="btn-action">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M18 6l-12 12"></path><path d="M6 6l12 12"></path></svg>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row row-cards">
                                    <div class="col-md-12">
                                        <x-input name="name"
                                                 id="name"
                                                 placeholder="Product name"
                                                 value="{{ old('name') }}"
                                        />
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">
                                                Product Category
                                                <span class="text-danger">*</span>
                                            </label>

                                            @if ($categories->count() === 1)
                                                <select name="category_id" id="category_id"
                                                        class="form-select @error('category_id') is-invalid @enderror"
                                                        readonly
                                                >
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" selected>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <select name="category_id" id="category_id"
                                                        class="form-select @error('category_id') is-invalid @enderror"
                                                >
                                                    <option selected="" disabled="">
                                                        Select a category:
                                                    </option>

                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" @if(old('category_id') == $category->id) selected="selected" @endif>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif

                                            @error('category_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <x-input type="number"
                                                 label="Selling Price"
                                                 name="price"
                                                 id="price"
                                                 placeholder="0"
                                                 value="{{ old('price') }}"
                                        />
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <x-input type="number"
                                                 label="Quantity"
                                                 name="quantity"
                                                 id="quantity"
                                                 placeholder="0"
                                                 value="{{ old('quantity') }}"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <x-button.save type="submit">
                                    {{ __('Save') }}
                                </x-button.save>

                                <x-button.back route="{{ route('products.index') }}">
                                    {{ __('Cancel') }}
                                </x-button.back>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce

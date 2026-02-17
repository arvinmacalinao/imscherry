@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center mb-3">
            <div class="col">
                <h2 class="page-title">
                    {{ __('Edit Product') }}
                </h2>
            </div>
        </div>

        @include('partials._breadcrumbs', ['model' => $product])
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="row">
                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Product Details') }}
                                </h3>

                                <div class="row row-cards">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                {{ __('Name') }}
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text"
                                                   id="name"
                                                   name="name"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   placeholder="Product name"
                                                   value="{{ old('name', $product->name) }}"
                                            >

                                            @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">
                                                Product category
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select name="category_id" id="category_id"
                                                    class="form-select @error('category_id') is-invalid @enderror"
                                            >
                                                <option selected="" disabled="">Select a category:</option>
                                                @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @if(old('category_id', $product->category_id) == $category->id) selected="selected" @endif>{{ $category->name }}</option>
                                                @endforeach
                                            </select>

                                            @error('category_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="price">
                                                Price
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text"
                                                   id="price"
                                                   name="price"
                                                   class="form-control @error('price') is-invalid @enderror"
                                                   placeholder="0"
                                                   value="{{ old('price', $product->price) }}"
                                            >

                                            @error('price')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">
                                                {{ __('Quantity') }}
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number"
                                                   id="quantity"
                                                   name="quantity"
                                                   class="form-control @error('quantity') is-invalid @enderror"
                                                   min="0"
                                                   value="{{ old('quantity', $product->quantity) }}"
                                                   placeholder="0"
                                            >

                                            @error('quantity')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <x-button.save type="submit">
                                    {{ __('Update') }}
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

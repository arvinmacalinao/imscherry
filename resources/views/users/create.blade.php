@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center mb-3">
            <div class="col">
                <h2 class="page-title">
                    {{ __('Create User') }}
                </h2>
            </div>
        </div>

        @include('partials._breadcrumbs')
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">

            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('User Details') }}
                                </h3>

                                <div class="row row-cards">
                                    <div class="col-md-12">

                                        <x-input name="name" :value="old('name')" required="true"/>

                                        <x-input name="email" :value="old('email')" required="true"/>

                                        <x-input name="username" :value="old('username')" required="true"/>

                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <x-input type="password" name="password"/>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <x-input type="password" name="password_confirmation" label="Password Confirmation"/>
                                    </div>
                                </div>
                            <hr>
                            <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select name="role_id" id="role" class="form-select">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <x-button.save type="submit">
                                    {{ __('Save') }}
                                </x-button.save>

                                <x-button.back route="{{ route('users.index') }}">
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

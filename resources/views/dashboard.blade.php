@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <x-alert/>
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Overview</div>
                <h2 class="page-title">Dashboard</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
<div class="container-xl">

    {{-- =========================
        PERIOD FILTER
    ========================== --}}
    <div class="mb-3">
        <form method="GET" class="d-flex gap-2 align-items-end">

            <div>
                <label class="form-label">Period</label>
                <select name="period" class="form-select" onchange="this.form.submit()">
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>

            @if($period === 'custom')
                <div>
                    <label class="form-label">Start</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ request('start_date') }}">
                </div>

                <div>
                    <label class="form-label">End</label>
                    <input type="date" name="end_date" class="form-control"
                           value="{{ request('end_date') }}">
                </div>

                <div>
                    <button class="btn btn-primary">Apply</button>
                </div>
            @endif

        </form>
    </div>

    {{-- =========================
        DASHBOARD CARDS
    ========================== --}}
    <div class="row row-deck row-cards">

        {{-- PRODUCTS --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="font-weight-medium">
                        {{ $products }} Products
                    </div>
                    <div class="text-muted">
                        {{ $categories }} categories
                    </div>
                </div>
            </div>
        </div>

        {{-- ORDERS --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="font-weight-medium">
                        {{ $orders }} Orders
                    </div>
                    <div class="text-muted">
                        {{ $completedOrders }} shipped this {{ $period }}
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTAL SALES --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="font-weight-medium">
                        Total Sales
                    </div>
                    <div class="text-muted">
                        ₱{{ number_format($totalSales, 2) }} this {{ $period }}
                    </div>
                </div>
            </div>
        </div>

        {{-- PARCEL RETURNED --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="font-weight-medium">
                        Parcel Returned
                    </div>
                    <div class="text-muted">
                        {{ $returnedOrders }} this {{ $period }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
@endsection

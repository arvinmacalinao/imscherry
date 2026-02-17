@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">

        <h2 class="mb-4">Category Report</h2>

        @if($categories->isEmpty())
            <x-empty title="No category data found" />
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Sales</th>
                            <th>Returns</th>
                            <th>Cancelled</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ number_format($category->sales) }}</td>
                                <td>{{ number_format($category->returns) }}</td>
                                <td>{{ number_format($category->cancelled) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
@endsection

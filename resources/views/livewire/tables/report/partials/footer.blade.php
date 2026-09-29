{{-- "Showing x to y of z" + pagination for $rows (a paginator) --}}
<div class="card-footer d-flex align-items-center">
    <p class="m-0 text-secondary">
        Showing <span>{{ $rows->firstItem() ?? 0 }}</span>
        to <span>{{ $rows->lastItem() ?? 0 }}</span> of <span>{{ $rows->total() }}</span> entries
    </p>

    <ul class="pagination m-0 ms-auto">
        {{ $rows->links() }}
    </ul>
</div>

{{-- Sortable column header: $field (sort key), $title, $class --}}
<th scope="col" class="{{ $class ?? '' }}">
    <a wire:click.prevent="sortBy('{{ $field }}')" href="#" role="button">
        {{ __($title) }}
        @include('inclues._sort-icon', ['field' => $field])
    </a>
</th>

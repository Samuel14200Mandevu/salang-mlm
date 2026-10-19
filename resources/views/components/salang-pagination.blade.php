@props([
    'paginator',
])

@if ($paginator->hasPages())
    <div {{ $attributes->class(['salang-pagination-wrap']) }}>
        {{ $paginator->withQueryString()->onEachSide(1)->links() }}
    </div>
@endif

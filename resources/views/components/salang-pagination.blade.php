@props([
    'paginator',
])

@if ($paginator->hasPages())
    <div {{ $attributes->class(['salang-pagination-wrap']) }}>
        {{ $paginator->withQueryString()->links() }}
    </div>
@endif

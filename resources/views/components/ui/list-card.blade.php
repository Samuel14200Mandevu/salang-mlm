@props([
    'title',
    'subtitle' => null,
    'status' => null,
    'amount' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge([
        'class' => 'flex flex-col gap-2 rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 shadow-[var(--shadow-sm)]',
    ]) }}
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-[var(--text-primary)]">{{ $title }}</p>
            @if ($subtitle)
                <p class="mt-0.5 truncate text-xs text-[var(--text-secondary)]">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($amount !== null && $amount !== '')
            <p class="shrink-0 text-sm font-bold text-[var(--primary)]">{{ $amount }}</p>
        @endif
    </div>

    @if ($status || isset($actions))
        <div class="flex flex-wrap items-center justify-between gap-2">
            @if ($status)
                <div class="text-xs text-[var(--text-secondary)]">
                    {{ $status }}
                </div>
            @else
                <span></span>
            @endif

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif
</{{ $tag }}>

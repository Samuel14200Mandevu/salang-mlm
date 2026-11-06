@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 px-6 py-12 text-center']) }}>
    @isset($icon)
        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--primary)] shadow-[var(--shadow-sm)]">
            {{ $icon }}
        </div>
    @endisset

    <div class="max-w-sm space-y-1">
        <p class="text-base font-semibold text-[var(--text-primary)]">{{ $title }}</p>
        @if ($description)
            <p class="text-sm text-[var(--text-secondary)]">{{ $description }}</p>
        @endif
    </div>

    @isset($action)
        <div class="mt-2">
            {{ $action }}
        </div>
    @endisset
</div>

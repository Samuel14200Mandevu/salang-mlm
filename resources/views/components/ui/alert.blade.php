@props(['type' => 'info', 'title' => null])

@php
$classes = match ($type) {
    'info' => 'bg-neutral-100 border-neutral-300 text-neutral-800',
    'success' => 'bg-primary-50 border-primary-200 text-primary-900',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-900',
    'danger' => 'bg-red-50 border-red-200 text-red-800',
    default => 'bg-neutral-100 border-neutral-300 text-neutral-800',
};
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg p-4 {$classes}", 'role' => 'alert']) }}>
    @if ($title)
        <p class="font-semibold mb-1">{{ $title }}</p>
    @endif
    <div class="text-sm">{{ $slot }}</div>
</div>

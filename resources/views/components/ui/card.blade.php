@props(['padding' => 'p-5'])

<div {{ $attributes->merge(['class' => "bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)] shadow-[var(--shadow-sm)] {$padding}"]) }}>
    {{ $slot }}
</div>

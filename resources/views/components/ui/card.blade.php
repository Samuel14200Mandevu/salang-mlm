@props(['padding' => 'p-6'])

<div {{ $attributes->merge(['class' => "bg-neutral-50 border border-neutral-200 rounded-lg shadow-sm {$padding}"]) }}>
    {{ $slot }}
</div>

@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
$classes = match ($variant) {
    'primary' => 'bg-primary-600 text-white hover:bg-primary-700',
    'secondary' => 'bg-neutral-100 text-neutral-900 hover:bg-neutral-200',
    'danger' => 'bg-red-600 text-white hover:bg-red-700',
    'ghost' => 'text-neutral-700 hover:bg-neutral-100',
    default => 'bg-primary-600 text-white hover:bg-primary-700',
};

$sizes = match ($size) {
    'sm' => 'px-3 py-1.5 text-sm min-h-[44px]',
    'md' => 'px-4 py-2 text-sm min-h-[44px]',
    'lg' => 'px-6 py-3 text-base min-h-[44px]',
    default => 'px-4 py-2 text-sm min-h-[44px]',
};
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => "inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {$classes} {$sizes}"]) }}>
    {{ $slot }}
</button>

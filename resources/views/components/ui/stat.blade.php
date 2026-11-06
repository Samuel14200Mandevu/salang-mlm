{{--
  KPI card. Pour changer le padding, passer padding="p-3" (prop de x-ui.card) —
  ne pas override via class="p-*" (merge Tailwind non fiable sur le défaut p-5).
--}}
@props([
    'title' => null,
    'label' => null,
    'value',
    'trend' => null,
    'trendPositive' => null,
    'padding' => 'p-5',
])

@php
    $titleText = $title ?? $label ?? '';
    $isPositive = $trendPositive;
    if ($isPositive === null && is_string($trend)) {
        $isPositive = ! str_starts_with(ltrim($trend), '-');
    }
@endphp

<x-ui.card :padding="$padding" {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>
    <p class="text-sm text-[var(--text-secondary)]">{{ $titleText }}</p>

    <p class="text-3xl font-bold text-[var(--primary)] leading-tight">{{ $value }}</p>

    @if ($trend !== null && $trend !== '')
        <span @class([
            'inline-flex w-fit items-center rounded-md px-2 py-0.5 text-xs font-semibold',
            'text-[var(--success)] bg-[var(--success-light)]' => $isPositive,
            'text-[var(--danger)] bg-[var(--danger-light)]' => ! $isPositive,
        ])>
            {{ $trend }}
        </span>
    @endif

    @isset($chart)
        <div class="mt-2 min-h-0 w-full">
            {{ $chart }}
        </div>
    @endisset
</x-ui.card>

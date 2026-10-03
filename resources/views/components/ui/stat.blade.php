@props(['label', 'value', 'change' => null])

<div class="bg-neutral-50 border border-neutral-200 rounded-lg p-5 shadow-sm">
    <p class="text-sm text-neutral-600">{{ $label }}</p>
    <p class="mt-1 text-2xl font-semibold text-neutral-900">{{ $value }}</p>
    @if ($change)
        <p class="mt-1 text-xs text-neutral-500">{{ $change }}</p>
    @endif
    @if (isset($icon))
        <div class="mt-2 text-neutral-400">{{ $icon }}</div>
    @endif
</div>

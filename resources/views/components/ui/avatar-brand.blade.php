{{--
  Avatar rond — image ou initiales (gradient primary).

  Usage :
  <x-ui.avatar-brand name="Samuel Mandevu" size="md" />
  <x-ui.avatar-brand name="Samuel" src="{{ asset('images/avatar.jpg') }}" size="lg" />
  <x-ui.avatar-brand name="Ada" color="#184F94" size="sm" />
--}}
@props([
    'name',
    'src' => null,
    'size' => 'md',
    'color' => null,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-14 w-14 text-base',
        default => 'h-10 w-10 text-sm',
    };

    $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
    $initials = '';
    foreach (array_slice(array_filter($parts), 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    if ($initials === '') {
        $initials = '?';
    }

    // Hash → 4 bleus Salang uniquement (aucun vert / rouge / orange)
    $bluePalette = [
        '#1E5DAD', // --primary / primary-500
        '#184F94', // --primary-600
        '#134178', // --primary-700
        '#3B82F6', // bleu clair (4e teinte)
    ];
    $resolvedColor = $color;
    if ($resolvedColor === null || $resolvedColor === '') {
        $resolvedColor = $bluePalette[abs(crc32((string) $name)) % count($bluePalette)];
    }

    $bgStyle = 'background: ' . $resolvedColor;
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $name }}"
        title="{{ $name }}"
        {{ $attributes->merge([
            'class' => "{$sizeClass} shrink-0 rounded-full object-cover border border-[var(--border-color)] shadow-[var(--shadow-sm)]",
        ]) }}
    >
@else
    <span
        title="{{ $name }}"
        role="img"
        aria-label="{{ $name }}"
        style="{{ $bgStyle }}"
        {{ $attributes->merge([
            'class' => "{$sizeClass} inline-flex shrink-0 items-center justify-center rounded-full font-bold text-white shadow-[var(--shadow-sm)]",
        ]) }}
    >{{ $initials }}</span>
@endif

@php
    $icon = $icon ?? 'health';
@endphp
<span class="public-pillar__mark" aria-hidden="true">
    @if ($icon === 'prosperity')
        {{-- Prospérité — tendance / croissance --}}
        <svg class="public-pillar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 17 9 11l4 4 8-8" />
            <path d="M14 7h7v7" />
        </svg>
    @elseif ($icon === 'freedom')
        {{-- Liberté — ailes / envol --}}
        <svg class="public-pillar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 19V9" />
            <path d="M12 9c-2.5-3-5.5-4.5-9-5 1.5 3.5 2 6.5 2 9 2.5-.5 5-1.5 7-4Z" />
            <path d="M12 9c2.5-3 5.5-4.5 9-5-1.5 3.5-2 6.5-2 9-2.5-.5-5-1.5-7-4Z" />
        </svg>
    @else
        {{-- Santé — cœur --}}
        <svg class="public-pillar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19.5 12.6 12 20l-7.5-7.4a4.7 4.7 0 0 1 6.7-6.6L12 7l.8-.9a4.7 4.7 0 0 1 6.7 6.5Z" />
        </svg>
    @endif
</span>

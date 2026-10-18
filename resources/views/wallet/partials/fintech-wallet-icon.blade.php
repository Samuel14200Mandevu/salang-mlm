@php
    $name = $name ?? 'document';
    $bare = ! empty($bare);
@endphp
@if(! $bare)
<span class="member-wallet-salang-icon" aria-hidden="true">
@endif
@switch($name)
    @case('coins')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <ellipse cx="12" cy="8" rx="6" ry="2.5"/>
            <path stroke-linecap="round" d="M6 8v3c0 1.4 2.7 2.5 6 2.5s6-1.1 6-2.5V8"/>
            <path stroke-linecap="round" d="M6 11v3c0 1.4 2.7 2.5 6 2.5s6-1.1 6-2.5v-3"/>
        </svg>
        @break
    @case('wallet')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h15a2 2 0 012 2v8a2 2 0 01-2 2H3V7z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V6a2 2 0 012-2h11a2 2 0 012 2v1"/>
            <circle cx="17" cy="13" r="1" fill="currentColor" stroke="none"/>
        </svg>
        @break
    @case('withdraw')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 17H6a2 2 0 01-2-2V7a2 2 0 012-2h7"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15l7-7m0 0h-5m5 0v5"/>
        </svg>
        @break
    @case('document')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        @break
    @case('direct')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <circle cx="6" cy="6" r="2"/>
            <circle cx="18" cy="6" r="2"/>
            <circle cx="12" cy="18" r="2"/>
            <path stroke-linecap="round" d="M7.5 7.5L10.5 16M16.5 7.5L13.5 16M8 6h8"/>
        </svg>
        @break
    @case('indirect')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        @break
    @case('sponsor')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11h2v2H7v-2zm8 0h2v2h-2v-2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 11c0-1.5 1.5-3 3-3s3 1.5 3 3M6 14c1.2 1.2 3 2 6 2s4.8-.8 6-2"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 14l2 4h12l2-4"/>
        </svg>
        @break
    @case('cash_pos')
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 19V9a2 2 0 012-2h2"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 19V5a2 2 0 00-2-2h-2"/>
        </svg>
        @break
    @default
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/>
            <circle cx="12" cy="12" r="9"/>
        </svg>
@endswitch
@if(! $bare)
</span>
@endif

@php
    $center = $center ?? false;
@endphp
<header class="public-section-head public-reveal {{ $center ? 'public-section-head--center' : '' }}">
    @if (!empty($kicker))
        <p class="public-kicker">{{ $kicker }}</p>
    @endif
    <h2 class="public-heading">{{ $title }}</h2>
    @if (!empty($lead))
        <p class="public-lead">{{ $lead }}</p>
    @endif
</header>

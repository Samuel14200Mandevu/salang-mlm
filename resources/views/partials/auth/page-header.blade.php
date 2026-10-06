@php
    $title = $title ?? '';
    $lead = $lead ?? null;
@endphp
<header class="auth-page-header">
    <h1 class="auth-title">{{ $title }}</h1>
    @if ($lead)
        <p class="auth-subtitle">{!! $lead !!}</p>
    @endif
</header>

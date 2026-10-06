@php
    $asButton = $asButton ?? false;
    $id = $id ?? 'googleAuthBtn';
@endphp

@if ($asButton)
    <button type="button" class="auth-social" id="{{ $id }}">
        @include('partials.auth.google-icon')
        <span>Continuer avec Google</span>
    </button>
@else
    <a href="{{ $href ?? route('social.redirect', 'google') }}" class="auth-social" id="{{ $id }}">
        @include('partials.auth.google-icon')
        <span>Continuer avec Google</span>
    </a>
@endif

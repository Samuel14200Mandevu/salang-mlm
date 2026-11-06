<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
@include('layouts.partials.head')
@php
    $isItManagerShell = auth()->user()?->isItManagerOnly() ?? false;
    // Responsable IT = bleu admin (#1E5DAD), pas le thème grade membre
    $memberRankAccent = (auth()->check() && ! $isItManagerShell)
        ? \App\Support\MlmRank::dashboardAccent(auth()->user()?->rank_level)
        : null;
@endphp
<body @class([
    'cashier-app member-app public-body h-full bg-[var(--bg-page)] text-[var(--text-primary)] antialiased',
    $memberRankAccent ? 'member-rank-theme' : null,
    (auth()->check() && ! $isItManagerShell && request()->routeIs('dashboard')) ? 'member-home-rank-theme' : null,
    $isItManagerShell ? 'it-manager-shell' : null,
]) @if($memberRankAccent) style="--member-rank-accent: {{ $memberRankAccent }}; --dashboard-accent: {{ $memberRankAccent }};" @endif>
    @include('layouts.partials.app-shell-open')
    @include('layouts.partials.sidebar')
    @include('layouts.partials.main-content')
    @include('layouts.partials.bottom-nav')
    @auth
        @if(! $isItManagerShell && ! request()->routeIs('profile.assistant'))
            @include('partials.member.assistant-widget')
        @endif
    @endauth
    @include('layouts.partials.confirm-dialog')
    @include('layouts.partials.scripts')
</body>
</html>

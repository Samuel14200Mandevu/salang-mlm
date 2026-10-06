<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
@include('layouts.partials.head')
<body class="cashier-app member-app public-body h-full bg-[var(--bg-page)] text-[var(--text-primary)] antialiased">
    @include('layouts.partials.app-shell-open')
    @include('layouts.partials.sidebar')
    @include('layouts.partials.main-content')
    @include('layouts.partials.bottom-nav')
    @include('layouts.partials.confirm-dialog')
    @include('layouts.partials.scripts')
</body>
</html>

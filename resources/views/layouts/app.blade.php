<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
@include('layouts.partials.head')
<body class="h-full bg-[var(--bg-primary)] text-[var(--text-primary)] transition-colors duration-200 antialiased">
    @include('layouts.partials.app-shell-open')
    @include('layouts.partials.sidebar')
    @include('layouts.partials.main-content')
    @include('layouts.partials.bottom-nav')
    @include('layouts.partials.confirm-dialog')
    @include('layouts.partials.scripts')
</body>
</html>

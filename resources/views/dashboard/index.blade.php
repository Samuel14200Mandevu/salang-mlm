{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Tableau de bord')

@push('styles')
    @include('dashboard.partials.styles')
@endpush

@section('content')
<div
    class="member-dashboard member-dashboard--fintech space-y-4 sm:space-y-6 member-dashboard--level-{{ $dashboardLevelNumber }}"
    style="--dashboard-accent: {{ $dashboardLevel['gradient'] ?? 'var(--color-primary-600)' }};"
>
    @include('dashboard.partials.inactive-alert')

    {{-- Mobile : style fintech (Binance-like) --}}
    <div class="member-fintech-shell md:hidden">
        @include('dashboard.partials.fintech-hero')
        @include('dashboard.partials.fintech-actions')
        @include('dashboard.partials.fintech-metrics-rail')

        @if($dashboardLevel['show_max_banner'] ?? false)
            <div class="member-fintech-animate member-fintech-animate--d4">
                @include('dashboard.partials.max-banner')
            </div>
        @endif

        @include('dashboard.partials.fintech-rank-card')

        @if($dashboardLevel['show_chart'] ?? true)
            @include('dashboard.partials.fintech-market-panel')
        @endif

        @if($dashboardLevel['show_leaders'] ?? false)
            <div class="member-fintech-animate member-fintech-animate--d6">
                @include('dashboard.partials.leaders')
            </div>
        @endif
    </div>

    {{-- Desktop : mise en page classique --}}
    <div class="member-classic-shell hidden md:block space-y-4 sm:space-y-6">
        @include('dashboard.partials.welcome')
        @include('dashboard.partials.commission-bands')
        @include('dashboard.partials.stats-primary')

        @if($dashboardLevel['show_leaders'] ?? false)
            @include('dashboard.partials.leaders')
        @endif

        @include('dashboard.partials.profile-stats')

        @if($dashboardLevel['show_max_banner'] ?? false)
            @include('dashboard.partials.max-banner')
        @endif

        @include('dashboard.partials.rank-progress')

        @if($dashboardLevel['show_chart'] ?? true)
            @include('dashboard.partials.chart-activities')
        @endif

        @include('dashboard.partials.quick-actions')
    </div>
</div>
@endsection

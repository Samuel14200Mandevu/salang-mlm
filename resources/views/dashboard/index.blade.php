{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Tableau de bord')

@push('styles')
    @include('dashboard.partials.styles')
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    @include('dashboard.partials.inactive-alert')
    @include('dashboard.partials.welcome')
    @include('dashboard.partials.commission-bands')
    @include('dashboard.partials.stats-primary')

    @if($dashboardLevelNumber === 3)
        @include('dashboard.partials.leaders')
    @endif

    @include('dashboard.partials.profile-stats')

    @if($dashboardLevelNumber !== 3)
        @include('dashboard.partials.leaders')
    @endif

    @include('dashboard.partials.max-banner')
    @include('dashboard.partials.rank-progress')

    @if($dashboardLevel['show_chart'] ?? true)
        @include('dashboard.partials.chart-activities')
    @endif

    @include('dashboard.partials.quick-actions')
</div>
@endsection

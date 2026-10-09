@extends('layouts.app')

@section('title', 'Services')

@section('content')
<div class="member-services member-services--hub space-y-3 sm:space-y-4">
    <div class="member-services-intro-desktop member-page-intro animate-fadeInUp hidden md:block">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Services</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
            Réseau, commissions, événements et assistance — tout votre espace membre au même endroit.
        </p>
    </div>

    @include('services.partials.catalog-head')

    <div class="member-services-featured member-shop-animate member-shop-animate--2">
        @include('partials.member.services-featured-carousel')
    </div>

    @include('partials.member.services-account-menu')
</div>
@endsection

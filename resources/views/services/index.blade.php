@extends('layouts.app')

@section('title', 'Services')

@section('content')
@php
    $hasFeatured = ($servicesFeaturedPublications ?? collect())->isNotEmpty();
@endphp
<div class="member-services member-services--hub">
    {{-- —— Mobile (menu complet + bannière) —— --}}
    <div class="member-services-stack member-services-stack--mobile">
        @include('services.partials.catalog-head')

        @if($hasFeatured)
            <div class="member-services-featured member-shop-animate member-shop-animate--2">
                @include('partials.member.services-featured-carousel')
            </div>
        @endif

        @include('partials.member.services-account-menu')
    </div>

    {{-- —— Desktop (découverte, sans dupliquer la sidebar) —— --}}
    <div class="member-services-stack member-services-stack--desktop">
        <div class="member-services-intro-desktop member-page-intro animate-fadeInUp">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Hub Services</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Promotions à la une, assistance et outils complémentaires — la navigation principale reste dans le menu latéral.
            </p>
        </div>

        @include('services.partials.hub-desktop-shortcuts')

        @if($hasFeatured)
            <div class="member-services-featured-desktop card p-0 overflow-hidden member-shop-animate member-shop-animate--2">
                @include('partials.member.services-featured-carousel')
            </div>
        @endif

        @include('services.partials.hub-desktop-panels')
    </div>
</div>
@endsection

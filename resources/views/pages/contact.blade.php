@extends('layouts.app')

@section('title', 'Nous contacter')

@section('content')
@php
    $contactBack = auth()->check()
        ? route('profile.help')
        : route('home');
@endphp
<div class="profile-page profile-page--account profile-subpage profile-contact-page">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Nous contacter</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Salang Group · support officiel</p>
    </div>

    @include('profile.partials.account-subpage-header', [
        'title' => 'Contact',
        'sub' => 'Salang Group · support',
        'backUrl' => $contactBack,
    ])

    <div class="profile-subpage-body">
        @include('profile.partials.contact-content')
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Changer le mot de passe')

@section('content')
<div class="profile-page profile-page--account profile-subpage profile-page--password-only">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text-primary)]">Changer le mot de passe</h1>
                <p class="text-sm text-[var(--text-secondary)] mt-1">Mettez à jour votre mot de passe de connexion</p>
            </div>
            <a href="{{ route('profile.index') }}" class="btn btn-outline btn-sm">← Profil</a>
        </div>
    </div>

    @include('profile.partials.account-subpage-header', [
        'title' => 'Changer le mot de passe',
        'sub' => 'Sécurité du compte',
        'backUrl' => route('profile.index'),
    ])

    @if(session('success'))
        <div class="profile-subpage-body profile-flash profile-flash--mobile p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="profile-subpage-body profile-flash profile-flash--mobile p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-subpage-body profile-password-layout">
        <div class="profile-password-main">
            @include('profile.partials.password-change-card')
        </div>

        <aside class="profile-password-aside space-y-4">
            <section class="card p-4 sm:p-5">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Conseils de sécurité</h3>
                <ul class="mt-3 space-y-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                    <li>Au moins 8 caractères, idéalement plus.</li>
                    <li>Mélangez lettres, chiffres et symboles.</li>
                    <li>Ne réutilisez pas un mot de passe d’un autre site.</li>
                    <li>Changez-le si vous suspectez un accès non autorisé.</li>
                </ul>
            </section>
            <section class="card p-4 sm:p-5">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Autres accès</h3>
                <div class="mt-3 flex flex-col gap-2">
                    <a href="{{ route('profile.index', ['edit' => 1]) }}" class="btn btn-outline btn-sm w-full justify-center">Informations personnelles</a>
                    <a href="{{ route('kyc.index') }}" class="btn btn-outline btn-sm w-full justify-center">Vérification KYC</a>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

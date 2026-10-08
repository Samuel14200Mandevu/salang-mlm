@extends('layouts.app')

@section('title', 'Changer le mot de passe')

@section('content')
<div class="profile-page profile-page--account profile-subpage profile-page--password-only">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block mb-4">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Changer le mot de passe</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Mettez à jour votre mot de passe de connexion</p>
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

    <div class="profile-subpage-body">
        @include('profile.partials.password-change-card')
    </div>
</div>
@endsection

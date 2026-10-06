@extends('layouts.auth')

@section('title', config('legacy-auth.info_title').' — Salang Group')

@section('content')
    @include('partials.auth.page-header', [
        'title' => config('legacy-auth.info_title'),
        'lead' => config('legacy-auth.info_lead'),
    ])

    @if ($accountEmail)
        <p class="text-sm text-[var(--text-secondary)] mb-4">
            Compte concerné : <strong class="text-[var(--text-primary)]">{{ $accountEmail }}</strong>
        </p>
    @endif

    <div class="mb-6 rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] p-4">
        <h2 class="text-sm font-semibold text-[var(--text-primary)] mb-3">Marche à suivre</h2>
        <ol class="list-decimal pl-5 space-y-2 text-sm text-[var(--text-secondary)]">
            @foreach ($steps as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
    </div>

    <p class="text-sm text-[var(--text-secondary)] mb-6">{{ $contactHint }}</p>

    <a href="{{ route('login') }}" class="auth-btn-primary inline-flex w-full justify-center">Retour à la connexion</a>
@endsection

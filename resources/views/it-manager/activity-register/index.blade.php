@extends('layouts.app')

@section('title', 'Registre d’activité')

@section('content')
@php
    $todayCount = $todaySheet?->entries()->count() ?? 0;
@endphp

{{-- Desktop --}}
<div class="it-desktop-head it-desktop-only mb-6 items-end justify-between gap-4">
    <div class="member-page-intro mb-0 min-w-0">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Registre d’activité</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Jours avec participants enregistrés</p>
    </div>
    <a href="{{ route('it-manager.activity-register.show') }}" class="btn btn-primary shrink-0">Ouvrir aujourd’hui</a>
</div>

{{-- Mobile --}}
<div class="shop-catalog-head it-mobile-only mb-5">
    <div class="shop-catalog-banner">
        <div class="shop-catalog-banner__text">
            <p class="shop-catalog-banner__eyebrow">Activité terrain</p>
            <p class="shop-catalog-banner__title">Registre d’activité</p>
            <p class="shop-catalog-banner__sub">Jours avec participants enregistrés</p>
        </div>
        <div class="shop-catalog-banner__tools">
            <a href="{{ route('it-manager.activity-register.show') }}"
               class="shop-banner-icon-btn" aria-label="Ouvrir aujourd’hui" title="Aujourd’hui">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif

<a href="{{ route('it-manager.activity-register.show') }}"
   class="block rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4 sm:p-5 mb-6 hover:border-[var(--primary)] transition-colors">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wide text-[var(--text-muted)]">Aujourd’hui</p>
            <p class="text-lg font-semibold text-[var(--text-primary)] mt-0.5">
                {{ $today->translatedFormat('l d F Y') }}
            </p>
            <p class="text-sm text-[var(--text-secondary)] mt-1">
                @if ($todayCount > 0)
                    {{ $todayCount }} participant{{ $todayCount > 1 ? 's' : '' }}
                @else
                    Aucun participant — ouvrir pour saisir
                @endif
            </p>
        </div>
        <span class="btn btn-primary shrink-0">Ouvrir</span>
    </div>
</a>

@if ($sheets->count())
    <h2 class="text-sm font-semibold text-[var(--text-secondary)] mb-3">Jours avec activité</h2>
    <ul class="space-y-2">
        @foreach ($sheets as $sheet)
            <li>
                <a href="{{ route('it-manager.activity-register.show', ['date' => $sheet->activity_date?->toDateString()]) }}"
                   class="flex items-center justify-between gap-3 rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-4 py-3 hover:border-[var(--primary)] transition-colors">
                    <div>
                        <p class="text-sm font-medium text-[var(--text-primary)]">
                            {{ $sheet->activity_date?->translatedFormat('l d/m/Y') }}
                        </p>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">
                            {{ $sheet->entries_count }} participant{{ $sheet->entries_count > 1 ? 's' : '' }}
                            · {{ $sheet->adhesion_count }} adhésion{{ $sheet->adhesion_count > 1 ? 's' : '' }}
                            · {{ $sheet->consultation_count }} consult.
                            · {{ $sheet->purchase_count }} achat{{ $sheet->purchase_count > 1 ? 's' : '' }}
                        </p>
                    </div>
                    <span class="text-sm font-semibold text-primary-600">Voir</span>
                </a>
            </li>
        @endforeach
    </ul>
    <div class="mt-4">{{ $sheets->links() }}</div>
@else
    <p class="text-sm text-[var(--text-muted)]">Pas encore de jours enregistrés.</p>
@endif
@endsection

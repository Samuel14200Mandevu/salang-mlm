@extends('layouts.app')

@section('title', 'Rapport réseau')

@section('content')
<div class="space-y-4 sm:space-y-6 member-downline-report">
    <div class="member-page-intro animate-fadeInUp">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Rapport détaillé</h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    Filleuls ayant travaillé sur la période (depuis janvier 2026)
                </p>
            </div>
            <a href="{{ route('report.index') }}" class="btn btn-outline text-sm">← Rapports</a>
        </div>
    </div>

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="card animate-fadeInUp delay-1">
        <div class="card-title">Choisir une période</div>
        <p class="text-sm text-[var(--text-secondary)] mb-4">
            Chaque rapport liste vos filleuls (tous niveaux) avec du PV sur le mois sélectionné.
        </p>

        @if (count($periods) === 0)
            <p class="text-sm text-[var(--text-muted)] py-6 text-center">Aucune période disponible pour le moment.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($periods as $item)
                    <a
                        href="{{ route('report.downline.show', $item['value']) }}"
                        class="member-downline-period-card group"
                    >
                        <span class="member-downline-period-card__code">{{ $item['value'] }}</span>
                        <span class="member-downline-period-card__label">{{ $item['label'] }}</span>
                        <span class="member-downline-period-card__cta">Voir le rapport →</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .member-downline-period-card {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        padding: 1rem 1.1rem;
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        text-decoration: none;
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .member-downline-period-card:hover {
        border-color: color-mix(in srgb, var(--color-primary-600) 45%, var(--border-color));
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }
    .member-downline-period-card__code {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-primary-600);
    }
    .member-downline-period-card__label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1.35;
    }
    .member-downline-period-card__cta {
        margin-top: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-secondary);
    }
    .member-downline-period-card:hover .member-downline-period-card__cta {
        color: var(--color-primary-600);
    }
</style>
@endpush
@endsection

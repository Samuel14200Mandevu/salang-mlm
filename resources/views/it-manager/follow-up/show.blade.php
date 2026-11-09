@extends('layouts.app')

@section('title', 'Détail suivi')

@section('content')
@php
    $steps = [
        ['label' => 'Formation', 'done' => (bool) $entry->formation_done, 'hint' => $entry->formation_done ? 'A participé à la formation' : 'Pas encore de formation'],
        ['label' => 'Adhésion', 'done' => (bool) $entry->adhesion_done, 'hint' => $entry->adhesion_done
            ? ('Adhéré'.($entry->adhesion_at ? ' · '.$entry->adhesion_at->format('d/m/Y') : ''))
            : 'Sans adhésion'],
        ['label' => 'Consultation', 'done' => (bool) $entry->consultation_done, 'hint' => $entry->consultation_done
            ? ('Consult.'.($entry->consultation_at ? ' · '.$entry->consultation_at->format('d/m/Y') : ''))
            : 'Sans consultation'],
        ['label' => 'Achat', 'done' => (bool) $entry->purchase_done, 'hint' => $entry->purchase_done
            ? ('Achat'.($entry->purchase_at ? ' · '.$entry->purchase_at->format('d/m/Y') : ''))
            : 'Sans achat'],
    ];
@endphp

<div class="mb-4">
    <a href="{{ route('it-manager.follow-up.index', array_filter(['filter' => $filter, 'member_code' => ($memberCode ?? null) ?: null])) }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour au suivi
    </a>
</div>

<div class="member-page-intro mb-4">
    <p class="text-xs text-[var(--text-muted)] tabular-nums">{{ $entry->sheet?->activity_date?->format('d/m/Y') }}</p>
    <h1 class="text-2xl font-bold text-[var(--text-primary)] mt-0.5">{{ $entry->displayName() }}</h1>
    @if($entry->participant_phone)
        <p class="text-sm text-[var(--text-secondary)] mt-1">{{ $entry->participant_phone }}</p>
    @endif
</div>

<div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-3 mb-4">
    <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-muted)]">Parrain</p>
    <p class="mt-1 font-medium text-[var(--text-primary)]">{{ $entry->member?->name ?: '—' }}</p>
    @if($entry->member_code)
        <p class="text-xs font-mono text-[var(--text-muted)]">{{ $entry->member_code }}</p>
    @endif
</div>

<div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden mb-5">
    <div class="px-3 py-2.5 bg-[var(--bg-secondary)]">
        <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">Parcours</p>
    </div>
    <ul class="divide-y divide-[var(--border-light)]">
        @foreach ($steps as $step)
            <li class="flex items-center gap-3 px-3 py-3">
                <span @class([
                    'inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-bold',
                    'bg-[var(--success-light)] text-[var(--success)]' => $step['done'],
                    'bg-[var(--bg-secondary)] text-[var(--text-muted)]' => ! $step['done'],
                ])>{{ $step['done'] ? '✓' : '·' }}</span>
                <div class="min-w-0">
                    <p class="font-semibold text-[var(--text-primary)]">{{ $step['label'] }}</p>
                    <p class="text-xs text-[var(--text-muted)]">{{ $step['hint'] }}</p>
                </div>
            </li>
        @endforeach
    </ul>
</div>

@if($entry->sheet?->activity_date)
    <a href="{{ route('it-manager.activity-register.show', ['date' => $entry->sheet->activity_date->toDateString()]) }}"
       class="btn btn-primary w-full sm:w-auto">Ouvrir le registre</a>
@endif
@endsection

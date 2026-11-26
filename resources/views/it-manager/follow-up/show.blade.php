@extends('layouts.app')

@section('title', 'Suivi · '.$member->name)

@section('content')
@php
    $focusLabel = $focusDay ? \Illuminate\Support\Carbon::parse($focusDay)->format('d/m/Y') : null;
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

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="member-page-intro mb-4">
    <p class="text-xs uppercase tracking-wide text-[var(--text-muted)]">Membre · parrain</p>
    <h1 class="text-2xl font-bold text-[var(--text-primary)] mt-0.5">{{ $member->name }}</h1>
    @if($member->sponsor_id)
        <p class="text-sm font-mono text-[var(--text-secondary)] mt-1">{{ $member->sponsor_id }}</p>
    @endif
</div>

<section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden mb-5">
    <div class="px-3 py-2.5 bg-[var(--bg-secondary)]">
        <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">Dates avec invités</p>
    </div>
    @if ($dateStats->isEmpty())
        <p class="px-3 py-8 text-center text-sm text-[var(--text-muted)]">Aucune date enregistrée pour ce membre.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[0.6875rem] uppercase tracking-wide text-[var(--text-muted)] border-b border-[var(--border-light)]">
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2 text-center">Invités</th>
                        <th class="px-3 py-2 text-center">Form.</th>
                        <th class="px-3 py-2 text-center">Adh.</th>
                        <th class="px-3 py-2 text-center">Cons.</th>
                        <th class="px-3 py-2 text-center">Achat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dateStats as $stat)
                        @php
                            $statDate = \Illuminate\Support\Carbon::parse($stat->activity_date)->toDateString();
                            $isFocus = $focusDay === $statDate;
                            $url = route('it-manager.follow-up.show', array_filter([
                                'member' => $member,
                                'date' => $statDate,
                                'filter' => $filter,
                                'member_code' => ($memberCode ?? null) ?: null,
                            ]));
                        @endphp
                        <tr class="border-t border-[var(--border-light)] {{ $isFocus ? 'bg-[var(--bg-secondary)]' : 'hover:bg-[var(--bg-secondary)]/40' }} cursor-pointer"
                            onclick="window.location.href=@js($url)">
                            <td class="px-3 py-2.5 whitespace-nowrap font-medium {{ $isFocus ? 'text-primary-600' : 'text-[var(--text-primary)]' }}">
                                {{ \Illuminate\Support\Carbon::parse($stat->activity_date)->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2.5 text-center tabular-nums font-semibold">{{ (int) ($stat->invites_count ?? 0) }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) ($stat->formation_count ?? 0) }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) ($stat->adhesion_count ?? 0) }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) ($stat->consultation_count ?? 0) }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums">{{ (int) ($stat->purchase_count ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@if ($focusDay)
    <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] p-4 mb-5">
        <h2 class="text-sm font-semibold text-[var(--text-primary)] mb-3">Modifier la date</h2>
        <form method="POST"
              action="{{ route('it-manager.follow-up.update-date', $member) }}"
              class="flex flex-wrap items-end gap-3">
            @csrf
            @method('PATCH')
            <input type="hidden" name="from_date" value="{{ $focusDay }}">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="hidden" name="member_code" value="{{ $memberCode }}">
            <div>
                <label for="follow_up_to_date" class="block text-xs font-medium text-[var(--text-secondary)] mb-1">
                    Date du groupe ({{ $focusLabel }})
                </label>
                <input type="date"
                       id="follow_up_to_date"
                       name="to_date"
                       value="{{ $focusDay }}"
                       required
                       class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                Enregistrer la date
            </button>
            <a href="{{ route('it-manager.activity-register.show', ['date' => $focusDay]) }}"
               class="btn btn-outline btn-sm">
                Ouvrir le registre
            </a>
        </form>
        <p class="mt-2 text-xs text-[var(--text-muted)]">
            Déplace tous les invités de ce membre pour ce jour vers la nouvelle date.
        </p>
    </section>

    <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden mb-5">
        <div class="px-3 py-2.5 bg-[var(--bg-secondary)]">
            <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                Invités du {{ $focusLabel }}
            </p>
        </div>
        @if ($dayEntries->isEmpty())
            <p class="px-3 py-8 text-center text-sm text-[var(--text-muted)]">Aucun invité ce jour.</p>
        @else
            <ul class="divide-y divide-[var(--border-light)]">
                @foreach ($dayEntries as $entry)
                    @php
                        $editUrl = route('it-manager.follow-up.entry.edit', array_filter([
                            'entry' => $entry,
                            'filter' => $filter,
                            'member_code' => ($memberCode ?? null) ?: null,
                        ]));
                    @endphp
                    <li class="px-3 py-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-[var(--text-primary)]">{{ $entry->displayName() }}</p>
                            @if ($entry->participant_phone)
                                <p class="text-xs text-[var(--text-muted)]">{{ $entry->participant_phone }}</p>
                            @endif
                            <p class="mt-1.5 text-xs tabular-nums text-[var(--text-secondary)]">
                                {{ $entry->inviteLabel() }}
                                · Form {{ $entry->formation_done ? '✓' : '·' }}
                                · Adh {{ $entry->adhesion_done ? '✓' : '·' }}
                                · Cons {{ $entry->consultation_done ? '✓' : '·' }}
                                · Achat {{ $entry->purchase_done ? '✓' : '·' }}
                            </p>
                        </div>
                        <a href="{{ $editUrl }}" class="btn btn-primary btn-sm shrink-0">
                            Modifier
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endif
@endsection

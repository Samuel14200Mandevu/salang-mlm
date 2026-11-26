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
    <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden mb-5">
        <div class="flex items-center justify-between gap-3 px-3 py-2.5 bg-[var(--bg-secondary)]">
            <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                Invités du {{ $focusLabel }}
            </p>
            <a href="{{ route('it-manager.activity-register.show', ['date' => $focusDay]) }}"
               class="text-xs font-semibold text-primary-600">Registre</a>
        </div>
        @if ($dayEntries->isEmpty())
            <p class="px-3 py-8 text-center text-sm text-[var(--text-muted)]">Aucun invité ce jour.</p>
        @else
            <ul class="divide-y divide-[var(--border-light)]">
                @foreach ($dayEntries as $entry)
                    <li class="px-3 py-3 space-y-3">
                        <form method="POST"
                              action="{{ route('it-manager.activity-register.entries.update', $entry) }}"
                              class="space-y-3">
                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Nom</label>
                                    <input type="text"
                                           name="participant_nom"
                                           value="{{ $entry->participant_nom }}"
                                           required
                                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Post-Nom</label>
                                    <input type="text"
                                           name="participant_postnom"
                                           value="{{ $entry->participant_postnom }}"
                                           required
                                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                                    <input type="tel"
                                           name="participant_phone"
                                           value="{{ $entry->participant_phone }}"
                                           class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-[var(--text-secondary)] mb-1">Type</label>
                                    <select name="invite_type"
                                            class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-3 py-2 text-sm">
                                        <option value="formation" @selected($entry->invite_type === 'formation')>Formation</option>
                                        <option value="autre" @selected($entry->invite_type === 'autre')>Autre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach ([
                                        'formation_done' => ['Form.', (bool) $entry->formation_done],
                                        'adhesion_done' => ['Adh.', (bool) $entry->adhesion_done],
                                        'consultation_done' => ['Cons.', (bool) $entry->consultation_done],
                                        'purchase_done' => ['Achat', (bool) $entry->purchase_done],
                                    ] as $flag => [$label, $done])
                                        <label class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--bg-secondary)] px-2.5 py-1.5 text-xs font-semibold text-[var(--text-secondary)] cursor-pointer">
                                            <input type="hidden" name="{{ $flag }}" value="0">
                                            <input type="checkbox"
                                                   name="{{ $flag }}"
                                                   value="1"
                                                   class="rounded border-[var(--border-color)] text-[var(--primary)] focus:ring-[var(--primary)]"
                                                   @checked($done)>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endif
@endsection

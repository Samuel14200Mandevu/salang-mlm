@extends('layouts.app')

@section('title', 'Suivi · '.$member->name)

@section('content')
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

<div class="mb-4 flex flex-wrap items-start justify-between gap-3">
    <div class="member-page-intro mb-0 min-w-0">
        <p class="text-xs uppercase tracking-wide text-[var(--text-muted)]">Membre · parrain</p>
        <h1 class="text-2xl font-bold text-[var(--text-primary)] mt-0.5">{{ $member->name }}</h1>
        @if($member->sponsor_id)
            <p class="text-sm font-mono text-[var(--text-secondary)] mt-1">{{ $member->sponsor_id }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-stretch gap-2 shrink-0">
        @foreach ([
            ['Total', (int) ($memberTotals['invites'] ?? 0)],
            ['Form.', (int) ($memberTotals['formation'] ?? 0)],
            ['Cons.', (int) ($memberTotals['consultation'] ?? 0)],
            ['Achat', (int) ($memberTotals['purchase'] ?? 0)],
        ] as [$label, $count])
            <div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-2 min-w-[4.25rem] text-center">
                <p class="text-lg font-bold tabular-nums text-[var(--text-primary)] leading-none">{{ $count }}</p>
                <p class="mt-1 text-[0.625rem] uppercase tracking-wide text-[var(--text-muted)]">{{ $label }}</p>
            </div>
        @endforeach
    </div>
</div>

@if ($entriesByDate->isEmpty())
    <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-10 text-center text-sm text-[var(--text-muted)] mb-5">
        Aucun invité enregistré pour ce membre.
    </section>
@else
    @foreach ($entriesByDate as $day => $dayEntries)
        @php
            $dayLabel = $day === 'unknown'
                ? 'Date inconnue'
                : \Illuminate\Support\Carbon::parse($day)->format('d/m/Y');
        @endphp
        <section class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden mb-5">
            <div class="flex items-center justify-between gap-3 px-3 py-2.5 bg-[var(--bg-secondary)]">
                <div class="inline-flex items-center gap-1.5 min-w-0">
                    <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                        Invités du {{ $dayLabel }}
                    </p>
                    @if ($day !== 'unknown')
                        <form method="POST"
                              action="{{ route('it-manager.follow-up.update-date', $member) }}"
                              class="inline-flex">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="from_date" value="{{ $day }}">
                            <input type="hidden" name="filter" value="{{ $filter }}">
                            <input type="hidden" name="member_code" value="{{ $memberCode }}">
                            <label class="relative inline-flex h-7 w-7 items-center justify-center rounded-md text-[var(--text-secondary)] hover:bg-[var(--bg-card)] hover:text-primary-600 cursor-pointer"
                                   title="Modifier la date"
                                   aria-label="Modifier la date du {{ $dayLabel }}">
                                <input type="date"
                                       name="to_date"
                                       value="{{ $day }}"
                                       required
                                       class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                       onchange="this.form.requestSubmit()">
                                <svg class="pointer-events-none h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </label>
                        </form>
                    @endif
                </div>
                @if ($day !== 'unknown')
                    <a href="{{ route('it-manager.activity-register.show', ['date' => $day]) }}"
                       class="text-xs font-semibold text-primary-600">Registre</a>
                @endif
            </div>
            <ul class="divide-y divide-[var(--border-light)]">
                @foreach ($dayEntries as $entry)
                    @php
                        $editUrl = route('it-manager.follow-up.entry.edit', array_filter([
                            'entry' => $entry,
                            'filter' => $filter,
                            'member_code' => ($memberCode ?? null) ?: null,
                        ]));
                    @endphp
                    <li class="px-3 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-[var(--text-primary)] truncate">{{ $entry->displayName() }}</p>
                                @if ($entry->participant_phone)
                                    <p class="text-xs text-[var(--text-muted)]">{{ $entry->participant_phone }}</p>
                                @endif
                            </div>
                            <p class="shrink-0 text-xs tabular-nums text-[var(--text-secondary)] text-right">
                                Form {{ $entry->formation_done ? '✓' : '·' }}
                                · Adh {{ $entry->adhesion_done ? '✓' : '·' }}
                                · Cons {{ $entry->consultation_done ? '✓' : '·' }}
                                · Achat {{ $entry->purchase_done ? '✓' : '·' }}
                            </p>
                            <div class="flex items-center gap-2 shrink-0">
                                <p class="text-[0.6875rem] uppercase tracking-wide text-[var(--text-muted)]">
                                    {{ $entry->inviteLabel() }}
                                </p>
                                <a href="{{ $editUrl }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[var(--primary)] text-white hover:opacity-90"
                                   title="Modifier"
                                   aria-label="Modifier {{ $entry->displayName() }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
@endif
@endsection

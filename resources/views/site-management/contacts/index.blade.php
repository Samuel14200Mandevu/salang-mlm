@extends('layouts.app')

@section('title', 'Messages du site')

@section('content')
<div class="member-page-intro mb-4">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">Messages du site officiel</h1>
    <p class="text-sm text-[var(--text-secondary)] mt-1">Demandes des visiteurs — accessible admin &amp; responsable IT</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @foreach ([
        ['all', 'Tous', $counts['all']],
        ['new', 'Nouveaux', $counts['new']],
        ['read', 'Lus', $counts['read']],
        ['archived', 'Archivés', $counts['archived']],
    ] as [$key, $label, $count])
        <a href="{{ route('site-management.contacts.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $q ?: null])) }}"
           class="rounded-lg border px-3 py-3 {{ ($status === $key || ($key === 'all' && $status === '')) ? 'border-[var(--color-primary-600)] bg-[var(--bg-secondary)]' : 'border-[var(--border-color)] bg-[var(--bg-card)]' }}">
            <p class="text-lg font-bold tabular-nums text-[var(--text-primary)]">{{ $count }}</p>
            <p class="text-xs text-[var(--text-muted)] uppercase tracking-wide">{{ $label }}</p>
        </a>
    @endforeach
</div>

<form method="GET" class="mb-4 flex flex-col sm:flex-row gap-2">
    @if ($status !== '')
        <input type="hidden" name="status" value="{{ $status }}">
    @endif
    <input type="search" name="q" value="{{ $q }}" placeholder="Rechercher nom, email, sujet…"
           class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-2 text-sm">
    <button type="submit" class="btn btn-primary">Filtrer</button>
</form>

<div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[var(--bg-secondary)] text-left text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                    <th class="px-3 py-2.5">Statut</th>
                    <th class="px-3 py-2.5">Visiteur</th>
                    <th class="px-3 py-2.5">Sujet</th>
                    <th class="px-3 py-2.5">Date</th>
                    <th class="px-3 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $msg)
                    <tr class="border-t border-[var(--border-light)] hover:bg-[var(--bg-hover)]">
                        <td class="px-3 py-2.5">
                            <span class="inline-flex rounded-md px-2 py-0.5 text-[0.625rem] font-semibold
                                {{ $msg->status === 'new' ? 'bg-sky-100 text-sky-800' : ($msg->status === 'archived' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $msg->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-[var(--text-primary)]">{{ $msg->name }}</p>
                            <p class="text-xs text-[var(--text-muted)]">{{ $msg->email }}</p>
                        </td>
                        <td class="px-3 py-2.5 text-[var(--text-secondary)]">{{ \Illuminate\Support\Str::limit($msg->subject, 48) }}</td>
                        <td class="px-3 py-2.5 text-[var(--text-muted)] whitespace-nowrap">{{ $msg->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2.5 text-right">
                            <a href="{{ route('site-management.contacts.show', $msg) }}" class="text-sm font-semibold text-primary-600">Ouvrir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-10 text-center text-sm text-[var(--text-muted)]">Aucun message pour ce filtre.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $messages->links() }}</div>
@endsection

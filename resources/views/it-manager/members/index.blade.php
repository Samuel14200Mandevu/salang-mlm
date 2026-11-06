@extends('layouts.app')

@section('title', 'Membres')

@section('content')
<div class="member-page-intro mb-4">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">Membres</h1>
    <p class="text-sm text-[var(--text-secondary)] mt-1">Recherche lecture seule (code, nom, téléphone).</p>
</div>

<form method="GET" class="mb-4 flex flex-col sm:flex-row gap-2">
    <input type="search" name="q" value="{{ $q }}" placeholder="Code, nom, téléphone…"
           class="flex-1 rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] px-3 py-2 text-sm">
    <button type="submit" class="btn btn-primary">Rechercher</button>
</form>

<div class="rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[var(--bg-secondary)] text-left text-[0.6875rem] uppercase tracking-wide text-[var(--text-secondary)]">
                    <th class="px-3 py-2.5">Code</th>
                    <th class="px-3 py-2.5">Nom</th>
                    <th class="px-3 py-2.5">Téléphone</th>
                    <th class="px-3 py-2.5">Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr class="border-t border-[var(--border-light)]">
                        <td class="px-3 py-2.5 font-mono text-xs">{{ $member->sponsor_id ?: '—' }}</td>
                        <td class="px-3 py-2.5 font-medium text-[var(--text-primary)]">{{ $member->name }}</td>
                        <td class="px-3 py-2.5">{{ $member->phone ?: '—' }}</td>
                        <td class="px-3 py-2.5 text-[var(--text-secondary)]">{{ $member->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-10 text-center text-sm text-[var(--text-muted)]">
                            {{ $q ? 'Aucun résultat.' : 'Saisissez un code ou un nom.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $members->links() }}</div>
@endsection

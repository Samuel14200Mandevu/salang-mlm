@extends('admin.layouts.app')

@section('title', 'Pharmacie — Caisse')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Caisse pharmacie',
    'subtitle' => 'Solde : '.number_format($balance, 2).' $',
])

@include('admin.pharmacy.partials.nav')

<div class="grid md:grid-cols-4 gap-3 mb-4">
    @foreach([
        ['Fond de caisse', number_format($cashFloat, 2).' $'],
        ['Entrées période', number_format($periodIn, 2).' $'],
        ['Sorties période', number_format($periodOut, 2).' $'],
        ['Solde', number_format($balance, 2).' $'],
    ] as [$label, $value])
        <div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
            <p class="text-xs text-[var(--text-tertiary)]">{{ $label }}</p>
            <p class="text-xl font-bold">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-4 mb-6">
    <form method="POST" action="{{ route('admin.pharmacy.cash.store') }}" class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3">
        @csrf
        <h2 class="font-semibold">Nouveau mouvement</h2>
        <input type="date" name="entry_date" value="{{ now()->toDateString() }}" class="form-input" required>
        <input type="text" name="label" placeholder="Libellé" class="form-input" required>
        <select name="category" class="form-input" required>
            @foreach($categories as $k => $label)
                <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
        </select>
        <div class="grid grid-cols-2 gap-2">
            <input type="number" step="0.01" min="0" name="amount_in" placeholder="Entrée $" class="form-input">
            <input type="number" step="0.01" min="0" name="amount_out" placeholder="Sortie $" class="form-input">
        </div>
        <input type="text" name="notes" placeholder="Notes" class="form-input">
        <button class="btn btn-primary btn-sm" type="submit">Ajouter</button>
    </form>

    <form method="POST" action="{{ route('admin.pharmacy.cash.float') }}" class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3">
        @csrf
        @method('PUT')
        <h2 class="font-semibold">Fond de caisse</h2>
        <input type="number" step="0.01" min="0" name="cash_float" value="{{ $cashFloat }}" class="form-input" required>
        <button class="btn btn-outline btn-sm" type="submit">Mettre à jour</button>
    </form>
</div>

<form method="GET" class="flex flex-wrap gap-2 mb-3">
    <input type="date" name="from" value="{{ $from }}" class="form-input">
    <input type="date" name="to" value="{{ $to }}" class="form-input">
    <button class="btn btn-outline btn-sm" type="submit">Période</button>
</form>

<div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Libellé</th>
                <th class="px-4 py-2">Catégorie</th>
                <th class="px-4 py-2 text-right">Entrée</th>
                <th class="px-4 py-2 text-right">Sortie</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr class="border-t border-[var(--border-light)]">
                    <td class="px-4 py-2">{{ $entry->entry_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $entry->label }}</td>
                    <td class="px-4 py-2">{{ $entry->categoryLabel() }}</td>
                    <td class="px-4 py-2 text-right">{{ $entry->amount_in > 0 ? number_format($entry->amount_in, 2).' $' : '—' }}</td>
                    <td class="px-4 py-2 text-right">{{ $entry->amount_out > 0 ? number_format($entry->amount_out, 2).' $' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-[var(--text-secondary)]">Aucun mouvement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $entries->links() }}</div>
@endsection

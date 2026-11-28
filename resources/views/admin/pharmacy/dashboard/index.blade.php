@extends('admin.layouts.app')

@section('title', 'Pharmacie — Tableau de bord')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Pharmacie',
    'subtitle' => 'Tableau de bord · '. \Carbon\Carbon::parse($from)->format('d/m/Y').' → '.\Carbon\Carbon::parse($to)->format('d/m/Y'),
    'actions' => '<a href="'.route('admin.pharmacy.sales.create').'" class="btn btn-primary btn-sm">Nouvelle vente</a>
                  <a href="'.route('admin.pharmacy.purchases.create').'" class="btn btn-outline btn-sm">Nouvel achat</a>',
])

<div class="admin-page-header page-header md:hidden animate-fadeInUp mb-4">
    <h1 class="text-xl font-bold text-[var(--text-primary)]">Pharmacie</h1>
    <p class="text-sm text-[var(--text-secondary)]">Tableau de bord</p>
</div>

@include('admin.pharmacy.partials.nav')

<form method="GET" class="flex flex-wrap gap-2 items-end mb-4">
    <div>
        <label class="text-xs text-[var(--text-tertiary)]">Du</label>
        <input type="date" name="from" value="{{ $from }}" class="form-input">
    </div>
    <div>
        <label class="text-xs text-[var(--text-tertiary)]">Au</label>
        <input type="date" name="to" value="{{ $to }}" class="form-input">
    </div>
    <button type="submit" class="btn btn-outline btn-sm">Filtrer</button>
</form>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @foreach([
        ['Achats période', number_format($purchasesTotal, 2).' $', 'Payé '.number_format($purchasesPaid, 2).' $'],
        ['Ventes période', number_format($salesTotal, 2).' $', $salesLines.' lignes'],
        ['Encaissé (patient)', number_format($patientCollected, 2).' $', 'Facturé assur. '.number_format($insuranceBilled, 2).' $'],
        ['Marge / CMV', number_format($profit, 2).' $', 'CMV '.number_format($cogs, 2).' $'],
        ['Valeur stock', number_format($stockValue, 2).' $', $stockAlert.' alertes'],
        ['Solde caisse', number_format($cashBalance, 2).' $', 'Fond + mouvements'],
    ] as [$label, $value, $hint])
        <div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
            <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)]">{{ $label }}</p>
            <p class="text-xl font-bold text-[var(--text-primary)] mt-1">{{ $value }}</p>
            <p class="text-xs text-[var(--text-secondary)] mt-1">{{ $hint }}</p>
        </div>
    @endforeach
</div>

<div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <div class="px-4 py-3 border-b border-[var(--border-color)] flex justify-between items-center">
        <h2 class="font-semibold text-[var(--text-primary)]">Dernières ventes</h2>
        <a href="{{ route('admin.pharmacy.sales.index') }}" class="text-sm text-[var(--primary)]">Voir tout</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                    <th class="px-4 py-2">Ticket</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Client</th>
                    <th class="px-4 py-2">Paiement</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSales as $sale)
                    <tr class="border-t border-[var(--border-light)]">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.pharmacy.sales.show', $sale) }}" class="text-[var(--primary)] font-medium">{{ $sale->ticket_number }}</a>
                        </td>
                        <td class="px-4 py-2">{{ $sale->sold_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $sale->patient?->name ?? $sale->customer_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $sale->paymentMethodLabel() }}</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($sale->total_amount, 2) }} $</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-[var(--text-secondary)]">Aucune vente pour l’instant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

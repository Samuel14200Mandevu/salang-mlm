@extends('admin.layouts.app')

@section('title', 'Pharmacie — Achats')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Achats fournisseurs',
    'subtitle' => 'Dettes fournisseurs : '.number_format($debtTotal, 2).' $',
    'actions' => '<a href="'.route('admin.pharmacy.purchases.create').'" class="btn btn-primary btn-sm">Nouvel achat</a>',
])

<div class="admin-page-header page-header md:hidden mb-4">
    <h1 class="text-xl font-bold">Achats</h1>
</div>

@include('admin.pharmacy.partials.nav')

<form method="GET" class="flex flex-wrap gap-2 mb-4">
    <select name="status" class="form-input">
        <option value="">Tous statuts</option>
        @foreach(['unpaid' => 'Impayé', 'partial' => 'Partiel', 'paid' => 'Payé'] as $k => $label)
            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="supplier_id" class="form-input">
        <option value="">Tous fournisseurs</option>
        @foreach($suppliers as $s)
            <option value="{{ $s->id }}" @selected((string) request('supplier_id') === (string) $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    <button class="btn btn-outline btn-sm" type="submit">Filtrer</button>
</form>

<div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Facture</th>
                    <th class="px-4 py-2">Fournisseur</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-right">Payé</th>
                    <th class="px-4 py-2">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr class="border-t border-[var(--border-light)] hover:bg-[var(--bg-hover)]">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.pharmacy.purchases.show', $purchase) }}" class="text-[var(--primary)]">
                                {{ $purchase->purchased_at->format('d/m/Y') }}
                            </a>
                        </td>
                        <td class="px-4 py-2">{{ $purchase->invoice_number ?: '#'.$purchase->id }}</td>
                        <td class="px-4 py-2">{{ $purchase->supplier?->name }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($purchase->total_amount, 2) }} $</td>
                        <td class="px-4 py-2 text-right">{{ number_format($purchase->paid_amount, 2) }} $</td>
                        <td class="px-4 py-2">{{ $purchase->statusLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-[var(--text-secondary)]">Aucun achat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $purchases->links() }}</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Achat #'.$purchase->id)

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Achat '.($purchase->invoice_number ?: '#'.$purchase->id),
    'subtitle' => $purchase->supplier?->name.' · '.$purchase->purchased_at->format('d/m/Y'),
    'actions' => '<a href="'.route('admin.pharmacy.purchases.index').'" class="btn btn-outline btn-sm">Liste</a>',
])

@include('admin.pharmacy.partials.nav')

<div class="grid md:grid-cols-3 gap-4 mb-4">
    <div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
        <p class="text-xs text-[var(--text-tertiary)]">Total</p>
        <p class="text-xl font-bold">{{ number_format($purchase->total_amount, 2) }} $</p>
    </div>
    <div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
        <p class="text-xs text-[var(--text-tertiary)]">Payé / reste</p>
        <p class="text-xl font-bold">{{ number_format($purchase->paid_amount, 2) }} $ / {{ number_format($purchase->remainingAmount(), 2) }} $</p>
        <p class="text-sm mt-1">{{ $purchase->statusLabel() }}</p>
    </div>
    <div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
        <form method="POST" action="{{ route('admin.pharmacy.purchases.payment', $purchase) }}" class="space-y-2">
            @csrf
            @method('PUT')
            <label class="form-label">Mettre à jour le payé</label>
            <input type="number" step="0.01" min="0" name="paid_amount" value="{{ $purchase->paid_amount }}" class="form-input" required>
            <button class="btn btn-outline btn-sm" type="submit">Enregistrer</button>
        </form>
    </div>
</div>

<div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                <th class="px-4 py-2">Produit</th>
                <th class="px-4 py-2 text-right">Qté</th>
                <th class="px-4 py-2 text-right">Coût</th>
                <th class="px-4 py-2 text-right">Ligne</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
                <tr class="border-t border-[var(--border-light)]">
                    <td class="px-4 py-2">{{ $item->product?->name }}</td>
                    <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($item->unit_cost, 2) }} $</td>
                    <td class="px-4 py-2 text-right">{{ number_format($item->line_total, 2) }} $</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($purchase->notes)
    <p class="mt-3 text-sm text-[var(--text-secondary)]">{{ $purchase->notes }}</p>
@endif
@endsection

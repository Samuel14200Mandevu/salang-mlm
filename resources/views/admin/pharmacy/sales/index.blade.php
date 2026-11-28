@extends('admin.layouts.app')

@section('title', 'Pharmacie — Ventes')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Ventes pharmacie',
    'subtitle' => 'Tickets, assurances, marges',
    'actions' => '<a href="'.route('admin.pharmacy.sales.create').'" class="btn btn-primary btn-sm">Nouvelle vente</a>',
])

<div class="admin-page-header page-header md:hidden mb-4">
    <h1 class="text-xl font-bold">Ventes</h1>
</div>

@include('admin.pharmacy.partials.nav')

<form method="GET" class="flex flex-wrap gap-2 mb-4">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Ticket / client" class="form-input">
    <select name="payment_method" class="form-input">
        <option value="">Tous paiements</option>
        @foreach(\App\Models\Pharmacy\Sale::PAYMENT_METHODS as $k => $label)
            <option value="{{ $k }}" @selected(request('payment_method') === $k)>{{ $label }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" class="form-input">
    <input type="date" name="to" value="{{ request('to') }}" class="form-input">
    <button class="btn btn-outline btn-sm" type="submit">Filtrer</button>
</form>

<div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                    <th class="px-4 py-2">Ticket</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Client</th>
                    <th class="px-4 py-2">Paiement</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-right">Assur.</th>
                    <th class="px-4 py-2 text-right">Marge</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr class="border-t border-[var(--border-light)]">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.pharmacy.sales.show', $sale) }}" class="text-[var(--primary)] font-medium">{{ $sale->ticket_number }}</a>
                        </td>
                        <td class="px-4 py-2">{{ $sale->sold_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $sale->patient?->name ?? $sale->customer_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $sale->paymentMethodLabel() }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($sale->total_amount, 2) }} $</td>
                        <td class="px-4 py-2 text-right">{{ number_format($sale->insurance_amount, 2) }} $</td>
                        <td class="px-4 py-2 text-right">{{ number_format($sale->profit_amount, 2) }} $</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-[var(--text-secondary)]">Aucune vente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $sales->links() }}</div>
@endsection

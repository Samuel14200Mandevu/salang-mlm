@extends('admin.layouts.app')

@section('title', 'Vente '.$sale->ticket_number)

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => $sale->ticket_number,
    'subtitle' => $sale->sold_at->format('d/m/Y').' · '.$sale->paymentMethodLabel(),
    'actions' => '<a href="'.route('admin.pharmacy.sales.index').'" class="btn btn-outline btn-sm">Liste</a>',
])

@include('admin.pharmacy.partials.nav')

<div class="grid md:grid-cols-4 gap-3 mb-4">
    @foreach([
        ['Total', number_format($sale->total_amount, 2).' $'],
        ['Part patient', number_format($sale->patient_amount, 2).' $'],
        ['Assurance', number_format($sale->insurance_amount, 2).' $'],
        ['Marge', number_format($sale->profit_amount, 2).' $'],
    ] as [$label, $value])
        <div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
            <p class="text-xs text-[var(--text-tertiary)]">{{ $label }}</p>
            <p class="text-xl font-bold">{{ $value }}</p>
        </div>
    @endforeach
</div>

<p class="text-sm text-[var(--text-secondary)] mb-3">
    Client : <strong>{{ $sale->patient?->name ?? $sale->customer_name ?? '—' }}</strong>
    @if($sale->insurer)
        · Assurance : {{ $sale->insurer->name }} ({{ number_format($sale->coverage_rate * 100, 0) }}%)
    @endif
</p>

<div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                <th class="px-4 py-2">Produit</th>
                <th class="px-4 py-2 text-right">Qté</th>
                <th class="px-4 py-2 text-right">Prix</th>
                <th class="px-4 py-2 text-right">Ligne</th>
                <th class="px-4 py-2 text-right">Marge</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr class="border-t border-[var(--border-light)]">
                    <td class="px-4 py-2">{{ $item->product?->name }}</td>
                    <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($item->unit_price, 2) }} $</td>
                    <td class="px-4 py-2 text-right">{{ number_format($item->line_total, 2) }} $</td>
                    <td class="px-4 py-2 text-right">{{ number_format($item->profit, 2) }} $</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Stock — '.$product->name)

@section('content')
@php
    $stockSubtitle = 'Stock actuel : '.$product->stock
        .' · '.($product->sku ? 'SKU '.$product->sku : 'Sans SKU');
@endphp
<div class="space-y-4 sm:space-y-6">

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Stock</h1>
                <p class="page-subtitle">{{ $product->name }} · {{ $stockSubtitle }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline btn-sm btn-round-icon" title="Détail" aria-label="Détail">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="hidden md:flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Gestion du stock</h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                <strong>{{ $product->name }}</strong> · {{ $stockSubtitle }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline btn-sm">Détail</a>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline btn-sm">Modifier</a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 salang-flash-success border rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 salang-flash-error border rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="card p-3 sm:p-4 lg:col-span-1">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm mb-3">Mouvement</h3>
            <form method="POST" action="{{ route('admin.products.stock.update', $product->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label for="action" class="block text-xs font-semibold text-[var(--text-secondary)] mb-1">Type</label>
                    <select name="action" id="action" class="form-control" required>
                        <option value="entry" @selected(old('action') === 'entry')>Entrée (+)</option>
                        <option value="exit" @selected(old('action') === 'exit')>Sortie (−)</option>
                        <option value="adjust" @selected(old('action') === 'adjust')>Ajustement (= inventaire)</option>
                    </select>
                </div>
                <div>
                    <label for="quantity" class="block text-xs font-semibold text-[var(--text-secondary)] mb-1">
                        Quantité <span id="qtyHint" class="font-normal text-[var(--text-tertiary)]">(unités)</span>
                    </label>
                    <input type="number"
                           name="quantity"
                           id="quantity"
                           min="0"
                           value="{{ old('quantity', 1) }}"
                           class="form-control"
                           required>
                    <p class="text-xs text-[var(--text-tertiary)] mt-1" id="qtyHelp">
                        Entrée / sortie : quantité à ajouter ou retirer. Ajustement : stock final souhaité.
                    </p>
                </div>
                <div>
                    <label for="reason" class="block text-xs font-semibold text-[var(--text-secondary)] mb-1">Motif</label>
                    <input type="text"
                           name="reason"
                           id="reason"
                           maxlength="255"
                           value="{{ old('reason') }}"
                           class="form-control"
                           placeholder="Réception, casse, inventaire…">
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-full">Enregistrer</button>
            </form>
        </div>

        <div class="card p-3 sm:p-4 lg:col-span-2">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Historique des mouvements</h3>
                <span class="text-xs text-[var(--text-tertiary)]">{{ $movements->total() }} ligne(s)</span>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Δ</th>
                            <th>Avant</th>
                            <th>Après</th>
                            <th class="hidden sm:table-cell">Motif</th>
                            <th class="hidden md:table-cell">Par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td class="text-xs text-[var(--text-tertiary)] whitespace-nowrap">
                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <span class="badge {{ $movement->quantity_delta >= 0 ? 'badge-success' : 'badge-danger' }}">
                                        {{ $movement->type_label }}
                                    </span>
                                </td>
                                <td class="font-semibold {{ $movement->quantity_delta >= 0 ? 'text-[var(--ui-stat-success)]' : 'text-[var(--ui-stat-danger)]' }}">
                                    {{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }}
                                </td>
                                <td class="text-xs">{{ $movement->quantity_before }}</td>
                                <td class="text-xs font-semibold">{{ $movement->quantity_after }}</td>
                                <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)] max-w-xs truncate" title="{{ $movement->reason }}">
                                    {{ $movement->reason ?: '—' }}
                                </td>
                                <td class="hidden md:table-cell text-xs">
                                    {{ $movement->user?->name ?? 'Système' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                    <p class="text-base font-medium text-[var(--text-primary)]">Aucun mouvement</p>
                                    <p class="text-sm text-[var(--text-tertiary)] mt-1">Les entrées, sorties et ventes apparaîtront ici</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="mt-3">
                    <x-salang-pagination :paginator="$movements" />
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('action')?.addEventListener('change', function () {
    const help = document.getElementById('qtyHelp');
    const input = document.getElementById('quantity');
    if (!help || !input) return;
    if (this.value === 'adjust') {
        help.textContent = 'Ajustement : saisissez le stock final souhaité après inventaire.';
        input.value = @json((int) $product->stock);
    } else {
        help.textContent = 'Entrée / sortie : quantité à ajouter ou retirer du stock actuel.';
        if (Number(input.value) === 0) input.value = 1;
    }
});
</script>
@endpush
@endsection

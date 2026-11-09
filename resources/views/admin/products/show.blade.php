@extends('admin.layouts.app')

@section('title', 'Détails produit — '.$product->name)

@push('styles')
<style>
[x-cloak] { display: none !important; }

.product-show-desk .card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.product-show-desk .badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-color: var(--border-color);
    text-decoration: none;
}
.product-show-desk .badge-success {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.product-show-desk .badge-danger {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}
.product-show-desk .badge-warning {
    background: rgba(181, 71, 8, 0.12);
    color: #B54708;
    border-color: rgba(181, 71, 8, 0.15);
}
.product-show-desk .dest-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}
@media (min-width: 640px) {
    .product-show-desk .dest-grid.is-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (min-width: 1024px) {
    .product-show-desk .layout-split {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }
}
.product-show-desk .dest-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.875rem 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.product-show-desk .dest-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(30, 93, 173, 0.14);
    color: #1E5DAD;
}
.product-show-desk .dest-icon.is-success {
    background: rgba(28, 126, 74, 0.14);
    color: #1C7E4A;
}
.product-show-desk .dest-icon.is-warning {
    background: rgba(181, 71, 8, 0.14);
    color: #B54708;
}
.product-show-desk .dest-icon.is-danger {
    background: rgba(185, 28, 28, 0.14);
    color: #B91C1C;
}
.product-show-desk .detail-field + .detail-field {
    margin-top: 0.75rem;
}
.product-show-desk .detail-field label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.product-show-desk .detail-field .value {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    line-height: 1.4;
}
.product-show-desk .detail-field .value.is-muted {
    color: var(--text-secondary);
}
.product-show-desk .detail-row {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    padding: 1rem 1.1rem;
}
.product-show-desk .detail-row + .detail-row {
    margin-top: 0.75rem;
}
.product-show-desk .detail-row__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.product-show-desk .detail-row__index {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #1E5DAD;
    background: rgba(30, 93, 173, 0.14);
}
.product-show-desk .detail-row__label {
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
}
.product-show-desk .detail-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}
.product-show-desk .product-thumb {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    flex-shrink: 0;
}
.product-show-desk .product-thumb-fallback {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--text-tertiary);
}
.product-show-desk .fields-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem 1.25rem;
}
@media (min-width: 640px) {
    .product-show-desk .fields-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (min-width: 1024px) {
    .product-show-desk .fields-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (min-width: 1280px) {
    .product-show-desk .fields-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
.product-show-desk .fields-grid .detail-field + .detail-field {
    margin-top: 0;
}
.product-show-desk .table-wrap { overflow-x: auto; }
.product-show-desk .table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.product-show-desk .table thead th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}
.product-show-desk .table tbody td {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    vertical-align: middle;
    border-bottom: 1px solid var(--border-light);
}
.product-show-desk .table-striped tbody tr:nth-child(even) {
    background: var(--bg-secondary);
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}
.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.modal-box {
    background: var(--bg-card);
    border-radius: 12px;
    padding: 1.75rem;
    max-width: 440px;
    width: 90%;
    border: 1px solid var(--border-color);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}
.modal-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    background: rgba(185, 28, 28, 0.1);
    color: #B91C1C;
}
.modal-title {
    text-align: center;
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
}
.modal-text {
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 1.25rem;
    line-height: 1.6;
}
.modal-text .text-danger { color: var(--danger); }
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
</style>
@endpush

@section('content')
@php
    $hasImage = $product->image && file_exists(storage_path('app/public/products/'.$product->image));
    $hasHalf = $product->hasHalfOption();

    $stockTone = match (true) {
        $product->stock <= 0 => 'danger',
        $product->stock <= 5 => 'warning',
        default => 'success',
    };
    $stockLabel = match (true) {
        $product->stock <= 0 => 'Rupture',
        $product->stock <= 5 => $product->stock.' restants',
        default => $product->stock.' en stock',
    };

    $cost = $product->cost !== null ? (float) $product->cost : null;
    $salePrice = (float) $product->price;
    $fullPrice = $product->full_price !== null ? (float) $product->full_price : $salePrice;
    $fullPv = $product->full_pv !== null ? (int) $product->full_pv : (int) ($product->pv_value ?? 0);
    $halfPrice = $hasHalf ? (float) $product->half_price : null;
    $halfPv = $hasHalf ? (int) $product->half_pv : null;
    $marginFull = ($cost !== null && $fullPrice > 0) ? $fullPrice - $cost : null;

    $money = fn (?float $value) => $value === null
        ? '—'
        : number_format($value, 2).' $';

    $productShowSubtitle = $product->name
        .($product->sku ? ' · SKU '.$product->sku : '')
        .' · Flacon '.$money($fullPrice);

    $ficheFields = array_filter([
        ['label' => 'Prix catalogue', 'value' => $money($salePrice)],
        ['label' => 'PV catalogue', 'value' => ((int) ($product->pv_value ?? 0)).' PV'],
        ['label' => 'SKU', 'value' => $product->sku ?: null, 'mono' => true],
        ['label' => 'Catégorie', 'value' => $product->category ?: null],
        ['label' => 'Unité', 'value' => $product->unit ?: null],
        ['label' => 'Conditionnement', 'value' => $product->packaging ?: null],
        ['label' => 'Dosage', 'value' => $product->dosage ?: null],
        ['label' => 'Slug', 'value' => $product->slug ?: null, 'mono' => true],
        ['label' => 'ID', 'value' => '#'.$product->id, 'mono' => true],
        ['label' => 'Créé le', 'value' => optional($product->created_at)->format('d/m/Y')],
        ['label' => 'Mis à jour', 'value' => optional($product->updated_at)->format('d/m/Y H:i')],
    ], fn ($field) => filled($field['value'] ?? null));
@endphp

{{-- Boutons : structure détail membre --}}
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Détails produit',
    'subtitle' => $productShowSubtitle,
    'actions' => view('admin.products.partials.show-header-actions', [
        'product' => $product,
        'hasBarcode' => $hasBarcode,
    ])->render(),
])

<div class="admin-page-header page-header md:hidden animate-fadeInUp">
    <div class="admin-mobile-page-head is-mobile-banner">
        <div class="admin-title-banner__text">
            <h1 class="page-title">Détails produit</h1>
            <p class="page-subtitle">{{ $productShowSubtitle }}</p>
        </div>
        @include('admin.products.partials.show-mobile-banner-actions', [
            'product' => $product,
            'hasBarcode' => $hasBarcode,
        ])
    </div>
</div>

{{-- Contenu : structure précédente (cartes + fiche pleine largeur) --}}
<div class="product-show-desk space-y-4 w-full">

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="dest-grid is-3 animate-fadeInUp delay-1">
        <div class="dest-card">
            <span class="dest-icon" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $money($cost) }}</p>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">Prix d’achat</p>
            </div>
        </div>
        <div class="dest-card">
            <span class="dest-icon is-success" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $money($fullPrice) }}</p>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">Prix flacon · {{ $fullPv }} PV</p>
            </div>
        </div>
        @if($hasHalf)
            <div class="dest-card">
                <span class="dest-icon is-warning" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $money($halfPrice) }}</p>
                    <p class="text-xs text-[var(--text-secondary)] mt-0.5">Prix 1/2 · {{ $halfPv }} PV</p>
                </div>
            </div>
        @else
            <div class="dest-card">
                <span class="dest-icon is-{{ $stockTone }}" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $product->stock }}</p>
                    <p class="text-xs text-[var(--text-secondary)] mt-0.5">{{ $stockLabel }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="layout-split">
        <div class="card animate-fadeInUp delay-2">
            <div class="flex items-start gap-3 mb-4">
                @if($hasImage)
                    <img src="{{ asset('storage/products/'.$product->image) }}"
                         alt="{{ $product->name }}"
                         class="product-thumb">
                @else
                    <span class="product-thumb product-thumb-fallback" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </span>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[var(--text-primary)]">Fiche produit</p>
                            <p class="text-xs text-[var(--text-tertiary)] mt-0.5">
                                Identité, conditionnement et références catalogue.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2 shrink-0 ml-auto">
                            <a href="{{ route('admin.products.stock', $product->id) }}" class="badge badge-{{ $stockTone }}">
                                {{ $stockLabel }}
                            </a>
                            <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $product->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            @if($product->is_featured)
                                <span class="badge badge-warning">Vedette</span>
                            @endif
                            @if($hasBarcode)
                                <span class="badge badge-success">Code-barres</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($product->description)
                <div class="detail-field mb-4">
                    <label>Description</label>
                    <p class="value is-muted whitespace-pre-line">{{ Str::limit(strip_tags($product->description), 280) }}</p>
                </div>
            @endif

            <div class="fields-grid">
                @foreach($ficheFields as $field)
                    <div class="detail-field">
                        <label>{{ $field['label'] }}</label>
                        <p @class(['value', 'font-mono text-xs' => !empty($field['mono'])])>
                            {{ $field['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            @if($marginFull !== null)
                <p class="text-xs text-[var(--text-tertiary)] mt-4">
                    Marge flacon :
                    <span class="font-semibold text-[var(--text-secondary)]">{{ $money($marginFull) }}</span>
                    @if($fullPrice > 0)
                        · {{ number_format(($marginFull / $fullPrice) * 100, 1) }} %
                    @endif
                </p>
            @endif
        </div>

        <div class="space-y-4">
            <article class="detail-row animate-fadeInUp">
                <div class="detail-row__top">
                    <div class="flex items-center gap-2">
                        <span class="detail-row__index">CB</span>
                        <span class="detail-row__label">Code-barres</span>
                    </div>
                    <span class="badge {{ $hasBarcode ? 'badge-success' : '' }}">
                        {{ $hasBarcode ? 'Prêt' : 'À générer' }}
                    </span>
                </div>
                <p class="text-sm text-[var(--text-secondary)] mb-3">
                    {{ $hasBarcode
                        ? 'Code 128 = SKU « '.$product->sku.' » — scannable à la caisse.'
                        : 'Générez un code-barres (SKU) pour la douchette POS.' }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.products.generate-barcode', $product->id) }}" class="btn btn-primary btn-sm">
                        {{ $hasBarcode ? 'Régénérer' : 'Générer' }}
                    </a>
                    <a href="{{ route('admin.products.barcode', $product->id) }}" class="btn btn-outline btn-sm">Voir / imprimer</a>
                    @if($hasBarcode)
                        <a href="{{ route('admin.products.download-barcode', $product->id) }}" class="btn btn-outline btn-sm">Télécharger</a>
                    @endif
                </div>
            </article>
        </div>
    </div>

    <div class="card animate-fadeInUp">
        <div class="flex items-center justify-between gap-2 mb-3">
            <div>
                <h3 class="text-sm sm:text-base font-semibold text-[var(--text-primary)]">Mouvements récents</h3>
                <p class="text-xs text-[var(--text-tertiary)] mt-0.5">{{ $recentMovements->count() }} dernière(s) ligne(s)</p>
            </div>
            <a href="{{ route('admin.products.stock', $product->id) }}" class="btn btn-outline btn-sm">
                Voir tout
            </a>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
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
                    @forelse($recentMovements as $movement)
                        <tr>
                            <td class="text-xs text-[var(--text-tertiary)] whitespace-nowrap">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge {{ $movement->quantity_delta >= 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $movement->type_label }}
                                </span>
                            </td>
                            <td class="font-semibold {{ $movement->quantity_delta >= 0 ? 'text-[#1C7E4A]' : 'text-[#B91C1C]' }}">
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
    </div>
</div>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirmer la suppression</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <span class="text-danger">supprimer définitivement</span>
            <strong>{{ $product->name }}</strong> ?
            <br>
            Cette action est <span class="text-danger">irréversible</span>.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm">Annuler</button>
            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-primary btn-sm">Supprimer</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});
</script>
@endpush
@endsection

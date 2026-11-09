{{-- resources/views/admin/products/barcodes-list.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
.barcode-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    height: 100%;
}
.barcode-card img, .barcode-card svg {
    max-width: 100%;
    height: auto;
    max-height: 64px;
    background: white;
}
.barcode-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
}
.barcode-card .product-name {
    font-size: 0.813rem;
    font-weight: 600;
    margin-top: 0.625rem;
}
.barcode-card .product-sku {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.75rem;
    color: var(--primary-blue);
    font-weight: 700;
    letter-spacing: 0.04em;
}
.barcode-card .product-price {
    font-size: 0.75rem;
    color: #1C7E4A;
    font-weight: 700;
}
.barcode-card .btn-actions {
    margin-top: 0.625rem;
    display: flex;
    gap: 0.375rem;
    justify-content: center;
}
.barcode-card .btn-actions .btn {
    padding: 0.25rem 0.75rem;
    font-size: 0.688rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
}
.btn-primary { background: var(--primary-blue); color: white; }
.btn-outline { background: transparent; color: var(--primary-blue); border: 1px solid var(--primary-blue); }
.header-actions { display: flex; gap: 0.625rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.alert-info {
    background: var(--primary-blue-bg);
    border: 1px solid var(--primary-blue-border);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: var(--primary-blue);
    margin-bottom: 1.25rem;
}
.page-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.page-header h1 { font-size: 1.25rem; font-weight: 700; }
</style>
@endpush

@section('title', 'Codes-barres des produits')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1>Codes-barres des produits</h1>
        <div class="header-actions">
            <a href="{{ route('admin.products.generate-all-barcodes') }}" class="btn btn-outline">
                Générer tous
            </a>
            <a href="{{ route('admin.products.print-all-barcodes') }}" class="btn btn-success" target="_blank">
                Imprimer toutes les étiquettes
            </a>
            <a href="{{ route('admin.products') }}" class="btn btn-outline">Retour</a>
        </div>
    </div>

    <div class="alert-info">
        <strong>Information :</strong> Chaque code-barres (Code 128) contient le <strong>SKU</strong> du produit.
        Scannez-le avec la douchette au guichet POS pour l’ajouter au panier.
    </div>

    <div class="barcode-grid">
        @foreach($products as $product)
            @php
                $path = $product->metadata['barcode_svg'] ?? null;
                $hasBarcode = $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
            @endphp
            <div class="barcode-card">
                @if($hasBarcode)
                    <img src="{{ asset('storage/' . $path) }}" alt="Code-barres {{ $product->name }}">
                @else
                    <div style="height:64px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;border-radius:8px;color:#999;font-size:0.75rem;">
                        À générer
                    </div>
                @endif

                <div class="product-name">{{ Str::limit($product->name, 28) }}</div>
                <div class="product-sku">{{ $product->sku ?: '—' }}</div>
                <div class="product-price">${{ number_format($product->display_price ?? $product->price, 2) }}</div>

                <div class="btn-actions">
                    <a href="{{ route('admin.products.barcode', $product->id) }}" class="btn btn-primary">Voir</a>
                    <a href="{{ route('admin.products.download-barcode', $product->id) }}" class="btn btn-outline">↓</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

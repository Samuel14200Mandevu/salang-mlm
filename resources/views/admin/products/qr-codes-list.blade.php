{{-- resources/views/admin/products/qr-codes-list.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.qr-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    transition: box-shadow 0.15s ease, transform 0.1s ease;
    height: 100%;
}
.qr-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.qr-card:active {
    transform: scale(0.98);
}

.qr-card img {
    max-width: 120px;
    height: auto;
    background: white;
    padding: 4px;
}

.qr-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.25rem;
}

.qr-card .product-name {
    font-size: 0.813rem;
    font-weight: 600;
    margin-top: 0.625rem;
    color: #1a1a1a;
}
.qr-card .product-id {
    font-size: 0.688rem;
    color: var(--primary-blue);
    font-weight: 700;
}
.qr-card .product-price {
    font-size: 0.75rem;
    color: #1C7E4A;
    font-weight: 700;
}
.qr-card .product-sku {
    font-size: 0.625rem;
    color: #999;
}

.qr-card .btn-actions {
    margin-top: 0.625rem;
    display: flex;
    gap: 0.375rem;
    justify-content: center;
}
.qr-card .btn-actions .btn {
    padding: 0.25rem 0.75rem;
    font-size: 0.688rem;
    border-radius: 4px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.15s ease;
}

.btn-primary { background: var(--primary-blue); color: white; }
.btn-primary:hover { background: var(--primary-blue-dark); }

.btn-outline { background: transparent; color: var(--primary-blue); border: 1px solid var(--primary-blue); }
.btn-outline:hover { background: var(--primary-blue); color: white; }

.btn-success { background: #1C7E4A; color: white; }
.btn-success:hover { background: #14633A; }

.header-actions {
    display: flex;
    gap: 0.625rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.alert-info {
    background: var(--primary-blue-bg);
    border: 1px solid var(--primary-blue-border);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: var(--primary-blue);
    margin-bottom: 1.25rem;
}
.alert-info strong {
    font-weight: 600;
}

.page-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.page-header h1 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-actions {
        width: 100%;
        flex-direction: column;
    }
    .header-actions .btn {
        width: 100%;
        text-align: center;
    }
    .qr-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 0.75rem;
    }
    .qr-card {
        padding: 0.75rem;
    }
    .qr-card img {
        max-width: 80px;
    }
}
</style>
@endpush

@section('title', 'QR Codes des produits')

@section('content')
<div class="container-fluid">

    <div class="page-header">
        <h1>QR Codes des produits</h1>
        <div class="header-actions">
            <a href="{{ route('admin.products.print-all-qr') }}" class="btn btn-success" target="_blank">
                Imprimer toutes les étiquettes
            </a>
            <a href="{{ route('admin.products') }}" class="btn btn-outline">
                Retour
            </a>
        </div>
    </div>

    <div class="alert-info">
        <strong>Information :</strong> Chaque QR code contient l'ID du produit.
        Scannez-le au guichet POS pour ajouter automatiquement le produit au panier.
    </div>

    <div class="qr-grid">
        @foreach($products as $product)
            @php
                $qrPath = $product->metadata['qr_code_svg'] ?? null;
                $hasQr = $qrPath && Storage::disk('public')->exists($qrPath);
            @endphp
            <div class="qr-card">
                @if($hasQr)
                    <img src="{{ asset('storage/' . $qrPath) }}" alt="QR Code {{ $product->name }}">
                @else
                    <div style="width:120px;height:120px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;margin:0 auto;border-radius:8px;color:#999;font-size:0.75rem;">
                        QR non généré
                    </div>
                @endif

                <div class="product-name">{{ Str::limit($product->name, 25) }}</div>
                <div class="product-id">ID: {{ $product->id }}</div>
                <div class="product-price">${{ number_format($product->price, 2) }}</div>
                <div class="product-sku">SKU: {{ $product->sku }}</div>

                <div class="btn-actions">
                    <a href="{{ route('admin.products.qr-code', $product->id) }}" class="btn btn-primary">
                        Voir
                    </a>
                    <a href="{{ route('admin.products.download-qr', $product->id) }}" class="btn btn-outline">
                        ↓
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    
</div>
@endsection
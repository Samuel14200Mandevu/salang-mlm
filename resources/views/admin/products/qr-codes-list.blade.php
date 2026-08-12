{{-- resources/views/admin/products/qr-codes-list.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .qr-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s ease;
        height: 100%;
    }
    .qr-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .qr-card img {
        max-width: 120px;
        height: auto;
        background: white;
        padding: 5px;
    }
    .qr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
    }
    .qr-card .product-name {
        font-size: 13px;
        font-weight: 600;
        margin-top: 10px;
        color: #1a1a1a;
    }
    .qr-card .product-id {
        font-size: 11px;
        color: #0E2F76;
        font-weight: 700;
    }
    .qr-card .product-price {
        font-size: 12px;
        color: #22c55e;
        font-weight: 700;
    }
    .qr-card .btn-actions {
        margin-top: 10px;
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    .qr-card .btn-actions .btn {
        padding: 4px 12px;
        font-size: 11px;
        border-radius: 4px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    .btn-primary { background: #0E2F76; color: white; }
    .btn-primary:hover { background: #1a3f8a; }
    .btn-outline { background: transparent; color: #0E2F76; border: 1px solid #0E2F76; }
    .btn-outline:hover { background: #0E2F76; color: white; }
    .btn-success { background: #22c55e; color: white; }
    .btn-success:hover { background: #16a34a; }
    
    .header-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
</style>
@endpush

@section('title', 'QR Codes des produits')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1> QR Codes des produits</h1>
        <div class="header-actions">
            <a href="{{ route('admin.products.print-all-qr') }}" class="btn btn-success" target="_blank">
                 Imprimer toutes les étiquettes
            </a>
            <a href="{{ route('admin.products') }}" class="btn btn-outline">
                ↩ Retour
            </a>
        </div>
    </div>
    
    <div class="alert alert-info">
        <strong> Information :</strong> Chaque QR code contient l'ID du produit. 
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
                    <div style="width:120px;height:120px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;margin:0 auto;border-radius:8px;color:#999;font-size:12px;">
                        QR non généré
                    </div>
                @endif
                
                <div class="product-name">{{ Str::limit($product->name, 25) }}</div>
                <div class="product-id">ID: {{ $product->id }}</div>
                <div class="product-price">${{ number_format($product->price, 2) }}</div>
                <div style="font-size:10px;color:#999;">SKU: {{ $product->sku }}</div>
                
                <div class="btn-actions">
                    <a href="{{ route('admin.products.qr-code', $product->id) }}" class="btn btn-primary">
                        Voir
                    </a>
                    <a href="{{ route('admin.products.download-qr', $product->id) }}" class="btn btn-outline">
                        ⬇
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
{{-- resources/views/admin/orders/show.blade.php --}}
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

.order-detail-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.order-detail-card:hover {
    border-color: var(--primary-blue);
}

.order-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.order-status-badge-pending {
    background: rgba(181, 71, 8, 0.12);
    color: #B54708;
    border-color: rgba(181, 71, 8, 0.15);
}
.order-status-badge-processing {
    background: rgba(6, 95, 156, 0.12);
    color: #065F9C;
    border-color: rgba(6, 95, 156, 0.15);
}
.order-status-badge-completed {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.order-status-badge-cancelled {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}

.badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.badge-success { background: rgba(28, 126, 74, 0.12); color: #1C7E4A; border-color: rgba(28, 126, 74, 0.15); }
.badge-warning { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }
.badge-danger { background: rgba(185, 28, 28, 0.12); color: #B91C1C; border-color: rgba(185, 28, 28, 0.15); }
.badge-info { background: var(--primary-blue-bg); color: var(--primary-blue); border-color: var(--primary-blue-border); }
.badge-cancellation-pending { background: rgba(181, 71, 8, 0.12); color: #B54708; border-color: rgba(181, 71, 8, 0.15); }

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-success {
    background: #1C7E4A;
    color: white;
    border-color: #1C7E4A;
}
.btn-success:hover {
    background: #14633A;
    border-color: #14633A;
}

.btn-danger {
    background: #B91C1C;
    color: white;
    border-color: #B91C1C;
}
.btn-danger:hover {
    background: #991B1B;
    border-color: #991B1B;
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.item-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    background: var(--bg-secondary);
    border-radius: 8px;
    gap: 0.5rem;
}
.item-card .item-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 8px;
    background: var(--bg-card);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    border: 1px solid var(--border-color);
}
.item-card .item-icon svg {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--text-tertiary);
}
.item-card .item-info {
    flex: 1;
    min-width: 0;
}
.item-card .item-info .item-name {
    font-weight: 500;
    font-size: 0.813rem;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.item-card .item-info .item-meta {
    font-size: 0.7rem;
    color: var(--text-secondary);
}
.item-card .item-total {
    font-weight: 700;
    font-size: 0.813rem;
    color: var(--primary-blue);
    text-align: right;
    flex-shrink: 0;
}
.item-card .item-total .unit-price {
    font-weight: 400;
    font-size: 0.65rem;
    color: var(--text-tertiary);
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}
.timeline-item:last-child {
    border-bottom: none;
}
.timeline-dot {
    width: 0.625rem;
    height: 0.625rem;
    border-radius: 50%;
    margin-top: 0.25rem;
    flex-shrink: 0;
}
.timeline-dot-success { background: #1C7E4A; }
.timeline-dot-warning { background: #B54708; }
.timeline-dot-info { background: #065F9C; }
.timeline-dot-danger { background: #B91C1C; }

.cancellation-request-box {
    background: rgba(181, 71, 8, 0.05);
    border: 1px solid rgba(181, 71, 8, 0.15);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}
.cancellation-request-box .request-reason {
    background: var(--bg-secondary);
    padding: 0.625rem;
    border-radius: 8px;
    margin: 0.5rem 0;
    font-size: 0.813rem;
    color: var(--text-secondary);
    border-left: 3px solid #B54708;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.modal-overlay.active {
    display: flex;
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
.modal-text .text-danger {
    color: #B91C1C;
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.modal-actions .btn {
    min-width: 90px;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.1s; }
.delay-3 { animation-delay: 0.15s; }
.delay-4 { animation-delay: 0.2s; }

@media (max-width: 640px) {
    .order-detail-card {
        padding: 0.875rem;
    }
    .order-status-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
    }
    .btn {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    .item-card {
        padding: 0.375rem 0.5rem;
        flex-wrap: wrap;
    }
    .item-card .item-total {
        width: 100%;
        text-align: left;
        padding-left: 2.5rem;
    }
    .detail-grid {
        grid-template-columns: 1fr !important;
    }
    .address-grid {
        grid-template-columns: 1fr !important;
    }
    .cancellation-actions {
        flex-direction: column;
    }
    .cancellation-actions .btn {
        width: 100%;
    }
    .modal-box {
        padding: 1.25rem;
    }
    .modal-actions {
        flex-direction: column;
    }
    .modal-actions .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .order-detail-card {
        padding: 0.75rem;
    }
    .item-card .item-icon {
        width: 2rem;
        height: 2rem;
    }
    .item-card .item-icon svg {
        width: 1rem;
        height: 1rem;
    }
    .item-card .item-info .item-name {
        font-size: 0.75rem;
    }
    .item-card .item-info .item-meta {
        font-size: 0.6rem;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Commande #{{ $order->order_number }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">Détails de la commande</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2 flex-wrap">
            <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                Facture
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Status -->
    <div class="animate-fadeInUp delay-1">
        <div class="order-detail-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-[var(--text-secondary)]">Statut de la commande</p>
                    <span class="order-status-badge order-status-badge-{{ $order->status }}">
                        @if($order->status == 'pending') En attente
                        @elseif($order->status == 'processing') En traitement
                        @elseif($order->status == 'completed') Terminée
                        @elseif($order->status == 'cancelled') Annulée
                        @else {{ ucfirst($order->status) }}
                        @endif
                    </span>

                    @if(isset($order->metadata['cancellation_request']) && $order->metadata['cancellation_request']['status'] == 'pending')
                        <span class="badge badge-cancellation-pending ml-2">
                            Demande d'annulation
                        </span>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs text-[var(--text-secondary)]">Date de la commande</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="detail-grid grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-2">

        <!-- Items -->
        <div class="lg:col-span-2 order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Articles</h3>

            <div class="space-y-2 sm:space-y-3">
                @foreach($order->items as $item)
                    <div class="item-card">
                        <div class="item-icon">
                            @if($item->product_id && $item->product && $item->product->image && file_exists(storage_path('app/public/products/' . $item->product->image)))
                                <img src="{{ asset('storage/products/' . $item->product->image) }}"
                                     alt="{{ $item->name }}"
                                     class="w-full h-full object-cover rounded-md">
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                            @endif
                        </div>
                        <div class="item-info">
                            <p class="item-name">{{ $item->name }}</p>
                            <p class="item-meta">
                                Qté: {{ $item->quantity }}
                                @if($item->product_id)
                                    • SKU: {{ $item->sku ?? 'N/A' }}
                                @else
                                    • Package
                                @endif
                            </p>
                        </div>
                        <div class="item-total">
                            ${{ number_format($item->total, 2) }}
                            <div class="unit-price">${{ number_format($item->price, 2) }} / unité</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Summary -->
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Résumé</h3>

            <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Sous-total</span>
                    <span class="font-medium">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">TVA (18%)</span>
                    <span class="font-medium">${{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Livraison</span>
                    <span class="font-medium">${{ number_format($order->shipping, 2) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Réduction</span>
                    <span class="font-medium text-[#B91C1C]">-${{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="border-t border-[var(--border-color)] pt-2 mt-2">
                    <div class="flex justify-between text-base sm:text-lg font-bold">
                        <span>Total</span>
                        <span class="text-[var(--primary-blue)]">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-[var(--border-color)]">
                <div class="flex justify-between text-xs sm:text-sm">
                    <span class="text-[var(--text-secondary)]">Statut paiement</span>
                    <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : ($order->payment_status == 'pending' ? 'badge-warning' : 'badge-danger') }} text-[10px] sm:text-xs">
                        {{ $order->payment_status == 'completed' ? 'Payé' : ($order->payment_status == 'pending' ? 'En attente' : 'Échoué') }}
                    </span>
                </div>
                @if($order->payment_method)
                <div class="flex justify-between text-xs sm:text-sm mt-1">
                    <span class="text-[var(--text-secondary)]">Méthode paiement</span>
                    <span class="font-medium">{{ $order->payment_method }}</span>
                </div>
                @endif
                <div class="flex justify-between text-xs sm:text-sm mt-1">
                    <span class="text-[var(--text-secondary)]">Client</span>
                    <span class="font-medium">{{ $order->user?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-xs sm:text-sm">
                    <span class="text-[var(--text-secondary)]">Email</span>
                    <span class="font-medium">{{ $order->user?->email ?? 'N/A' }}</span>
                </div>
                @if(isset($order->metadata['cashier_name']))
                <div class="flex justify-between text-xs sm:text-sm mt-1">
                    <span class="text-[var(--text-secondary)]">Caissier</span>
                    <span class="font-medium">{{ $order->metadata['cashier_name'] }}</span>
                </div>
                @endif
            </div>

            <!-- Actions Admin -->
            <div class="mt-3 space-y-2">
                @if($order->status == 'pending' || $order->status == 'processing')
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success w-full text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Marquer comme terminée
                        </button>
                    </form>
                @endif

                @if($order->status == 'pending')
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="btn btn-primary w-full text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Passer en traitement
                        </button>
                    </form>
                @endif

                @if($order->status == 'pending')
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST"
                          onsubmit="return confirm('Confirmer l\'annulation de cette commande ?')">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-danger w-full text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Annuler la commande
                        </button>
                    </form>
                @endif
            </div>

            <!-- Cancellation Request (Admin) -->
            @if(isset($order->metadata['cancellation_request']) && $order->metadata['cancellation_request']['status'] == 'pending')
                <div class="cancellation-request-box">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-[#B54708]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h4 class="font-semibold text-[var(--text-primary)] text-sm">Demande d'annulation</h4>
                    </div>

                    <div class="text-xs text-[var(--text-secondary)]">
                        <p>
                            Demandé par <strong>{{ $order->user?->name ?? 'N/A' }}</strong>
                            le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['requested_at'])->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <div class="request-reason">
                        <p class="text-sm"><strong>Motif :</strong></p>
                        <p>{{ $order->metadata['cancellation_request']['reason'] ?? 'Aucun motif fourni' }}</p>
                    </div>

                    <div class="cancellation-actions flex gap-2 mt-3">
                        <form action="{{ route('admin.orders.handle-cancellation', $order) }}" method="POST" class="flex-1">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success w-full text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approuver l'annulation
                            </button>
                        </form>

                        <button onclick="openRejectModal()" class="btn btn-danger flex-1 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeter
                        </button>
                    </div>
                </div>
            @endif

            <!-- Cancellation Request Approved -->
            @if(isset($order->metadata['cancellation_request']) && $order->metadata['cancellation_request']['status'] == 'approved')
                <div class="mt-3 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#1C7E4A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-[#1C7E4A] font-medium">Annulation approuvée</span>
                    </div>
                    <p class="text-xs text-[var(--text-secondary)] mt-1">
                        Traitée le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['processed_at'])->format('d/m/Y H:i') }}
                    </p>
                </div>
            @endif

            <!-- Cancellation Request Rejected -->
            @if(isset($order->metadata['cancellation_request']) && $order->metadata['cancellation_request']['status'] == 'rejected')
                <div class="mt-3 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span class="text-[#B91C1C] font-medium">Annulation rejetée</span>
                    </div>
                    <p class="text-xs text-[var(--text-secondary)] mt-1">
                        Rejetée le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['processed_at'])->format('d/m/Y H:i') }}
                    </p>
                    @if(isset($order->metadata['cancellation_request']['admin_notes']))
                        <p class="text-xs text-[var(--text-secondary)] mt-1">
                            <strong>Motif du rejet :</strong> {{ $order->metadata['cancellation_request']['admin_notes'] }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Addresses -->
    @if($order->shipping_address || $order->billing_address)
    <div class="address-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-3">
        @if($order->shipping_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Adresse de livraison</h3>
            <p class="text-[var(--text-secondary)] text-sm whitespace-pre-line">{{ $order->shipping_address }}</p>
        </div>
        @endif

        @if($order->billing_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2">Adresse de facturation</h3>
            <p class="text-[var(--text-secondary)] text-sm whitespace-pre-line">{{ $order->billing_address }}</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Timeline -->
    <div class="order-detail-card animate-fadeInUp delay-4">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3">Historique</h3>
        <div class="space-y-1">
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot-success"></div>
                <div>
                    <p class="font-medium text-[var(--text-primary)] text-sm">Commande créée</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            @if($order->paid_at)
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot-success"></div>
                <div>
                    <p class="font-medium text-[var(--text-primary)] text-sm">Paiement effectué</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ $order->paid_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            @endif

            @if($order->status == 'processing')
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot-info"></div>
                <div>
                    <p class="font-medium text-[var(--text-primary)] text-sm">En traitement</p>
                    <p class="text-xs text-[var(--text-secondary)]">La commande est en cours de traitement</p>
                </div>
            </div>
            @endif

            @if($order->status == 'completed')
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot-success"></div>
                <div>
                    <p class="font-medium text-[var(--text-primary)] text-sm">Commande terminée</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ $order->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            @endif

            @if($order->status == 'cancelled')
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot-danger"></div>
                <div>
                    <p class="font-medium text-[var(--text-primary)] text-sm">Commande annulée</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ $order->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            @endif

            @if(isset($order->metadata['cancellation_request']))
            <div class="timeline-item">
                <div class="timeline-dot
                    @if($order->metadata['cancellation_request']['status'] == 'pending') timeline-dot-warning
                    @elseif($order->metadata['cancellation_request']['status'] == 'approved') timeline-dot-success
                    @else timeline-dot-danger
                    @endif
                "></div>
                <div>
                    <p class="font-medium text-[var(--text-primary)] text-sm">
                        Demande d'annulation
                        @if($order->metadata['cancellation_request']['status'] == 'pending') en attente
                        @elseif($order->metadata['cancellation_request']['status'] == 'approved') approuvée
                        @else rejetée
                        @endif
                    </p>
                    <p class="text-xs text-[var(--text-secondary)]">
                        @if($order->metadata['cancellation_request']['status'] == 'pending')
                            Demandée le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['requested_at'])->format('d/m/Y H:i') }}
                        @else
                            Traitée le {{ \Carbon\Carbon::parse($order->metadata['cancellation_request']['processed_at'])->format('d/m/Y H:i') }}
                        @endif
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h3 class="modal-title">Rejeter la demande</h3>
        <p class="modal-text">
            Êtes-vous sûr de vouloir <strong class="text-danger">rejeter</strong> cette demande d'annulation ?
        </p>

        <div class="mb-3">
            <label for="rejectReason" class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                Motif du rejet <span class="text-[#B91C1C]">*</span>
            </label>
            <textarea id="rejectReason"
                      class="input w-full p-2 border border-[var(--border-color)] rounded-md text-sm"
                      rows="3"
                      placeholder="Expliquez pourquoi vous rejetez cette demande..."
                      required></textarea>
        </div>

        <div class="modal-actions">
            <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm">
                Annuler
            </button>
            <form action="{{ route('admin.orders.handle-cancellation', $order) }}" method="POST" class="inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="reject">
                <input type="hidden" id="rejectReasonInput" name="admin_notes" value="">
                <button type="submit" class="btn btn-danger btn-sm" onclick="document.getElementById('rejectReasonInput').value = document.getElementById('rejectReason').value;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal() {
    document.getElementById('rejectModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectReason').focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection
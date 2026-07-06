@extends('layouts.app')

@push('styles')
<style>
    .order-detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .order-detail-card:hover {
        border-color: var(--primary-500);
    }
    
    .order-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.875rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    .order-status-badge-pending {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    .order-status-badge-processing {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    .order-status-badge-completed {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .order-status-badge-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .btn-sm {
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    .btn-md {
        padding: 0.625rem 1.5rem;
        font-size: 0.875rem;
    }
    
    .item-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        gap: 0.5rem;
    }
    .item-card .item-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        background: var(--bg-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
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
        font-size: 0.8125rem;
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
        font-size: 0.8125rem;
        color: var(--primary-500);
        text-align: right;
        flex-shrink: 0;
    }
    .item-card .item-total .unit-price {
        font-weight: 400;
        font-size: 0.65rem;
        color: var(--text-tertiary);
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    
    @media (max-width: 640px) {
        .order-detail-card {
            padding: 0.875rem;
        }
        .order-status-badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.6rem;
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
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Order #{{ $order->order_number }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Order details</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm2 2h12v12H6V6zm2 2h8v8H8V8z"/>
                </svg>
                Invoice
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Status -->
    <div class="animate-fadeInUp delay-1">
        <div class="order-detail-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Order Status</p>
                    <span class="order-status-badge order-status-badge-{{ $order->status }}">
                        @if($order->status == 'pending') Pending
                        @elseif($order->status == 'processing') Processing
                        @elseif($order->status == 'completed') Completed
                        @elseif($order->status == 'cancelled') Cancelled
                        @else {{ ucfirst($order->status) }}
                        @endif
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Order Date</p>
                    <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">
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
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Items</h3>

            <div class="space-y-2 sm:space-y-3">
                @foreach($order->items as $item)
                    <div class="item-card">
                        <div class="item-icon">
                            @if($item->product_id && $item->product && $item->product->image && file_exists(storage_path('app/public/products/' . $item->product->image)))
                                <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                     alt="{{ $item->name }}"
                                     class="w-full h-full object-cover rounded-md">
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                            @endif
                        </div>
                        <div class="item-info">
                            <p class="item-name">{{ $item->name }}</p>
                            <p class="item-meta">
                                Qty: {{ $item->quantity }}
                                @if($item->product_id)
                                    • SKU: {{ $item->sku ?? 'N/A' }}
                                @else
                                    • Package
                                @endif
                            </p>
                        </div>
                        <div class="item-total">
                            ${{ number_format($item->total, 2) }}
                            <div class="unit-price">${{ number_format($item->price, 2) }} / unit</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Summary -->
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Summary</h3>

            <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Subtotal</span>
                    <span class="font-medium">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Tax (18%)</span>
                    <span class="font-medium">${{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Shipping</span>
                    <span class="font-medium">${{ number_format($order->shipping, 2) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Discount</span>
                    <span class="font-medium text-red-500">-${{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="border-t border-[var(--border-color)] pt-2 sm:pt-3 mt-2 sm:mt-3">
                    <div class="flex justify-between text-base sm:text-lg font-bold">
                        <span>Total</span>
                        <span class="text-primary-500">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-[var(--border-color)]">
                <div class="flex justify-between text-xs sm:text-sm">
                    <span class="text-[var(--text-secondary)]">Payment Status</span>
                    <span class="badge {{ $order->payment_status == 'completed' ? 'badge-success' : 'badge-warning' }} text-[10px] sm:text-xs">
                        {{ $order->payment_status == 'completed' ? 'Paid' : 'Pending' }}
                    </span>
                </div>
                @if($order->payment_method)
                <div class="flex justify-between text-xs sm:text-sm mt-1 sm:mt-2">
                    <span class="text-[var(--text-secondary)]">Payment Method</span>
                    <span class="font-medium">{{ $order->payment_method }}</span>
                </div>
                @endif
            </div>

            @if($order->status == 'pending')
            <div class="mt-3 sm:mt-4">
                <form action="{{ route('orders.cancel', $order) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to cancel this order?')">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-danger w-full text-sm sm:text-base py-2 sm:py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel Order
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Addresses -->
    @if($order->shipping_address || $order->billing_address)
    <div class="address-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-3">
        @if($order->shipping_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2 sm:mb-3">Shipping Address</h3>
            <p class="text-[var(--text-secondary)] text-sm whitespace-pre-line">{{ $order->shipping_address }}</p>
        </div>
        @endif

        @if($order->billing_address)
        <div class="order-detail-card">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2 sm:mb-3">Billing Address</h3>
            <p class="text-[var(--text-secondary)] text-sm whitespace-pre-line">{{ $order->billing_address }}</p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
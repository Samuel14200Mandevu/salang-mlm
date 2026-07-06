@extends('layouts.app')

@push('styles')
<style>
    .cart-item {
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 0;
    }
    .cart-item:first-child {
        padding-top: 0;
    }
    .cart-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .cart-item:hover {
        background: var(--bg-hover);
        margin: 0 -0.5rem;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        border-radius: var(--radius-sm);
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
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .sticky-top {
        position: sticky;
        top: 1.5rem;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .animate-fadeInLeft { animation: fadeInLeft 0.6s ease forwards; }
    .animate-fadeInRight { animation: fadeInRight 0.6s ease forwards; }
    
    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .cart-item { padding: 0.75rem 0; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .btn-sm { font-size: 0.65rem; padding: 0.25rem 0.5rem; }
        .cart-grid {
            grid-template-columns: 1fr !important;
        }
        .cart-item .item-actions {
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }
    }
    
    @media (max-width: 480px) {
        .cart-item {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }
        .cart-item .item-info {
            text-align: center;
        }
        .cart-item .item-actions {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">My Cart</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Review your items before checkout</p>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($cart))
        <!-- Empty Cart -->
        <div class="card text-center py-8 sm:py-12 animate-fadeIn">
            <svg class="w-16 h-16 sm:w-24 sm:h-24 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.4 8M17 13l2.4 8M9 21a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-lg sm:text-xl font-semibold text-[var(--text-primary)]">Your cart is empty</h3>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-1 sm:mt-2">Discover our products and subscriptions</p>
            <div class="flex flex-wrap justify-center gap-2 sm:gap-3 mt-3 sm:mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary text-sm sm:text-base">View Products</a>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline text-sm sm:text-base">View Subscriptions</a>
            </div>
        </div>
    @else
        <!-- Cart Content -->
        <div class="cart-grid grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            
            <!-- Items -->
            <div class="lg:col-span-2 animate-fadeInLeft">
                <div class="card">
                    <div class="divide-y divide-[var(--border-color)]">
                        @php $total = 0; @endphp
                        @foreach($cart as $key => $item)
                            @php 
                                $itemTotal = $item['price'] * $item['quantity']; 
                                $total += $itemTotal; 
                            @endphp
                            <div class="cart-item flex items-center gap-3 sm:gap-4">
                                <div class="item-info flex-1 min-w-0">
                                    <h4 class="font-medium text-[var(--text-primary)] text-sm sm:text-base truncate">
                                        {{ $item['name'] }}
                                    </h4>
                                    <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                                        {{ $item['type'] == 'package' ? 'Subscription' : 'Product' }}
                                        <span class="mx-1">•</span>
                                        Qty: {{ $item['quantity'] }}
                                    </p>
                                </div>
                                <div class="item-actions text-right flex items-center gap-3 sm:gap-4">
                                    <p class="font-bold text-primary-500 text-sm sm:text-base">
                                        ${{ number_format($itemTotal, 2) }}
                                    </p>
                                    <form action="{{ route('cart.remove', $key) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs sm:text-sm transition font-medium">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-3 sm:mt-4 flex flex-wrap gap-2 sm:gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Continue Shopping
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Clear your cart?')">
                            Clear Cart
                        </button>
                    </form>
                </div>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1 animate-fadeInRight">
                <div class="card sticky-top">
                    <h3 class="font-bold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Order Summary</h3>
                    
                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">Subtotal</span>
                            <span class="font-medium">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">Tax (18%)</span>
                            <span class="font-medium">${{ number_format($total * 0.18, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--text-secondary)]">Shipping</span>
                            <span class="font-medium">{{ $total > 100 ? 'Free' : '$10.00' }}</span>
                        </div>
                        <div class="border-t border-[var(--border-color)] pt-3 mt-3">
                            <div class="flex justify-between text-base sm:text-lg font-bold">
                                <span>Total</span>
                                <span class="text-primary-500">
                                    ${{ number_format($total + ($total * 0.18) + ($total > 100 ? 0 : 10), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('cart.checkout') }}" method="POST" class="mt-3 sm:mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full text-sm sm:text-base py-2 sm:py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Proceed to Checkout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
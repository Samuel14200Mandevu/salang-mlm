@if($products->count() > 0)
    <div class="product-grid shop-product-grid">
        @foreach($products as $product)
            @include('products.partials.shop-product-card', ['product' => $product])
        @endforeach
    </div>

    <div class="member-shop-pagination mt-4">
        <x-salang-pagination :paginator="$products" id="paginationContainer" />
    </div>
@else
    <div class="card text-center py-8 sm:py-12 p-4 sm:p-6 shop-catalog-empty">
        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
        </svg>
        <h3 class="text-lg font-semibold text-[var(--text-primary)]">Aucun produit</h3>
        <p class="text-sm text-[var(--text-secondary)] mt-2">Modifiez votre recherche ou revenez plus tard.</p>
        @if(request('category') || request('search'))
            <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm mt-4">Tout le catalogue</a>
        @endif
    </div>
@endif

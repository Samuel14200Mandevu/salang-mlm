@extends('admin.layouts.app')

@push('styles')
<style>
    .product-row:hover { background: var(--bg-hover); }
    .stock-badge { transition: all 0.3s ease; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🛍️ Produits</h1>
            <p class="text-[var(--text-secondary)] mt-1">Gérez le catalogue de produits</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 animate-fadeIn">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Recherche -->
    <div class="relative animate-fadeInUp delay-1 max-w-sm">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </span>
        <input type="text" 
               id="searchInput"
               placeholder="Rechercher un produit..."
               class="input pl-9">
    </div>

    <div class="card animate-fadeInUp delay-2">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th class="hidden md:table-cell">Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    @forelse($products as $product)
                        <tr class="product-row" data-name="{{ strtolower($product->name) }}" data-category="{{ strtolower($product->category ?? '') }}">
                            <td class="font-mono text-sm">#{{ $product->id }}</td>
                            <td>
                                <div class="w-10 h-10 rounded-lg bg-[var(--bg-secondary)] overflow-hidden flex items-center justify-center">
                                    @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                                        <img src="{{ asset('storage/products/' . $product->image) }}" 
                                             alt="{{ $product->name }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <span class="text-lg">📦</span>
                                    @endif
                                </div>
                            </td>
                            <td class="font-medium">{{ $product->name }}</td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)]">{{ $product->category ?? '-' }}</td>
                            <td class="font-bold text-primary-500">${{ number_format($product->price, 2) }}</td>
                            <td>
                                <span class="badge stock-badge {{ $product->stock > 10 ? 'badge-success' : ($product->stock > 0 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $product->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                    @if($product->is_featured)
                                        <span class="badge badge-warning">⭐ Vedette</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.products.toggle-status', $product->id) }}" 
                                       class="btn btn-outline btn-sm btn-icon" 
                                       title="{{ $product->is_active ? 'Désactiver' : 'Activer' }}">
                                        @if($product->is_active)
                                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement ce produit ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm btn-icon text-red-500 hover:text-red-700" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                Aucun produit
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="mt-4" id="paginationContainer">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('.product-row');
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        rows.forEach(row => {
            const name = row.dataset.name || '';
            const category = row.dataset.category || '';
            const match = name.includes(query) || category.includes(query);
            row.style.display = match ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection
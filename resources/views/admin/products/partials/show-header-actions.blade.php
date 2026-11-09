<a href="{{ route('admin.products') }}" class="btn btn-outline btn-sm sm:btn-md">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    <span class="hidden xs:inline">Retour</span>
</a>

<a href="{{ route('admin.products.stock', $product->id) }}" class="btn btn-outline btn-sm sm:btn-md" title="Gérer le stock">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <span class="hidden sm:inline">Stock</span>
</a>

<a href="{{ route('admin.products.generate-barcode', $product->id) }}" class="btn btn-info btn-sm sm:btn-md">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h2m4 0h10M4 12h16M4 18h2m4 0h10"/>
    </svg>
    <span class="hidden sm:inline">{{ !empty($hasBarcode) ? 'Régénérer code-barres' : 'Générer code-barres' }}</span>
    <span class="sm:hidden">CB</span>
</a>

<a href="{{ route('admin.products.barcode', $product->id) }}" class="btn btn-outline btn-sm sm:btn-md">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
    <span class="hidden sm:inline">Voir code-barres</span>
</a>

@if($product->is_active)
    <a href="{{ route('admin.products.toggle-status', $product->id) }}" class="btn btn-warning btn-sm sm:btn-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
        <span class="hidden sm:inline">Désactiver</span>
    </a>
@else
    <a href="{{ route('admin.products.toggle-status', $product->id) }}" class="btn btn-success btn-sm sm:btn-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="hidden sm:inline">Activer</span>
    </a>
@endif

<a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary btn-sm sm:btn-md">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    <span class="hidden xs:inline">Modifier</span>
</a>

<button type="button" onclick="openDeleteModal()" class="btn btn-danger btn-sm sm:btn-md">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
    </svg>
    <span class="hidden xs:inline">Supprimer</span>
</button>

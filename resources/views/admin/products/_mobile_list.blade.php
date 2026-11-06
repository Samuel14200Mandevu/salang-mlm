{{-- Liste mobile (cartes) — sync AJAX via #productsMobileList --}}
<div id="productsMobileList" class="md:hidden space-y-3">
    @forelse($products as $product)
        <x-ui.list-card
            :title="$product->name"
            :subtitle="($product->category ?? 'Sans catégorie') . ' · PV ' . ($product->pv_value ?? 0)"
            :amount="number_format($product->price, 2) . ' $'"
        >
            <x-slot:status>
                @php
                    [$stockClass, $stockLabel] = match (true) {
                        $product->stock > 10 => ['success', $product->stock . ' en stock'],
                        $product->stock > 0 => ['warning', $product->stock . ' restants'],
                        default => ['danger', 'Rupture'],
                    };
                @endphp
                <span @class([
                    'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold',
                    'text-[var(--success)] bg-[var(--success-light)]' => $stockClass === 'success',
                    'text-[var(--danger)] bg-[var(--danger-light)]' => $stockClass === 'danger',
                    'text-[var(--warning)] bg-[var(--warning-light)]' => $stockClass === 'warning',
                ])>{{ $stockLabel }}</span>
            </x-slot:status>

            <x-slot:actions>
                <a href="{{ route('admin.products.edit', $product->id) }}"
                   class="btn btn-outline btn-sm btn-icon"
                   title="Modifier">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <a href="{{ route('admin.products.toggle-status', $product->id) }}"
                   class="btn btn-outline btn-sm btn-icon"
                   title="{{ $product->is_active ? 'Désactiver' : 'Activer' }}">
                    @if($product->is_active)
                        <svg class="w-4 h-4 text-[var(--warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-[var(--success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </a>
                <button type="button"
                        onclick="openDeleteModal({{ Js::from((string) $product->id) }}, {{ Js::from($product->name) }})"
                        class="btn btn-outline btn-sm btn-icon btn-danger-icon"
                        title="Supprimer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </x-slot:actions>
        </x-ui.list-card>
    @empty
        <x-ui.empty-state
            title="Aucun produit"
            description="Créez votre premier produit pour commencer."
        >
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </x-slot:icon>
            <x-slot:action>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Créer un produit</a>
            </x-slot:action>
        </x-ui.empty-state>
    @endforelse
</div>

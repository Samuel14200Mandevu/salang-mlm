{{-- Liste mobile (cartes) — pas d'AJAX à sync sur packages --}}
<div id="packagesMobileList" class="md:hidden space-y-3">
    @forelse($packages as $package)
        <x-ui.list-card
            :title="$package->name"
            :subtitle="'PV ' . number_format($package->pv_value) . ' · ' . $package->commission_rate . '% commission'"
            :amount="number_format($package->price, 2) . ' $'"
        >
            <x-slot:actions>
                <a href="{{ route('admin.packages.edit', $package) }}"
                   class="btn btn-outline btn-sm btn-icon"
                   title="Modifier">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <button type="button"
                        onclick="openDeleteModal({{ Js::from((string) $package->id) }}, {{ Js::from($package->name) }})"
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
            title="Aucun package"
            description="Créez votre premier package pour commencer."
        >
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                </svg>
            </x-slot:icon>
            <x-slot:action>
                <a href="{{ route('admin.packages.create') }}" class="btn btn-primary btn-sm">Créer un package</a>
            </x-slot:action>
        </x-ui.empty-state>
    @endforelse
</div>

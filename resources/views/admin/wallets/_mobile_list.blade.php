{{-- Liste mobile (cartes) — sync AJAX via #walletsMobileList --}}
<div id="walletsMobileList" class="md:hidden space-y-3">
    @forelse($wallets as $wallet)
        <x-ui.list-card
            :title="$wallet->user?->name ?? ('Utilisateur #' . $wallet->user_id)"
            :subtitle="($wallet->user?->email ?? '') . ' · ' . ($wallet->currency ?? 'USD')"
            :amount="number_format($wallet->balance, 2) . ' $'"
            :href="route('admin.wallets.show', $wallet->id)"
        >
            <x-slot:status>
                @if(! $wallet->is_active)
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold text-[var(--danger)] bg-[var(--danger-light)]">Bloqué</span>
                @else
                    {{-- --bg-subtle inexistant : fallback --bg-secondary --}}
                    <span class="inline-flex items-center rounded-md border border-[var(--border-color)] px-2 py-0.5 text-xs font-semibold text-[var(--text-secondary)] bg-[var(--bg-secondary)]">{{ $wallet->currency ?? 'USD' }}</span>
                @endif
            </x-slot:status>
            <x-slot:actions>
                <a href="{{ route('admin.wallets.show', $wallet->id) }}"
                   class="btn btn-outline btn-sm btn-icon"
                   title="Voir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
            </x-slot:actions>
        </x-ui.list-card>
    @empty
        <x-ui.empty-state
            title="Aucun portefeuille"
            description="{{ (request('search') || request('status')) ? 'Aucun résultat pour ces filtres.' : 'Les portefeuilles apparaîtront lors de l\'inscription des utilisateurs.' }}"
        >
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </x-slot:icon>
        </x-ui.empty-state>
    @endforelse
</div>

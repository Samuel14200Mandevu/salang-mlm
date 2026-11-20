{{-- Liste mobile (cartes) — sync AJAX via #walletsMobileList --}}
<div id="walletsMobileList" class="admin-wallet-list md:hidden">
    @forelse($wallets as $wallet)
        @php
            $owner = $wallet->user;
            $ownerName = $owner?->name ?? ('Utilisateur #' . $wallet->user_id);
            $ownerEmail = $owner?->email ?? '';
            $sponsorCode = $owner?->sponsor_id;
            $pending = (float) ($wallet->pending_balance ?? 0);
            $isActive = (bool) $wallet->is_active;
        @endphp
        <a href="{{ route('admin.wallets.show', $wallet->id) }}" class="admin-wallet-card">
            <div class="admin-wallet-card__main">
                <div class="admin-wallet-card__avatar" aria-hidden="true">
                    {{ strtoupper(substr($ownerName, 0, 1)) }}
                </div>
                <div class="admin-wallet-card__text">
                    <span class="admin-wallet-card__name">{{ $ownerName }}</span>
                    @if($ownerEmail !== '')
                        <span class="admin-wallet-card__email" title="{{ $ownerEmail }}">{{ $ownerEmail }}</span>
                    @endif
                </div>
                <div class="admin-wallet-card__balance-wrap">
                    <span class="admin-wallet-card__balance">${{ number_format((float) $wallet->balance, 2) }}</span>
                    <svg class="admin-wallet-card__chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
            <div class="admin-wallet-card__meta">
                @if($sponsorCode)
                    <span class="admin-wallet-card__code">{{ $sponsorCode }}</span>
                @endif
                <span class="admin-wallet-card__chip">{{ $wallet->currency ?? 'USD' }}</span>
                @if($pending > 0)
                    <span class="admin-wallet-card__pending">En attente ${{ number_format($pending, 2) }}</span>
                @endif
                <span class="admin-wallet-card__status badge {{ $isActive ? 'badge-success' : 'badge-warning' }}">
                    {{ $isActive ? 'Actif' : 'Bloqué' }}
                </span>
            </div>
        </a>
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

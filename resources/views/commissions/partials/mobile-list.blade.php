<ul class="commissions-mobile-feed__list" id="commissionsMobileList">
    @forelse($commissions ?? [] as $commission)
        @php
            $typeLetter = strtoupper(substr($commission->type ?? 'C', 0, 1));
        @endphp
        <li>
            <button type="button"
                    class="commissions-mobile-row member-fintech-tx commission-row"
                    data-type="{{ $commission->type }}"
                    data-status="{{ $commission->status }}"
                    data-date="{{ $commission->created_at->format('Y-m-d') }}"
                    onclick="openCommissionDetails({{ $commission->id }})">
                <span class="member-fintech-tx__icon commissions-mobile-row__icon">{{ $typeLetter }}</span>
                <div class="member-fintech-tx__body">
                    <p class="member-fintech-tx__title">{{ ucfirst($commission->type) }}</p>
                    <p class="member-fintech-tx__meta">
                        {{ $commission->created_at->format('d/m/Y H:i') }}
                        · {{ Str::limit($commission->description ?? 'Commission MLM', 28) }}
                    </p>
                </div>
                <div class="commissions-mobile-row__aside">
                    <span class="member-fintech-tx__amount is-credit">+${{ number_format($commission->amount, 2) }}</span>
                    <span class="badge {{ $commission->status == 'paid' ? 'badge-success' : 'badge-warning' }} text-[10px]">
                        {{ $commission->status == 'paid' ? 'Payé' : 'En attente' }}
                    </span>
                </div>
            </button>
        </li>
    @empty
        <li class="commissions-mobile-feed__empty">
            <svg class="w-12 h-12 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="font-semibold text-[var(--text-primary)]">Aucune commission</p>
            <p class="text-sm text-[var(--text-secondary)] mt-1">Développez votre réseau pour générer des gains</p>
            <a href="{{ route('network.index') }}" class="btn btn-primary btn-sm mt-3 inline-flex w-auto">Mon réseau</a>
        </li>
    @endforelse
</ul>

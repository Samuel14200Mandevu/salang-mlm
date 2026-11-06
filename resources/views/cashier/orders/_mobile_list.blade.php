{{-- Liste mobile (cartes) — recherche = reload page, pas de sync AJAX --}}
<div id="cashierOrderMobileList" class="md:hidden space-y-3">
    @forelse($orders ?? [] as $order)
        @php
            $statusMap = [
                'completed' => ['label' => 'Terminée', 'token' => 'success'],
                'pending' => ['label' => 'En attente', 'token' => 'warning'],
                'cancelled' => ['label' => 'Annulée', 'token' => 'danger'],
                'processing' => ['label' => 'En traitement', 'token' => 'primary'],
            ];
            $status = $statusMap[$order->status] ?? ['label' => ucfirst((string) $order->status), 'token' => 'primary'];
            $source = $order->source === 'pos'
                ? 'POS'
                : (($order->source === 'web' || $order->source === 'online') ? 'En ligne' : 'MLM');
            $showUrl = route('cashier.orders.show', $order->id);
        @endphp
        {{-- div + onclick : évite <a> imbriqué avec l’action Voir --}}
        <x-ui.list-card
            :title="'#' . $order->order_number"
            :subtitle="($order->user?->name ?? 'Client inconnu') . ' · ' . $order->created_at->format('d/m/Y')"
            :amount="number_format((float) $order->total, 2) . ' $'"
            class="cursor-pointer"
            role="link"
            tabindex="0"
            onclick="window.location='{{ $showUrl }}'"
            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location='{{ $showUrl }}';}"
        >
            <x-slot:status>
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold text-[var(--primary)] bg-[var(--primary-light)]">
                    {{ $source }}
                </span>
                <span @class([
                    'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ml-1',
                    'text-[var(--success)] bg-[var(--success-light)]' => $status['token'] === 'success',
                    'text-[var(--warning)] bg-[var(--warning-light)]' => $status['token'] === 'warning',
                    'text-[var(--danger)] bg-[var(--danger-light)]' => $status['token'] === 'danger',
                    'text-[var(--primary)] bg-[var(--primary-light)]' => $status['token'] === 'primary',
                ])>{{ $status['label'] }}</span>
            </x-slot:status>
            <x-slot:actions>
                <a href="{{ $showUrl }}"
                   class="btn btn-primary btn-sm"
                   title="Voir"
                   aria-label="Voir"
                   onclick="event.stopPropagation()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
            </x-slot:actions>
        </x-ui.list-card>
    @empty
        <x-ui.empty-state
            title="Aucune commande"
            :description="request()->filled('search') ? 'Aucun résultat pour ces filtres.' : 'Les commandes apparaîtront ici.'"
        >
            <x-slot:icon>🧾</x-slot:icon>
        </x-ui.empty-state>
    @endforelse
</div>

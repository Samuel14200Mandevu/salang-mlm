{{-- Liste mobile (cartes) — clic = détail commande --}}
<div id="cashierOrderMobileList" class="md:hidden space-y-3">
    @forelse($orders ?? [] as $order)
        @php
            $client = $order->user;
            $member = null;
            $metaSponsorId = data_get($order->metadata, 'sponsor_id');
            if ($metaSponsorId && isset($sponsorsById) && $sponsorsById->has($metaSponsorId)) {
                $member = $sponsorsById->get($metaSponsorId);
            } elseif ($client?->parrain) {
                $member = $client->parrain;
            } elseif ($client && $client->user_type === 'member') {
                $member = $client;
            }

            $memberLabel = $member
                ? (trim(($member->sponsor_id ? $member->sponsor_id.' : ' : '').$member->name))
                : null;

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
            $subtitle = ($client?->name ?? 'Client inconnu')
                .($memberLabel ? ' · '.$memberLabel : '')
                .' · '.$order->created_at->format('d/m/Y');
        @endphp
        <x-ui.list-card
            :title="'#' . $order->order_number"
            :subtitle="$subtitle"
            :amount="'$' . number_format((float) $order->total, 2)"
            :href="$showUrl"
            class="cursor-pointer"
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

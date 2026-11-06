{{-- Liste mobile (cartes) — sync AJAX via #withdrawalsMobileList --}}
{{-- processing : --info/--info-light absents → --primary/--primary-light --}}
<div id="withdrawalsMobileList" class="md:hidden space-y-3">
    @forelse($withdrawals as $withdrawal)
        @php
            $statusMap = [
                'pending' => ['label' => 'En attente', 'token' => 'warning'],
                'processing' => ['label' => 'En traitement', 'token' => 'primary'],
                'completed' => ['label' => 'Terminé', 'token' => 'success'],
                'failed' => ['label' => 'Échoué', 'token' => 'danger'],
            ];
            $status = $statusMap[$withdrawal->status] ?? ['label' => ucfirst((string) $withdrawal->status), 'token' => 'neutral'];
        @endphp
        <x-ui.list-card
            :title="$withdrawal->user?->name ?? ('Utilisateur #' . $withdrawal->user_id)"
            :subtitle="($withdrawal->user?->email ?? '') . ' · ' . ucfirst((string) ($withdrawal->method ?? 'N/A')) . ' · ' . $withdrawal->created_at->format('d/m/Y H:i')"
            :amount="number_format($withdrawal->amount, 2) . ' $'"
        >
            <x-slot:status>
                <span @class([
                    'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold',
                    'text-[var(--warning)] bg-[var(--warning-light)]' => $status['token'] === 'warning',
                    'text-[var(--primary)] bg-[var(--primary-light)]' => $status['token'] === 'primary',
                    'text-[var(--success)] bg-[var(--success-light)]' => $status['token'] === 'success',
                    'text-[var(--danger)] bg-[var(--danger-light)]' => $status['token'] === 'danger',
                    'text-[var(--text-secondary)] bg-[var(--bg-secondary)]' => $status['token'] === 'neutral',
                ])>{{ $status['label'] }}</span>
            </x-slot:status>
            <x-slot:actions>
                <a href="{{ route('admin.withdrawals.show', $withdrawal->id) }}"
                   class="btn btn-outline btn-sm btn-icon"
                   title="Voir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
                @if(in_array($withdrawal->status, ['pending', 'processing'], true))
                    <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="btn btn-success btn-sm btn-icon"
                                onclick="return confirm('Approuver ce retrait ?')"
                                title="Approuver">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </form>
                    <button type="button"
                            onclick="openRejectModal({{ Js::from((string) $withdrawal->id) }})"
                            class="btn btn-danger btn-sm btn-icon"
                            title="Rejeter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </x-slot:actions>
        </x-ui.list-card>
    @empty
        <x-ui.empty-state
            title="Aucun retrait"
            description="{{ (request('search') || request('status') || request('method')) ? 'Aucun résultat pour ces filtres.' : 'Les retraits apparaîtront ici lorsqu\'ils seront demandés.' }}"
        >
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-ui.empty-state>
    @endforelse
</div>

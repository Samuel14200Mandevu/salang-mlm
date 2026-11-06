{{-- Liste mobile (cartes) — pas d'AJAX à sync sur ranks --}}
<div id="ranksMobileList" class="md:hidden space-y-3">
    @forelse($ranks as $rank)
        @php
            $userCount = $stats['users_by_rank'][$rank->id] ?? 0;
        @endphp
        <x-ui.list-card
            :title="$rank->name"
            :subtitle="'PV min ' . number_format($rank->min_pv) . ' · BV min ' . number_format($rank->min_bv ?? 0) . ' · ' . $rank->bonus_percentage . '% bonus' . ($userCount > 0 ? ' · ' . $userCount . ' utilisateur(s)' : '')"
            :amount="$rank->bonus_percentage . '%'"
        >
            <x-slot:status>
                {{-- Pill bleu = rank-badge desktop --}}
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-[var(--primary)] bg-[var(--primary-light)]">
                    {{ $rank->name }}
                </span>
            </x-slot:status>
            <x-slot:actions>
                <a href="{{ route('admin.ranks.edit', $rank->id) }}"
                   class="btn btn-outline btn-sm btn-icon"
                   title="Modifier">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <a href="{{ route('admin.ranks.toggle-status', $rank->id) }}"
                   class="btn btn-outline btn-sm btn-icon"
                   title="{{ $rank->is_active ? 'Désactiver' : 'Activer' }}">
                    @if($rank->is_active)
                        <svg class="w-4 h-4 text-[var(--ui-stat-warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-[var(--ui-stat-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </a>
                <button type="button"
                        onclick="openDeleteModal({{ Js::from((string) $rank->id) }}, {{ Js::from($rank->name) }}, {{ (int) $userCount }})"
                        class="btn btn-outline btn-sm btn-icon text-[var(--ui-stat-danger)]"
                        title="Supprimer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </x-slot:actions>
        </x-ui.list-card>
    @empty
        <x-ui.empty-state
            title="Aucun rang configuré"
            description="Créez votre premier rang pour commencer."
        >
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </x-slot:icon>
            <x-slot:action>
                <a href="{{ route('admin.ranks.create') }}" class="btn btn-primary btn-sm">Créer un rang</a>
            </x-slot:action>
        </x-ui.empty-state>
    @endforelse
</div>

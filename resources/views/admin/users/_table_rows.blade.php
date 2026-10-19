{{-- resources/views/admin/users/_table_rows.blade.php --}}
@forelse($users as $user)
    @php
        $rankLevel = (int) ($user->rank_level ?? 0);
    @endphp
    <tr class="user-row"
        data-name="{{ strtolower($user->name) }}"
        data-email="{{ strtolower($user->email) }}"
        data-sponsor="{{ strtolower($user->sponsor_id ?? '') }}"
        data-phone="{{ strtolower($user->phone ?? '') }}"
        onclick="window.location='{{ route('admin.users.show', $user) }}'">
        <td class="font-mono text-xs sm:text-sm">{{ $user->id }}</td>
        <td>
            <div class="flex items-center gap-2">
                <div class="avatar-sm hidden sm:flex">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-medium text-sm sm:text-base">{{ $user->name }}</div>
                    <div class="text-xs text-[var(--text-secondary)]">{{ $user->email }}</div>
                    @if($user->phone && $user->phone !== 'N/A')
                        <div class="text-xs text-[var(--text-tertiary)]">{{ $user->phone }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td class="font-mono text-xs sm:text-sm">
            @if($user->sponsor_id)
                {{ $user->sponsor_id }}
            @else
                <span class="text-[var(--text-tertiary)]">—</span>
            @endif
        </td>
        <td>
            <span class="{{ \App\Support\MlmRank::badgeClass($rankLevel) }}">
                {{ \App\Support\MlmRank::label($rankLevel) }}
            </span>
        </td>
        <td class="font-mono text-xs sm:text-sm">
            @if($codeParrain = $user->parrainSponsorCode())
                {{ $codeParrain }}
            @else
                <span class="text-[var(--text-tertiary)]">—</span>
            @endif
        </td>
        <td class="font-mono text-sm tabular-nums">
            {{ number_format((float) ($user->pv_balance ?? 0), 0, ',', ' ') }}
        </td>
        <td>
            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                {{ $user->is_active ? 'Actif' : 'Inactif' }}
            </span>
        </td>
        <td class="text-center">
            <div class="flex justify-center">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="btn btn-warning btn-sm"
                   title="Modifier"
                   onclick="event.stopPropagation();">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr id="noResultsRow">
        <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-base sm:text-lg font-medium">Aucun utilisateur trouvé</p>
            <p class="text-sm text-[var(--text-tertiary)]">
                @if(request('search'))
                    Aucun résultat ne correspond à votre recherche "{{ request('search') }}"
                @else
                    Commencez par ajouter votre premier utilisateur
                @endif
            </p>
        </td>
    </tr>
@endforelse

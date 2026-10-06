@forelse($members as $member)
    <tr class="member-row">
        <td>
            <div class="flex items-center gap-2">
                <div class="avatar-sm">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-medium text-sm">{{ $member->name }}</div>
                    <div class="text-xs text-[var(--text-secondary)]">{{ $member->email }}</div>
                    @if($member->phone)
                        <div class="text-xs text-[var(--text-secondary)]">{{ $member->phone }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td class="hidden sm:table-cell font-mono text-xs text-[var(--cashier-stat-primary)]">
            {{ $member->sponsor_id ?? 'N/A' }}
        </td>
        <td>
            @php
                $roleName = $member->getRoleNames()->first() ?? 'user';
                $badgeClass = 'badge-neutral';
                if ($roleName === 'admin') {
                    $badgeClass = 'badge-admin';
                } elseif ($roleName === 'cashier') {
                    $badgeClass = 'badge-cashier';
                } elseif ($roleName === 'caissier_principal') {
                    $badgeClass = 'badge-cashier-principal';
                } elseif ($roleName === 'user') {
                    $badgeClass = 'badge-user';
                }
            @endphp
            <span class="badge {{ $badgeClass }}">
                {{ $roleName == 'user' ? 'Utilisateur' : ucfirst(str_replace('_', ' ', $roleName)) }}
            </span>
        </td>
        <td>
            @if($member->is_active)
                <span class="badge badge-success">Actif</span>
            @else
                <span class="badge badge-danger">Inactif</span>
            @endif
        </td>
        <td class="hidden md:table-cell">
            @php
                $totalCommissions = \App\Models\Commission::where('user_id', $member->id)
                    ->where('source', 'pos')
                    ->where('status', 'paid')
                    ->sum('amount');
            @endphp
            @if($totalCommissions > 0)
                <span class="badge badge-commission">${{ number_format($totalCommissions, 2) }}</span>
            @else
                <span class="text-xs text-[var(--text-tertiary)]">Aucune</span>
            @endif
        </td>
        <td class="text-right">
            <div class="flex items-center justify-end gap-1">
                <a href="{{ route('cashier.members.show', $member->id) }}"
                   class="btn btn-primary btn-xs btn-icon" title="Voir">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
                <a href="{{ route('cashier.members.commissions', $member->id) }}"
                   class="btn btn-success btn-xs btn-icon" title="Commissions POS">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08.-402.2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
                <a href="{{ route('cashier.members.orders', $member->id) }}"
                   class="btn btn-warning btn-xs btn-icon" title="Commandes">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-base font-medium text-[var(--text-primary)]">Aucun membre</p>
            <p class="text-sm text-[var(--text-tertiary)]">Aucun membre trouvé</p>
        </td>
    </tr>
@endforelse

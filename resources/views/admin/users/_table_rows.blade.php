{{-- resources/views/admin/users/_table_rows.blade.php --}}
@forelse($users as $user)
    @php
        $roleName = $user->getRoleNames()->first() ?? 'user';
        
        $roleDisplay = 'Utilisateur';
        $badgeClass = 'badge-user';
        
        if($roleName === 'admin') {
            $roleDisplay = 'Administrateur';
            $badgeClass = 'badge-admin';
        } elseif($roleName === 'cashier') {
            $roleDisplay = 'Caissier';
            $badgeClass = 'badge-cashier';
        } elseif($roleName === 'caissier_principal') {
            $roleDisplay = 'Caissier Principal';
            $badgeClass = 'badge-cashier-principal';
        }
        
        if($user->hasRole('caissier_principal') && $roleName !== 'caissier_principal') {
            $roleDisplay = 'Caissier Principal';
            $badgeClass = 'badge-cashier-principal';
        } elseif($user->hasRole('cashier') && $roleName !== 'cashier' && $roleName !== 'caissier_principal') {
            $roleDisplay = 'Caissier';
            $badgeClass = 'badge-cashier';
        } elseif($user->hasRole('admin') && $roleName !== 'admin') {
            $roleDisplay = 'Administrateur';
            $badgeClass = 'badge-admin';
        }
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
        <td class="hidden sm:table-cell">
            @if($user->sponsor_id)
                <span class="badge badge-sponsor text-[10px] sm:text-xs">
                    {{ $user->sponsor_id }}
                </span>
            @else
                <span class="text-xs text-[var(--text-tertiary)]">-</span>
            @endif
        </td>
        <td class="hidden md:table-cell">
            <span class="badge {{ $badgeClass }} text-[10px] sm:text-xs">
                {{ $roleDisplay }}
            </span>
        </td>
        <td class="hidden lg:table-cell">
            @if($user->package)
                <span class="text-sm">{{ $user->package->name }}</span>
            @else
                <span class="text-xs text-[var(--text-tertiary)]">-</span>
            @endif
        </td>
        <td class="hidden xl:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
            {{ $user->created_at->format('d/m/Y') }}
        </td>
        <td>
            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                {{ $user->is_active ? 'Actif' : 'Inactif' }}
            </span>
        </td>
        <td class="text-right">
            <div class="flex items-center justify-end gap-1">
                <a href="{{ route('admin.users.show', $user) }}"
                   class="btn btn-primary btn-sm"
                   title="Voir le profil">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="hidden sm:inline">Voir</span>
                </a>
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="btn btn-warning btn-sm"
                   title="Modifier">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span class="hidden sm:inline">Modifier</span>
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
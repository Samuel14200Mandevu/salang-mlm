<div class="grid grid-cols-1 lg:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-3">
    <div class="card">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="avatar avatar-lg sm:avatar-xl avatar-gradient avatar-ring">
                @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Avatar">
                @else
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-[var(--text-primary)] truncate">{{ $user->name }}</h3>
                <p class="text-xs text-[var(--text-secondary)]">Membre</p>
                <span class="badge badge-success text-[10px] sm:text-xs inline-block mt-0.5">{{ $currentRankName ?? 'Distributeur' }}</span>
            </div>
        </div>

        <div class="mt-3 sm:mt-4 grid grid-cols-2 gap-2 text-xs sm:text-sm">
            <div>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">ID</p>
                <p class="font-semibold text-[var(--text-primary)]">#{{ $user->id }}</p>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Inscrit</p>
                <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">{{ $user->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Parrain</p>
            @if($sponsor)
                <div class="flex items-center gap-3 mt-1">
                    <div class="avatar avatar-md avatar-gradient">{{ strtoupper(substr($sponsor->name, 0, 1)) }}</div>
                    <div>
                        <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">{{ $sponsor->name }}</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">{{ $sponsor->email }}</p>
                    </div>
                </div>
            @else
                <p class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">Aucun parrain</p>
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Vous etes le premier de votre reseau</p>
            @endif
        </div>

        <div class="mt-2 grid grid-cols-2 gap-2">
            <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Filleuls</p>
                <p class="font-bold text-primary-500 text-sm">{{ $totalDownlines ?? 0 }}</p>
            </div>
            <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Mon Code</p>
                <p class="font-bold text-primary-500 text-xs font-mono truncate">{{ $user->sponsor_id }}</p>
            </div>
        </div>

        <div class="mt-2 grid grid-cols-2 gap-2">
            <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">PV Personnel</p>
                <p class="font-bold text-primary-500 text-sm">{{ number_format($pvPersonnel ?? 0) }}</p>
            </div>
            <div class="p-2 bg-[var(--bg-secondary)] rounded-lg text-center">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">PV Cumule</p>
                <p class="font-bold text-purple-500 text-sm">{{ number_format($pvCumul ?? 0) }}</p>
            </div>
        </div>
    </div>

    <div class="lg:col-span-3 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <div class="card-stats">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Personnel</p>
                    <p class="text-lg sm:text-2xl font-bold text-primary-500 truncate">{{ number_format($pvPersonnel ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="card-stats">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Cumule</p>
                <p class="text-lg sm:text-2xl font-bold text-purple-500 truncate">{{ number_format($pvCumul ?? 0) }}</p>
            </div>
        </div>
        <div class="card-stats">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">PV Mensuel</p>
                <p class="text-lg sm:text-2xl font-bold text-green-500 truncate">{{ number_format($rankProgress['monthly_pv'] ?? 0) }}</p>
            </div>
        </div>
        <div class="card-stats">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider truncate">Total paye visible</p>
                <p class="text-lg sm:text-2xl font-bold text-blue-500 truncate">${{ number_format($totalCommission ?? 0, 2) }}</p>
            </div>
        </div>
    </div>
</div>

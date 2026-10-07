<div class="flex items-center gap-3">
    <div class="avatar avatar-md avatar-gradient avatar-ring flex-shrink-0">
        @if($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar)))
            <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="">
        @else
            {{ strtoupper(substr($user->name, 0, 2)) }}
        @endif
    </div>
    <div class="min-w-0 flex-1">
        <p class="font-semibold text-sm text-[var(--text-primary)] truncate">{{ $user->name }}</p>
        <p class="text-[10px] text-[var(--text-tertiary)] font-mono">#{{ $user->sponsor_id ?? $user->id }}</p>
    </div>
    <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm shrink-0">Réseau</a>
</div>
@if($sponsor)
    <div class="mt-3 pt-3 border-t border-[var(--border-light)] flex items-center gap-2 text-xs">
        <span class="text-[var(--text-tertiary)]">Parrain</span>
        <span class="font-medium text-[var(--text-primary)] truncate">{{ $sponsor->name }}</span>
    </div>
@endif

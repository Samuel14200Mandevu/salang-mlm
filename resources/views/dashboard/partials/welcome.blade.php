<div class="dashboard-welcome-card animate-fadeInUp delay-1">
    <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
        <span class="dashboard-level-badge">Niveau {{ $dashboardLevelNumber }}</span>
    </div>
    <h3 class="font-bold text-[var(--text-primary)] text-base sm:text-lg">{{ $dashboardLevel['welcome_title'] }}</h3>
    <p class="text-sm text-[var(--text-secondary)] mt-1">{{ $dashboardLevel['welcome_message'] }}</p>
    <div class="mt-3 flex flex-wrap gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">Commencer a acheter</a>
        <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm">Inviter des membres</a>
    </div>
</div>

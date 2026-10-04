@if(!$user->is_active)
<div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 animate-fadeInUp delay-1">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-semibold text-yellow-700 dark:text-yellow-300">Compte inactif</span>
        </div>
        <p class="text-sm text-yellow-600 dark:text-yellow-400 flex-1">Activez votre compte pour recevoir des commissions</p>
        <a href="{{ route('activate.index') }}" class="btn btn-primary btn-sm flex-shrink-0">Activer maintenant</a>
    </div>
</div>
@endif

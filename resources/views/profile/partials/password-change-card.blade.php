<div class="card profile-password-card animate-fadeInUp">
    <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
        <div class="stat-icon stat-icon-warning">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-base sm:text-lg font-semibold text-[var(--text-primary)]">Changer le mot de passe</h3>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Sécurisez votre compte</p>
        </div>
    </div>

    <form action="{{ route('profile.update-password') }}" method="POST">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
            <div>
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Mot de passe actuel</label>
                <input type="password" name="current_password" value="{{ old('current_password') }}" class="input text-sm sm:text-base" placeholder="Entrez votre mot de passe actuel" required autocomplete="current-password">
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nouveau mot de passe</label>
                <input type="password" name="password" class="input text-sm sm:text-base" placeholder="Entrez un nouveau mot de passe" required autocomplete="new-password">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" class="input text-sm sm:text-base" placeholder="Confirmez le nouveau mot de passe" required autocomplete="new-password">
            </div>
        </div>

        <div class="mt-3 sm:mt-4 flex justify-end">
            <button type="submit" class="btn btn-warning w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mettre à jour
            </button>
        </div>
    </form>
</div>

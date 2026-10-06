<footer class="public-footer">
    <div class="public-footer-grid">
        <div>
            <div class="flex items-center gap-3">
                <x-ui.image src="images/salang_logo.png" alt="" class="h-10 w-auto" width="120" height="40" :lazy="true" />
                <div>
                    <p class="text-sm font-semibold text-[var(--text-primary)]">Salang Group</p>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">Complementary &amp; Alternative Medicine</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-[var(--text-secondary)] max-w-md leading-relaxed">
                Votre santé, notre priorité. Informations générales sur l’entreprise et le plan de rémunération&nbsp;; l’adhésion et les achats se font via votre espace membre.
            </p>
        </div>

        <div class="sm:text-right">
            <p class="text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-3">Informations légales</p>
            <nav class="public-footer-legal sm:justify-end" aria-label="Liens légaux">
                @if (Route::has('legal.mentions'))
                    <a href="{{ route('legal.mentions') }}">Mentions légales</a>
                @endif
                <a href="{{ route('legal.terms') }}">Conditions générales</a>
                <a href="{{ route('legal.privacy') }}">Politique de confidentialité</a>
            </nav>
            <p class="mt-6 text-xs text-[var(--text-muted)]">
                &copy; {{ date('Y') }} Salang Group. Tous droits réservés.
            </p>
        </div>
    </div>
</footer>

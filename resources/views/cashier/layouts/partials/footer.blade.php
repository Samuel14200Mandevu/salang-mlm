            <!-- Footer fixe en bas avec CGU et Confidentialité -->
            <footer class="main-footer" role="contentinfo">
                <div class="max-w-7xl mx-auto">
                    <div class="footer-links flex flex-wrap justify-center items-center gap-1 text-xs sm:text-sm text-[var(--text-secondary)]">
                        <span>&copy; {{ date('Y') }} Salang Group. Tous droits réservés.</span>
                        <span class="footer-separator text-[var(--text-tertiary)]">•</span>
                        <a href="{{ route('legal.terms') }}">CGU</a>
                        <span class="footer-separator text-[var(--text-tertiary)]">•</span>
                        <a href="{{ route('legal.privacy') }}">Confidentialité</a>
                        <span class="footer-separator text-[var(--text-tertiary)]">•</span>
                        <a href="{{ route('legal.mentions') }}">Mentions légales</a>
                    </div>
                </div>
            </footer>

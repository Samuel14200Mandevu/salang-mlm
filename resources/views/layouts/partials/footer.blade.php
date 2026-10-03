    <footer class="border-t border-neutral-200 bg-neutral-50 px-4 py-6 text-sm text-neutral-600 md:px-8" role="contentinfo">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Salang MLM</p>
            <nav class="flex flex-wrap gap-x-4 gap-y-2" aria-label="Liens légaux">
                <a href="{{ route('legal.terms') }}" class="hover:text-primary-600">CGU</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-primary-600">Confidentialité</a>
                <a href="{{ route('cookie-policy') }}" class="hover:text-primary-600">Cookies</a>
                <a href="{{ route('terms-of-service') }}" class="hover:text-primary-600">Conditions</a>
            </nav>
        </div>
    </footer>

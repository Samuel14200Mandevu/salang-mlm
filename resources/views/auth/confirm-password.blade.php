<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-12 bg-[var(--bg-primary)]">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="logo-light">
                    <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang MLM" class="h-16 mx-auto">
                </div>
                <div class="logo-dark">
                    <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang MLM" class="h-16 mx-auto">
                </div>
                <h2 class="mt-4 text-2xl font-bold text-[var(--text-primary)]">Confirm Password</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    This is a secure area. Please confirm your password before continuing.
                </p>
            </div>

            <div class="card">
                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <!-- Password -->
                    <div class="mb-4">
                        <x-input-label for="password" :value="__('Password')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="password" class="input-primary mt-1" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn-primary w-full text-center">
                        {{ __('Confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

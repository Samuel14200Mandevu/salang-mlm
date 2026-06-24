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
                <h2 class="mt-4 text-2xl font-bold text-[var(--text-primary)]">Reset Password</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>

            <div class="card">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Email Address')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="email" class="input-primary mt-1" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn-primary w-full text-center">
                            {{ __('Send Reset Link') }}
                        </button>
                        
                        <p class="text-center text-sm text-[var(--text-secondary)]">
                            <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-semibold">
                                ← Back to Sign In
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

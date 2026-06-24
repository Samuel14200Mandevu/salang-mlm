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
                <h2 class="mt-4 text-3xl font-bold text-[var(--text-primary)]">Welcome Back</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">Sign in to your account to continue</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="card">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Email Address')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="email" class="input-primary mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <x-input-label for="password" :value="__('Password')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="password" class="input-primary mt-1" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between mb-6">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-[var(--border-color)] text-primary-600 shadow-sm focus:ring-primary-500" name="remember">
                            <span class="ms-2 text-sm text-[var(--text-secondary)]">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-primary-600 hover:text-primary-700" href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn-primary w-full text-center">
                            {{ __('Sign In') }}
                        </button>
                        
                        <p class="text-center text-sm text-[var(--text-secondary)]">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="text-primary-600 hover:text-primary-700 font-semibold">
                                Create Account
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

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
                    Enter your new password below.
                </p>
            </div>

            <div class="card">
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="mb-3">
                        <x-input-label for="email" :value="__('Email Address')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="email" class="input-primary mt-1" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <x-input-label for="password" :value="__('New Password')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="password" class="input-primary mt-1" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="password_confirmation" class="input-primary mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </div>

                    <button type="submit" class="btn-primary w-full text-center">
                        {{ __('Reset Password') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

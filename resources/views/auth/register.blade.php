<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-[var(--bg-primary)]">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-6">
                <div class="logo-light">
                    <img src="{{ asset('images/light_logo.jpeg') }}" alt="Salang MLM" class="h-16 mx-auto">
                </div>
                <div class="logo-dark">
                    <img src="{{ asset('images/dark_logo.jpeg') }}" alt="Salang MLM" class="h-16 mx-auto">
                </div>
                <h2 class="mt-3 text-2xl font-bold text-[var(--text-primary)]">Create Account</h2>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Start your journey with Salang MLM</p>
            </div>

            <div class="card">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <x-input-label for="name" :value="__('Full Name')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="name" class="input-primary mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <!-- Email Address -->
                    <div class="mb-3">
                        <x-input-label for="email" :value="__('Email Address')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="email" class="input-primary mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <x-input-label for="phone" :value="__('Phone Number')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="phone" class="input-primary mt-1" type="tel" name="phone" :value="old('phone')" required />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    <!-- Sponsor ID -->
                    <div class="mb-3">
                        <x-input-label for="sponsor_id" :value="__('Sponsor ID (Optional)')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="sponsor_id" class="input-primary mt-1" type="text" name="sponsor_id" :value="request()->query('ref') ?? old('sponsor_id')" placeholder="SALXXXXXX" />
                        <p class="mt-1 text-xs text-[var(--text-tertiary)]">Enter the sponsor ID of the person who invited you</p>
                        <x-input-error :messages="$errors->get('sponsor_id')" class="mt-1" />
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <x-input-label for="password" :value="__('Password')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="password" class="input-primary mt-1" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-[var(--text-secondary)]" />
                        <x-text-input id="password_confirmation" class="input-primary mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn-primary w-full text-center">
                            {{ __('Create Account') }}
                        </button>
                        
                        <p class="text-center text-sm text-[var(--text-secondary)]">
                            Already have an account?
                            <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-semibold">
                                Sign In
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Packages Info -->
            <div class="mt-4 p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                <p class="text-xs text-[var(--text-secondary)] text-center">
                    <strong class="text-primary-600">Start your journey with Salang Group!</strong><br>
                    Choose a package after registration to start earning commissions.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>

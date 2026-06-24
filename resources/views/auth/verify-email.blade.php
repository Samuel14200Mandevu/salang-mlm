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
                <h2 class="mt-4 text-2xl font-bold text-[var(--text-primary)]">Verify Your Email</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    Thanks for signing up! Before getting started, please verify your email address.
                </p>
            </div>

            <div class="card">
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 rounded-lg text-sm">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </div>
                @endif

                <p class="text-sm text-[var(--text-secondary)] mb-4">
                    If you didn't receive the email, we will gladly send you another.
                </p>

                <div class="flex flex-col gap-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full text-center">
                            {{ __('Resend Verification Email') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary w-full text-center">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

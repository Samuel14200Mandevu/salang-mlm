@php
    $bannerKeys = ['legacy', 'credentials', 'general', 'throttle'];
@endphp

@foreach ($bannerKeys as $key)
    @error($key)
        <div class="auth-alert-error mb-4" role="alert">
            <p>{{ $message }}</p>
            @if ($key === 'legacy')
                <a href="{{ route('login.legacy-info') }}" class="auth-btn-primary inline-flex w-full justify-center text-center mt-3">
                    Voir les informations
                </a>
            @endif
        </div>
    @enderror
@endforeach

@if (!empty($showErrors) && isset($errors) && $errors->any())
    @php
        $listed = collect($errors->getMessages())
            ->except(array_merge($bannerKeys, ['email', 'password', 'name', 'phone', 'sponsor_id', 'terms', 'password_confirmation', 'activation_code']))
            ->flatten();
    @endphp
    @if ($listed->isNotEmpty())
        <div class="auth-alert-error mb-4" role="alert">
            @if ($listed->count() === 1)
                <p>{{ $listed->first() }}</p>
            @else
                <ul class="auth-alert-list">
                    @foreach ($listed as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
@endif

@if (session('status'))
    <div class="auth-alert-success mb-4" role="status">{{ session('status') }}</div>
@endif

@if (session('success'))
    <div class="auth-alert-success mb-4" role="status">{{ session('success') }}</div>
@endif

@if (session('warning'))
    <div class="auth-notice mb-4" role="status">
        <p class="text-sm text-[var(--text-secondary)]">{{ session('warning') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="auth-alert-error mb-4" role="alert">{{ session('error') }}</div>
@endif

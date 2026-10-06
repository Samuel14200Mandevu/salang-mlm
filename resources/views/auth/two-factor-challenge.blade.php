@extends('layouts.auth')

@section('title', 'Double authentification — Salang Group')

@section('content')
    @include('partials.auth.page-header', [
        'title' => 'Authentification à deux facteurs',
        'lead' => 'Saisissez le code à 6 chiffres généré par votre application d’authentification.',
    ])

    @include('partials.auth.alerts', ['showErrors' => true])

    <form method="POST" action="{{ route('two-factor.login') }}" id="twoFactorForm" class="auth-form">
        @csrf

        <div class="auth-form-group" id="codeSection">
            <label for="code">Code à usage unique</label>
            <input type="text" name="code" id="code" inputmode="numeric" autocomplete="one-time-code"
                   class="auth-input text-center tracking-[0.35em] font-mono text-lg" maxlength="6" pattern="[0-9]*" autofocus>
            @error('code')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <p class="mb-4 text-xs text-[var(--text-secondary)]">
            <button type="button" id="showRecovery" class="auth-link font-normal bg-transparent border-0 cursor-pointer p-0 text-xs">
                Utiliser un code de récupération
            </button>
        </p>

        <div class="auth-form-group hidden" id="recoverySection">
            <label for="recovery_code">Code de récupération</label>
            <input type="text" name="recovery_code" id="recovery_code" class="auth-input font-mono"
                   placeholder="xxxx-xxxx-xxxx" autocomplete="off">
        </div>

        <button type="submit" class="auth-btn-primary" data-loading-text="Validation…">Valider</button>
    </form>

    <div class="auth-meta-links">
        <p>
            <a href="{{ route('logout') }}" class="auth-link font-normal"
               onclick="event.preventDefault(); document.getElementById('logout-form')?.submit();">Se déconnecter</a>
        </p>
    </div>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden auth-form-skip">@csrf</form>
@endsection

@include('partials.auth.flash-toasts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const showRecovery = document.getElementById('showRecovery');
    const recoverySection = document.getElementById('recoverySection');
    const codeSection = document.getElementById('codeSection');

    showRecovery?.addEventListener('click', function () {
        const showCode = recoverySection.classList.contains('hidden');
        recoverySection.classList.toggle('hidden', !showCode);
        codeSection.classList.toggle('hidden', showCode);
        showRecovery.textContent = showCode
            ? 'Revenir au code à 6 chiffres'
            : 'Utiliser un code de récupération';
        (showCode ? document.getElementById('recovery_code') : document.getElementById('code'))?.focus();
    });
});
</script>
@endpush

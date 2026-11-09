@extends('layouts.app')

@section('title', 'Parrainage')

@section('content')
<div class="profile-page profile-page--account profile-subpage profile-referral-page space-y-4">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text-primary)]">Parrainage</h1>
                <p class="text-sm text-[var(--text-secondary)] mt-1">Partagez votre lien et votre code QR Salang</p>
            </div>
            <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm">Mon réseau</a>
        </div>
    </div>
    @include('profile.partials.account-subpage-header', [
        'title' => 'Inviter & QR',
        'sub' => 'Lien et code QR de parrainage',
    ])

    <div class="profile-subpage-body profile-referral-layout">
        <div class="profile-referral-main space-y-4">
            <div class="card p-4 sm:p-6 text-center sm:text-left profile-referral-code-card">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-[var(--text-tertiary)] font-semibold">Votre code parrain</p>
                        <p class="text-3xl sm:text-4xl font-mono font-bold text-primary-500 mt-1">{{ $user->sponsor_id }}</p>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-2 break-all" id="profileReferralUrl">{{ $referralUrl }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                        <button type="button" class="btn btn-primary" data-copy-target="profileReferralUrl">Copier le lien</button>
                        <button type="button" class="btn btn-outline" data-copy-target="profileReferralCode">Copier le code</button>
                        <span id="profileReferralCode" class="sr-only">{{ $user->sponsor_id }}</span>
                    </div>
                </div>
            </div>

            <div class="card p-4 sm:p-6 flex flex-col md:flex-row items-center gap-5 md:gap-8" id="profileReferralQr">
                <div class="profile-referral-qr bg-white p-3 rounded-xl border border-[var(--border-light)] shrink-0">
                    {!! $qrSvg !!}
                </div>
                <div class="text-center md:text-left min-w-0">
                    <p class="text-base font-semibold text-[var(--text-primary)]">Scanner pour s’inscrire</p>
                    <p class="text-sm text-[var(--text-secondary)] mt-2 leading-relaxed">
                        Affichez ce QR lors d’un événement ou partagez le lien : le nouveau membre sera rattaché à votre code
                        <span class="font-mono font-semibold text-primary-500">{{ $user->sponsor_id }}</span>.
                    </p>
                    <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm mt-4 inline-flex md:hidden">Voir mon réseau</a>
                </div>
            </div>
        </div>

        <aside class="profile-referral-aside space-y-4">
            <section class="card p-4 sm:p-5">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Comment inviter</h3>
                <ol class="mt-3 space-y-2 text-sm text-[var(--text-secondary)] list-decimal list-inside leading-relaxed">
                    <li>Copiez votre lien ou montrez le QR.</li>
                    <li>Le filleul s’inscrit via ce lien.</li>
                    <li>Il apparaît dans votre réseau une fois le compte créé.</li>
                </ol>
            </section>
            <section class="card p-4 sm:p-5">
                <h3 class="text-sm font-semibold text-[var(--text-primary)]">Raccourcis</h3>
                <div class="mt-3 flex flex-col gap-2">
                    <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm w-full justify-center">Voir mon réseau</a>
                    <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm w-full justify-center">Mes commissions</a>
                    <a href="{{ route('profile.help') }}" class="btn btn-outline btn-sm w-full justify-center">Centre d’aide</a>
                </div>
            </section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-copy-target');
            var el = document.getElementById(id);
            var text = el ? (el.textContent || '').trim() : '';
            if (!text) return;
            var done = function () {
                if (typeof window.showToast === 'function') window.showToast('Copié');
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                alert(text);
            }
        });
    });
});
</script>
@endpush
@endsection

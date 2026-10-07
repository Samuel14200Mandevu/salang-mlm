@extends('layouts.app')

@section('title', 'Parrainage')

@section('content')
<div class="profile-page profile-page--account profile-subpage space-y-4">
    <div class="member-page-intro profile-page-intro-desktop hidden md:block">
        <h1 class="text-2xl font-bold text-[var(--text-primary)]">Parrainage</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-1">Partagez votre lien et votre code QR Salang</p>
    </div>
    @include('profile.partials.account-subpage-header', [
        'title' => 'Inviter & QR',
        'sub' => 'Lien et code QR de parrainage',
    ])

    <div class="profile-subpage-body">
        <div class="card p-4 text-center">
            <p class="text-xs text-[var(--text-secondary)] mb-1">Votre code parrain</p>
            <p class="text-2xl font-mono font-bold text-primary-500">{{ $user->sponsor_id }}</p>
            <p class="text-[10px] text-[var(--text-tertiary)] mt-2 break-all px-2" id="profileReferralUrl">{{ $referralUrl }}</p>
        </div>

        <div class="card p-4 flex flex-col items-center" id="profileReferralQr">
            <p class="text-sm font-semibold text-[var(--text-primary)] mb-3">Scanner pour s'inscrire</p>
            <div class="profile-referral-qr bg-white p-3 rounded-xl border border-[var(--border-light)]">
                {!! $qrSvg !!}
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <button type="button" class="btn btn-primary w-full" data-copy-target="profileReferralUrl">Copier le lien</button>
            <a href="{{ route('network.index') }}" class="btn btn-outline w-full">Voir mon réseau</a>
        </div>
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
                if (typeof window.showToast === 'function') window.showToast('Lien copié');
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

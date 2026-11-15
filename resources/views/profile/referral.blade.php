@extends('layouts.app')

@section('title', 'Parrainage')

@section('content')
@php
    $sponsorCode = (string) ($user->sponsor_id ?? '');
    $qrOnly = (bool) ($qrOnly ?? false);
    $shareText = $sponsorCode !== ''
        ? "Rejoins Salang avec mon code {$sponsorCode}"
        : 'Rejoins Salang';
@endphp
<div class="profile-page profile-page--account profile-subpage profile-referral-page space-y-4{{ $qrOnly ? ' profile-referral-page--qr-only' : '' }}">
    @if(! $qrOnly)
        <div class="member-page-intro profile-page-intro-desktop hidden md:block">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-[var(--text-primary)]">Inviter & QR</h1>
                    <p class="text-sm text-[var(--text-secondary)] mt-1">Partagez votre lien et votre code de parrainage Salang</p>
                </div>
                <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm">Mon réseau</a>
            </div>
        </div>
    @endif

    @include('profile.partials.account-subpage-header', [
        'title' => $qrOnly ? 'Code parrain & QR' : 'Inviter & QR',
        'sub' => $qrOnly ? null : 'Lien, code et QR de parrainage',
    ])

    @if($qrOnly)
        <div class="profile-subpage-body">
            <section class="profile-invite-qr-only" id="profileReferralQr" aria-label="Code QR de parrainage">
                <div class="profile-referral-qr profile-invite-qr profile-invite-qr--solo" role="img" aria-label="QR code de parrainage Salang">
                    {!! $qrSvg !!}
                </div>
                <p class="profile-invite-qr-only__code" id="profileReferralCode">{{ $sponsorCode }}</p>
                <span id="profileReferralUrl" class="sr-only">{{ $referralUrl }}</span>
            </section>
        </div>
    @else
        <div class="profile-subpage-body profile-referral-layout">
            <div class="profile-referral-main">
                <section class="profile-invite-hero" aria-labelledby="profileInviteCodeLabel">
                    <div class="profile-invite-hero__glow" aria-hidden="true"></div>

                    <div class="profile-invite-hero__code-block">
                        <p class="profile-invite-hero__eyebrow" id="profileInviteCodeLabel">Votre code parrain</p>
                        <p class="profile-invite-hero__code" id="profileReferralCode">{{ $sponsorCode }}</p>
                        <p class="profile-invite-hero__hint">Les nouveaux membres s’inscrivent avec ce code</p>
                    </div>

                    <div class="profile-invite-hero__qr-wrap" id="profileReferralQr">
                        <div class="profile-referral-qr profile-invite-qr" role="img" aria-label="QR code de parrainage Salang">
                            {!! $qrSvg !!}
                        </div>
                        <p class="profile-invite-hero__qr-caption">À scanner pour s’inscrire</p>
                    </div>

                    <div class="profile-invite-link" aria-label="Lien de parrainage">
                        <div class="profile-invite-link__body">
                            <span class="profile-invite-link__label">Lien d’invitation</span>
                            <p class="profile-invite-link__url" id="profileReferralUrl">{{ $referralUrl }}</p>
                        </div>
                        <button
                            type="button"
                            class="profile-invite-link__copy"
                            data-copy-target="profileReferralUrl"
                            data-copy-toast="Lien copié"
                            aria-label="Copier le lien"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="profile-invite-actions">
                        <button
                            type="button"
                            class="btn btn-primary profile-invite-actions__primary"
                            id="profileInviteShare"
                            data-share-url="{{ $referralUrl }}"
                            data-share-text="{{ $shareText }}"
                        >
                            <svg class="profile-invite-actions__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            Partager
                        </button>
                        <a
                            class="btn btn-outline profile-invite-actions__wa"
                            href="https://wa.me/?text={{ rawurlencode($shareText.' '.$referralUrl) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            WhatsApp
                        </a>
                        <button
                            type="button"
                            class="btn btn-outline"
                            data-copy-target="profileReferralCode"
                            data-copy-toast="Code copié"
                        >
                            Copier le code
                        </button>
                    </div>
                </section>

                <ol class="profile-invite-steps" aria-label="Comment inviter">
                    <li class="profile-invite-steps__item">
                        <span class="profile-invite-steps__n" aria-hidden="true">1</span>
                        <span>Partagez le lien, le code ou montrez le QR.</span>
                    </li>
                    <li class="profile-invite-steps__item">
                        <span class="profile-invite-steps__n" aria-hidden="true">2</span>
                        <span>Le filleul s’inscrit via votre invitation.</span>
                    </li>
                    <li class="profile-invite-steps__item">
                        <span class="profile-invite-steps__n" aria-hidden="true">3</span>
                        <span>Il apparaît dans votre réseau dès le compte créé.</span>
                    </li>
                </ol>
            </div>

            <aside class="profile-referral-aside space-y-4">
                <section class="card p-4 sm:p-5 profile-invite-aside-card">
                    <h3 class="profile-invite-aside-card__title">Raccourcis</h3>
                    <div class="mt-3 flex flex-col gap-2">
                        <a href="{{ route('network.index') }}" class="btn btn-outline btn-sm w-full justify-center">Voir mon réseau</a>
                        <a href="{{ route('commissions.index') }}" class="btn btn-outline btn-sm w-full justify-center">Mes commissions</a>
                        <a href="{{ route('profile.help') }}" class="btn btn-outline btn-sm w-full justify-center">Centre d’aide</a>
                    </div>
                </section>
                <section class="card p-4 sm:p-5 profile-invite-aside-card hidden md:block">
                    <h3 class="profile-invite-aside-card__title">Astuce</h3>
                    <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed text-pretty">
                        En événement, affichez le QR en plein écran : le filleul scanne et s’inscrit déjà rattaché à
                        <span class="font-mono font-semibold text-primary-500">{{ $sponsorCode }}</span>.
                    </p>
                </section>
            </aside>
        </div>
    @endif
</div>

@if(! $qrOnly)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function toast(msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(msg);
            return;
        }
        console.info(msg);
    }

    function fallbackCopy(text, done) {
        var input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();
        try {
            document.execCommand('copy');
            done();
        } catch (e) {
            /* ignore */
        }
        document.body.removeChild(input);
    }

    function copyText(text, message) {
        if (!text) return;
        var done = function () { toast(message || 'Copié'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function () {
                fallbackCopy(text, done);
            });
        } else {
            fallbackCopy(text, done);
        }
    }

    document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-copy-target');
            var el = document.getElementById(id);
            var text = el ? (el.textContent || '').trim() : '';
            copyText(text, btn.getAttribute('data-copy-toast') || 'Copié');
        });
    });

    var shareBtn = document.getElementById('profileInviteShare');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            var url = shareBtn.getAttribute('data-share-url') || '';
            var text = shareBtn.getAttribute('data-share-text') || '';
            if (!url) return;
            if (navigator.share) {
                navigator.share({
                    title: 'Rejoins Salang',
                    text: text,
                    url: url,
                }).catch(function () {});
                return;
            }
            copyText(url, 'Lien copié');
        });
    }
});
</script>
@endpush
@endif
@endsection

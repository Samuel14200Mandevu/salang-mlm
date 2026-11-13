{{-- Modal résultat retrait — succès / échec (mockup payaiment.webp) --}}
@php
    $result = $withdrawalResult ?? session('withdrawal_result');
@endphp
@if(is_array($result ?? null) && ! empty($result['status']))
@php
    $isSuccess = ($result['status'] ?? '') === 'success';
    $amount = isset($result['amount']) ? (float) $result['amount'] : null;
    $dateLabel = $result['date'] ?? now()->format('d M Y, H:i');
    $reference = $result['reference'] ?? ($result['transaction_id'] ?? null);
    $subtitle = $result['message'] ?? (
        $isSuccess
            ? 'Votre retrait a été traité avec succès !'
            : 'Veuillez choisir une autre méthode de paiement.'
    );
    $title = $result['title'] ?? ($isSuccess ? 'Paiement réussi !' : 'Paiement échoué !');
    $primaryLabel = $result['button'] ?? ($isSuccess ? 'Terminé' : 'Retour');
    $primaryHref = $result['url'] ?? route('withdrawal.index');
@endphp

<div
    id="withdrawalResultModal"
    class="member-pay-result-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="withdrawalResultTitle"
    data-withdrawal-result="1"
>
    <div class="member-pay-result member-pay-result--{{ $isSuccess ? 'success' : 'failed' }}">
        <button
            type="button"
            class="member-pay-result__close"
            data-withdrawal-result-dismiss
            aria-label="Fermer"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

        <div class="member-pay-result__icon" aria-hidden="true">
            @if($isSuccess)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            @endif
        </div>

        <h2 id="withdrawalResultTitle" class="member-pay-result__title">{{ $title }}</h2>
        <p class="member-pay-result__sub">{{ $subtitle }}</p>

        @if($isSuccess)
            <div class="member-pay-result__details">
                <h3 class="member-pay-result__details-title">Détails de la transaction</h3>
                <dl class="member-pay-result__rows">
                    @if($amount !== null)
                        <div class="member-pay-result__row">
                            <dt>Montant</dt>
                            <dd>${{ number_format($amount, 2) }}</dd>
                        </div>
                    @endif
                    <div class="member-pay-result__row">
                        <dt>Date</dt>
                        <dd>{{ $dateLabel }}</dd>
                    </div>
                    @if($reference)
                        <div class="member-pay-result__row">
                            <dt>Référence</dt>
                            <dd>{{ $reference }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        <a href="{{ $primaryHref }}" class="member-pay-result__btn" data-withdrawal-result-dismiss>
            {{ $primaryLabel }}
        </a>
    </div>
</div>

<script>
(function () {
    var overlay = document.getElementById('withdrawalResultModal');
    if (!overlay) return;

    function dismiss() {
        overlay.classList.add('is-hiding');
        window.setTimeout(function () {
            if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 220);
    }

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) dismiss();
    });

    overlay.querySelectorAll('[data-withdrawal-result-dismiss]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (el.tagName === 'BUTTON') {
                e.preventDefault();
                dismiss();
                return;
            }
            var href = el.getAttribute('href') || '';
            var here = window.location.pathname + window.location.search;
            if (!href || href === '#' || href === here || href === window.location.pathname) {
                e.preventDefault();
                dismiss();
            }
        });
    });

    document.addEventListener('keydown', function onKey(e) {
        if (e.key === 'Escape') {
            dismiss();
            document.removeEventListener('keydown', onKey);
        }
    });
})();
</script>
@endif

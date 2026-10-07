@php
    $formId = $formId ?? 'withdrawForm';
    $isFintech = ($variant ?? 'classic') === 'fintech';
    $maxBalance = (float) ($balance ?? 0);
@endphp
<form
    id="{{ $formId }}"
    action="{{ route('withdrawal.store') }}"
    method="POST"
    class="member-withdraw-form {{ $isFintech ? 'member-withdraw-form--fintech' : '' }}"
    data-withdraw-form
    data-balance="{{ $maxBalance }}"
    data-fee-rate="0.025"
    data-min="10"
>
    @csrf

    <div class="member-withdraw-form__section">
        <div class="member-withdraw-form__section-head">
            <span class="member-withdraw-form__step">1</span>
            <h3 class="member-withdraw-form__section-title">Méthode de retrait</h3>
        </div>
        <div class="member-withdraw-methods" role="radiogroup" aria-label="Méthode de retrait">
            <button type="button" class="member-withdraw-method is-selected" data-method="crypto" data-withdraw-method>
                <span class="member-withdraw-method__icon" aria-hidden="true">₿</span>
                <span class="member-withdraw-method__body">
                    <span class="member-withdraw-method__label">Crypto</span>
                    <span class="member-withdraw-method__hint">USDT · BTC · ETH</span>
                </span>
                <svg class="member-withdraw-method__chev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button type="button" class="member-withdraw-method" data-method="mobile_money" data-withdraw-method>
                <span class="member-withdraw-method__icon member-withdraw-method__icon--mobile" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
                <span class="member-withdraw-method__body">
                    <span class="member-withdraw-method__label">Mobile Money</span>
                    <span class="member-withdraw-method__hint">Orange · Airtel · M-Pesa</span>
                </span>
                <svg class="member-withdraw-method__chev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button type="button" class="member-withdraw-method" data-method="bank" data-withdraw-method>
                <span class="member-withdraw-method__icon member-withdraw-method__icon--bank" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <span class="member-withdraw-method__body">
                    <span class="member-withdraw-method__label">Virement bancaire</span>
                    <span class="member-withdraw-method__hint">IBAN · RIB · SWIFT</span>
                </span>
                <svg class="member-withdraw-method__chev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <select name="method" id="{{ $formId }}_method" class="sr-only" required data-withdraw-method-select>
            <option value="crypto">crypto</option>
            <option value="mobile_money">mobile_money</option>
            <option value="bank">bank</option>
        </select>
    </div>

    <div class="member-withdraw-form__section">
        <div class="member-withdraw-form__section-head">
            <span class="member-withdraw-form__step">2</span>
            <h3 class="member-withdraw-form__section-title">Montant</h3>
        </div>
        <div class="member-withdraw-amount">
            <label class="sr-only" for="{{ $formId }}_amount">Montant en USD</label>
            <div class="member-withdraw-amount__row">
                <input
                    type="number"
                    name="amount"
                    id="{{ $formId }}_amount"
                    step="0.01"
                    min="10"
                    max="{{ $maxBalance }}"
                    class="member-withdraw-amount__input @error('amount') input-error @enderror"
                    placeholder="0.00"
                    inputmode="decimal"
                    required
                    data-withdraw-amount
                >
                <span class="member-withdraw-amount__unit">USD</span>
                <button type="button" class="member-withdraw-amount__max" data-withdraw-max>Tout</button>
            </div>
            @error('amount')
                <p class="member-withdraw-form__error">{{ $message }}</p>
            @enderror
            <p class="member-withdraw-amount__avail">
                Disponible&nbsp;: <strong data-withdraw-balance-label>${{ number_format($maxBalance, 2) }}</strong>
                · Min.&nbsp;10&nbsp;USD
            </p>
        </div>

        <div class="member-withdraw-summary" aria-live="polite">
            <div class="member-withdraw-summary__row">
                <span>Montant</span>
                <span data-withdraw-summary-gross>$0.00</span>
            </div>
            <div class="member-withdraw-summary__row">
                <span>Frais (2,5&nbsp;%)</span>
                <span data-withdraw-summary-fee>$0.00</span>
            </div>
            <div class="member-withdraw-summary__row member-withdraw-summary__row--total">
                <span>Vous recevez</span>
                <span data-withdraw-summary-net>$0.00</span>
            </div>
        </div>
    </div>

    <div class="member-withdraw-form__section">
        <div class="member-withdraw-form__section-head">
            <span class="member-withdraw-form__step">3</span>
            <h3 class="member-withdraw-form__section-title">Destination</h3>
        </div>

        <div class="member-withdraw-dest" data-withdraw-fields="crypto">
            <label class="member-withdraw-field__label" for="{{ $formId }}_address">Adresse du portefeuille</label>
            <input
                type="text"
                name="address"
                id="{{ $formId }}_address"
                class="member-withdraw-field__input @error('address') input-error @enderror"
                placeholder="Adresse USDT (TRC20), BTC ou ETH"
                autocomplete="off"
            >
            @error('address')
                <p class="member-withdraw-form__error">{{ $message }}</p>
            @enderror
            <p class="member-withdraw-field__hint">Vérifiez le réseau avant envoi — erreur = perte des fonds.</p>
        </div>

        <div class="member-withdraw-dest hidden" data-withdraw-fields="mobile_money" hidden>
            <label class="member-withdraw-field__label" for="{{ $formId }}_phone">Numéro Mobile Money</label>
            <input
                type="tel"
                name="phone"
                id="{{ $formId }}_phone"
                class="member-withdraw-field__input @error('phone') input-error @enderror"
                placeholder="+243 …"
                autocomplete="tel"
            >
            @error('phone')
                <p class="member-withdraw-form__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="member-withdraw-dest hidden" data-withdraw-fields="bank" hidden>
            <label class="member-withdraw-field__label" for="{{ $formId }}_bank">Coordonnées bancaires</label>
            <textarea
                name="bank_details"
                id="{{ $formId }}_bank"
                rows="3"
                class="member-withdraw-field__input member-withdraw-field__textarea @error('bank_details') input-error @enderror"
                placeholder="Banque, titulaire, IBAN/RIB, SWIFT…"
            ></textarea>
            @error('bank_details')
                <p class="member-withdraw-form__error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="member-withdraw-form__submit-wrap">
        <button type="submit" class="member-withdraw-submit btn btn-primary" data-withdraw-submit disabled>
            Confirmer le retrait
        </button>
        <p class="member-withdraw-form__legal">
            Plafond journalier 5&nbsp;000&nbsp;USD (demandes en attente). KYC requis au-delà de 5&nbsp;000&nbsp;USD cumulés retirés.
        </p>
    </div>
</form>

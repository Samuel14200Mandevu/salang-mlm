@extends('cashier.layouts.app')

@section('title', 'Nouveau rapport')

@push('styles')
<style>
    .section-box {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }
    .section-box h3 {
        font-size: 0.813rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .currency-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    .currency-col {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 6px);
        padding: 0.75rem;
    }
    .currency-col .currency-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
        padding-bottom: 0.375rem;
        border-bottom: 1px solid var(--border-color);
    }
    .currency-col.usd .currency-title { color: #0E2F76; }
    .currency-col.cdf .currency-title { color: #8b0000; }
    .auto-value {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.35rem 0;
        font-size: 0.813rem;
        border-bottom: 1px dashed var(--border-color);
    }
    .auto-value:last-child { border-bottom: none; }
    .auto-value .label { color: var(--text-tertiary); }
    .auto-value .value { font-weight: 700; color: var(--text-primary); }
    .auto-value.total {
        margin-top: 0.35rem;
        padding-top: 0.5rem;
        border-top: 1.5px solid var(--primary);
        border-bottom: none;
    }
    .auto-value.total .label { color: var(--primary); font-weight: 700; }
    .auto-value.total .value { color: var(--primary); font-size: 0.938rem; }
    .form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }
    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md, 6px);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.875rem;
        font-family: inherit;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(30, 93, 173, 0.08);
    }
    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 0.375rem; padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px); font-weight: 600;
        font-size: 0.875rem; cursor: pointer; border: none; text-decoration: none;
    }
    .btn-primary { background: var(--primary); color: #FFF; }
    .btn-primary:hover { background: var(--primary-hover, #134178); }
    .btn-outline {
        background: transparent; color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover { background: var(--bg-card); border-color: var(--primary); color: var(--primary); }

    @media (max-width: 640px) {
        .currency-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Nouveau rapport de caisse
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5">
                Date : <strong>{{ $reportDate->format('d/m/Y') }}</strong>
            </p>
        </div>
        <a href="{{ route('cashier.reports.index') }}" class="btn btn-outline">← Retour</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-3 text-red-800 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cashier.reports.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="report_date" value="{{ $reportDate->format('Y-m-d') }}">

        {{-- SECTION 1 : RÉSUMÉ DES VENTES --}}
        <div class="section-box">
            <h3>1. Résumé des ventes</h3>

            <div class="currency-grid">
                <div class="currency-col usd">
                    <div class="currency-title">USD ($)</div>
                    <div class="auto-value">
                        <span class="label">Ventes POS</span>
                        <span class="value">${{ number_format($total_pos, 2) }}</span>
                    </div>
                    <div class="auto-value">
                        <span class="label">Ventes MLM</span>
                        <span class="value">${{ number_format($total_mlm, 2) }}</span>
                    </div>
                    <div class="auto-value total">
                        <span class="label">TOTAL</span>
                        <span class="value">${{ number_format($total_sales, 2) }}</span>
                    </div>
                </div>

                <div class="currency-col cdf">
                    <div class="currency-title">CDF (FC)</div>
                    <div class="auto-value">
                        <span class="label">Ventes POS</span>
                        <span class="value">FC {{ number_format($total_pos_cdf, 0, ',', ' ') }}</span>
                    </div>
                    <div class="auto-value">
                        <span class="label">Ventes MLM</span>
                        <span class="value">FC {{ number_format($total_mlm_cdf, 0, ',', ' ') }}</span>
                    </div>
                    <div class="auto-value total">
                        <span class="label">TOTAL</span>
                        <span class="value">FC {{ number_format($total_sales_cdf, 0, ',', ' ') }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
                <div class="auto-value"><span class="label">Commandes</span><span class="value">{{ $total_orders }}</span></div>
                <div class="auto-value"><span class="label">PV</span><span class="value">{{ number_format($total_pv) }}</span></div>
                <div class="auto-value"><span class="label">BV</span><span class="value">{{ number_format($total_bv) }}</span></div>
            </div>
        </div>

        {{-- SECTION 2 : MODES DE PAIEMENT --}}
        <div class="section-box">
            <h3>2. Détail par mode de paiement</h3>

            <div class="currency-grid">
                <div class="currency-col usd">
                    <div class="currency-title">USD ($)</div>
                    <div class="auto-value"><span class="label">Espèces</span><span class="value">${{ number_format($cash_amount, 2) }}</span></div>
                    <div class="auto-value"><span class="label">Mobile Money</span><span class="value">${{ number_format($mobile_money_amount, 2) }}</span></div>
                    <div class="auto-value"><span class="label">Banque</span><span class="value">${{ number_format($bank_amount, 2) }}</span></div>
                </div>
                <div class="currency-col cdf">
                    <div class="currency-title">CDF (FC)</div>
                    <div class="auto-value"><span class="label">Espèces</span><span class="value">FC {{ number_format($cash_amount_cdf, 0, ',', ' ') }}</span></div>
                    <div class="auto-value"><span class="label">Mobile Money</span><span class="value">FC {{ number_format($mobile_money_amount_cdf, 0, ',', ' ') }}</span></div>
                    <div class="auto-value"><span class="label">Banque</span><span class="value">FC {{ number_format($bank_amount_cdf, 0, ',', ' ') }}</span></div>
                </div>
            </div>
        </div>

        {{-- SECTION 3 : DÉPENSES & COMMISSIONS --}}
        <div class="section-box">
            <h3>3. Dépenses & Commissions</h3>

            <div class="currency-grid">
                <div class="currency-col usd">
                    <div class="currency-title">USD ($)</div>
                    <div class="auto-value"><span class="label">Dépenses</span><span class="value" style="color:#b32a2a;">- ${{ number_format($total_expenses, 2) }}</span></div>
                    <div class="auto-value"><span class="label">Commissions cash</span><span class="value" style="color:#b32a2a;">- ${{ number_format($total_commissions, 2) }}</span></div>
                    <div class="auto-value total"><span class="label">SOLDE NET</span><span class="value">${{ number_format($net_balance, 2) }}</span></div>
                </div>
                <div class="currency-col cdf">
                    <div class="currency-title">CDF (FC)</div>
                    <div class="auto-value"><span class="label">Dépenses</span><span class="value" style="color:#b32a2a;">- FC {{ number_format($total_expenses_cdf, 0, ',', ' ') }}</span></div>
                    <div class="auto-value"><span class="label">Commissions cash</span><span class="value" style="color:#b32a2a;">- FC {{ number_format($total_commissions_cdf, 0, ',', ' ') }}</span></div>
                    <div class="auto-value total"><span class="label">SOLDE NET</span><span class="value">FC {{ number_format($net_balance_cdf, 0, ',', ' ') }}</span></div>
                </div>
            </div>
        </div>

        {{-- SECTION 4 : CAISSE --}}
        <div class="section-box">
            <h3>4. État de la caisse</h3>

            <div class="currency-grid">
                <div class="currency-col usd">
                    <div class="currency-title">USD ($)</div>
                    <div class="mb-2">
                        <label class="form-label">Fond de caisse (ouverture)</label>
                        <input type="number" name="opening_balance" step="0.01" min="0"
                               value="{{ old('opening_balance', 0) }}" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Caisse comptée</label>
                        <input type="number" name="closing_balance" step="0.01" min="0"
                               value="{{ old('closing_balance', 0) }}" class="form-control" required>
                    </div>
                </div>

                <div class="currency-col cdf">
                    <div class="currency-title">CDF (FC)</div>
                    <div class="mb-2">
                        <label class="form-label">Fond de caisse (ouverture)</label>
                        <input type="number" name="opening_balance_cdf" step="1" min="0"
                               value="{{ old('opening_balance_cdf', 0) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Caisse comptée</label>
                        <input type="number" name="closing_balance_cdf" step="1" min="0"
                               value="{{ old('closing_balance_cdf', 0) }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="flex items-start gap-2 text-xs text-[var(--text-tertiary)] mt-3">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Caisse théorique = Fond de caisse + Encaissements espèces − Dépenses espèces. L'écart est calculé automatiquement à l'enregistrement.</span>
            </div>
        </div>

        {{-- SECTION 5 : NOUVEAUX INSCRITS --}}
        <div class="section-box">
            <h3>5. Nouveaux inscrits</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="auto-value">
                    <span class="label">Nouveaux membres</span>
                    <span class="value">{{ $new_members }}</span>
                </div>
                <div class="auto-value">
                    <span class="label">Nouveaux clients</span>
                    <span class="value">{{ $new_clients }}</span>
                </div>
            </div>
        </div>

        {{-- SECTION 6 : OBSERVATIONS --}}
        <div class="section-box">
            <h3>6. Observations</h3>
            <textarea name="notes" rows="3" class="form-control"
                      placeholder="Notes, incidents, remarques, écarts constatés...">{{ old('notes') }}</textarea>
        </div>

        {{-- SECTION 7 : SIGNATURE --}}
        <div class="section-box">
            <h3>7. Signature</h3>
            <label class="form-label">Nom du signataire</label>
            <input type="text" name="signature_name" value="{{ old('signature_name', auth()->user()->name) }}"
                   class="form-control" required maxlength="255">
            <p class="text-xs text-[var(--text-tertiary)] mt-2">
                En signant, je certifie l'exactitude des informations ci-dessus.
            </p>
        </div>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-2 pt-3 border-t border-[var(--border-color)]">
            <button type="submit" name="action" value="draft" class="btn btn-outline">
                Enregistrer en brouillon
            </button>
            <button type="submit" name="action" value="submit" class="btn btn-primary">
                Soumettre pour validation
            </button>
        </div>
    </form>
</div>
@endsection
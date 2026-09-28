@extends('cashier.layouts.app')

@section('title', 'Rapport ' . $report->report_number)

@push('styles')
<style>
    .report-section {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem 1.25rem;
    }
    .report-section h3 {
        font-size: 0.813rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        font-size: 0.813rem;
        border-bottom: 1px dashed var(--border-color);
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .label {
        color: var(--text-tertiary);
        font-weight: 500;
    }
    .info-row .value {
        font-weight: 700;
        color: var(--text-primary);
        text-align: right;
    }
    .info-row .value-usd { color: #0E2F76; }
    .info-row .value-cdf { color: #8b0000; }
    .info-row .value-green { color: #16a34a; }
    .info-row .value-red { color: #b32a2a; }

    .stat-block {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 0.875rem 1rem;
    }
    .stat-block .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
        margin-bottom: 0.25rem;
    }
    .stat-block .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-block.usd .stat-value { color: var(--primary); }
    .stat-block.cdf .stat-value { color: #8b0000; }
    .stat-block.balance .stat-value { color: #16a34a; }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 0.375rem; padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px); font-weight: 600;
        font-size: 0.875rem; cursor: pointer; border: none; text-decoration: none;
    }
    .btn-primary { background: var(--primary); color: #FFF; }
    .btn-primary:hover { background: var(--primary-hover, #091E3B); }
    .btn-outline {
        background: transparent; color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover { background: var(--bg-card); border-color: var(--primary); color: var(--primary); }
    .btn-danger-outline {
        background: transparent; color: #b32a2a; border: 1.5px solid #b32a2a;
    }
    .btn-danger-outline:hover { background: #fef2f2; }

    .form-control {
        width: 100%;
        padding: 0.4rem 0.6rem;
        border-radius: var(--radius-md, 6px);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        font-family: inherit;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(15, 43, 79, 0.08);
    }

    .badge {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(15, 43, 79, 0.10); color: var(--primary); }
    .badge-secondary { background: rgba(107, 114, 128, 0.12); color: #6b7280; }

    @media (max-width: 640px) {
        .stat-block .stat-value { font-size: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Rapport {{ $report->report_number }}
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5">
                {{ $report->report_date->format('d/m/Y') }} — {{ $report->user->name }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.reports.pdf', $report->id) }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Télécharger PDF
            </a>
            <a href="{{ route('cashier.reports.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    {{-- STATUT + ACTIONS ADMIN --}}
    <div class="report-section flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="badge {{ $report->status_badge_class }}">{{ $report->status_label }}</span>
            @if($report->approver)
                <span class="text-sm text-[var(--text-secondary)]">
                    Approuvé par <strong>{{ $report->approver->name }}</strong>
                    le {{ $report->approved_at?->format('d/m/Y H:i') }}
                </span>
            @endif
        </div>
        @if(auth()->user()->hasRole('admin') && $report->status === 'submitted')
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('cashier.reports.approve', $report->id) }}">
                    @csrf
                    <button class="btn btn-primary">Approuver</button>
                </form>
                <form method="POST" action="{{ route('cashier.reports.reject', $report->id) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="rejection_reason" placeholder="Motif du rejet..." class="form-control" required>
                    <button class="btn btn-danger-outline">Rejeter</button>
                </form>
            </div>
        @endif
    </div>

    {{-- STATS RAPIDES --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="stat-block usd">
            <p class="stat-label">Ventes USD</p>
            <p class="stat-value">${{ number_format($report->total_sales, 2) }}</p>
        </div>
        <div class="stat-block cdf">
            <p class="stat-label">Ventes CDF</p>
            <p class="stat-value">FC {{ number_format($report->total_sales_cdf, 0, ',', ' ') }}</p>
        </div>
        <div class="stat-block balance">
            <p class="stat-label">Solde net USD</p>
            <p class="stat-value">${{ number_format($report->net_balance, 2) }}</p>
        </div>
        <div class="stat-block balance">
            <p class="stat-label">Solde net CDF</p>
            <p class="stat-value">FC {{ number_format($report->net_balance_cdf, 0, ',', ' ') }}</p>
        </div>
    </div>

    {{-- VENTES --}}
    <div class="report-section">
        <h3>Résumé des ventes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div>
                <div class="info-row">
                    <span class="label">Ventes POS (USD)</span>
                    <span class="value value-usd">${{ number_format($report->total_pos, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Ventes MLM (USD)</span>
                    <span class="value value-usd">${{ number_format($report->total_mlm, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Total ventes USD</span>
                    <span class="value value-usd">${{ number_format($report->total_sales, 2) }}</span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="label">Ventes POS (CDF)</span>
                    <span class="value value-cdf">FC {{ number_format($report->total_pos_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Ventes MLM (CDF)</span>
                    <span class="value value-cdf">FC {{ number_format($report->total_mlm_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Total ventes CDF</span>
                    <span class="value value-cdf">FC {{ number_format($report->total_sales_cdf, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-4">
            <div class="info-row" style="border-bottom: none;">
                <span class="label">Commandes</span>
                <span class="value">{{ $report->total_orders }}</span>
            </div>
            <div class="info-row" style="border-bottom: none;">
                <span class="label">PV</span>
                <span class="value">{{ number_format($report->total_pv) }}</span>
            </div>
            <div class="info-row" style="border-bottom: none;">
                <span class="label">BV</span>
                <span class="value">{{ number_format($report->total_bv) }}</span>
            </div>
        </div>
    </div>

    {{-- MODES DE PAIEMENT --}}
    <div class="report-section">
        <h3>Détail par mode de paiement</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div>
                <div class="info-row">
                    <span class="label">Espèces USD</span>
                    <span class="value value-usd">${{ number_format($report->cash_amount, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Mobile Money USD</span>
                    <span class="value value-usd">${{ number_format($report->mobile_money_amount, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Banque USD</span>
                    <span class="value value-usd">${{ number_format($report->bank_amount, 2) }}</span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="label">Espèces CDF</span>
                    <span class="value value-cdf">FC {{ number_format($report->cash_amount_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Mobile Money CDF</span>
                    <span class="value value-cdf">FC {{ number_format($report->mobile_money_amount_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Banque CDF</span>
                    <span class="value value-cdf">FC {{ number_format($report->bank_amount_cdf, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- DÉPENSES & COMMISSIONS --}}
    <div class="report-section">
        <h3>Dépenses & Commissions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div>
                <div class="info-row">
                    <span class="label">Dépenses USD</span>
                    <span class="value value-red">- ${{ number_format($report->total_expenses, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Commissions cash USD</span>
                    <span class="value value-red">- ${{ number_format($report->total_commissions, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Solde net USD</span>
                    <span class="value value-green">${{ number_format($report->net_balance, 2) }}</span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="label">Dépenses CDF</span>
                    <span class="value value-red">- FC {{ number_format($report->total_expenses_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Commissions cash CDF</span>
                    <span class="value value-red">- FC {{ number_format($report->total_commissions_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Solde net CDF</span>
                    <span class="value value-green">FC {{ number_format($report->net_balance_cdf, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ÉTAT DE LA CAISSE --}}
    <div class="report-section">
        <h3>État de la caisse</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div>
                <div class="info-row">
                    <span class="label">Fond de caisse USD</span>
                    <span class="value value-usd">${{ number_format($report->opening_balance, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Caisse comptée USD</span>
                    <span class="value value-usd">${{ number_format($report->closing_balance, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Caisse théorique USD</span>
                    <span class="value value-usd">${{ number_format($report->theoretical_balance, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Écart USD</span>
                    <span class="value" style="color: {{ $report->difference_color }};">
                        ${{ number_format($report->difference, 2) }}
                    </span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="label">Fond de caisse CDF</span>
                    <span class="value value-cdf">FC {{ number_format($report->opening_balance_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Caisse comptée CDF</span>
                    <span class="value value-cdf">FC {{ number_format($report->closing_balance_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Caisse théorique CDF</span>
                    <span class="value value-cdf">FC {{ number_format($report->theoretical_balance_cdf, 0, ',', ' ') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Écart CDF</span>
                    <span class="value" style="color: {{ abs($report->difference_cdf) < 1 ? '#16a34a' : (abs($report->difference_cdf) <= 5000 ? '#f59e0b' : '#b32a2a') }};">
                        FC {{ number_format($report->difference_cdf, 0, ',', ' ') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- PRODUITS VENDUS --}}
    @if(!empty($report->details['products_sold']) && count($report->details['products_sold']) > 0)
        <div class="report-section">
            <h3>Produits vendus</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <th class="text-left py-2 text-xs uppercase tracking-wider text-[var(--text-tertiary)]">Produit</th>
                            <th class="text-center py-2 text-xs uppercase tracking-wider text-[var(--text-tertiary)]" style="width: 80px;">Qté</th>
                            <th class="text-center py-2 text-xs uppercase tracking-wider text-[var(--text-tertiary)]" style="width: 80px;">Devise</th>
                            <th class="text-right py-2 text-xs uppercase tracking-wider text-[var(--text-tertiary)]" style="width: 140px;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report->details['products_sold'] as $product)
                            @php
                                $currency = is_array($product) ? ($product['currency'] ?? 'USD') : ($product->currency ?? 'USD');
                                $name = is_array($product) ? ($product['name'] ?? 'N/A') : ($product->name ?? 'N/A');
                                $qty = is_array($product) ? ($product['total_quantity'] ?? 0) : ($product->total_quantity ?? 0);
                                $amount = is_array($product) ? ($product['total_amount'] ?? 0) : ($product->total_amount ?? 0);
                            @endphp
                            <tr style="border-bottom: 1px dashed var(--border-color);">
                                <td class="py-2 text-[var(--text-primary)]">{{ $name }}</td>
                                <td class="py-2 text-center text-[var(--text-secondary)]">{{ $qty }}</td>
                                <td class="py-2 text-center text-xs font-semibold" style="color: {{ $currency === 'CDF' ? '#8b0000' : '#0E2F76' }};">
                                    {{ $currency }}
                                </td>
                                <td class="py-2 text-right font-semibold" style="color: {{ $currency === 'CDF' ? '#8b0000' : '#0E2F76' }};">
                                    @if($currency === 'CDF')
                                        FC {{ number_format($amount, 0, ',', ' ') }}
                                    @else
                                        ${{ number_format($amount, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- NOUVEAUX INSCRITS --}}
    <div class="report-section">
        <h3>Nouveaux inscrits</h3>
        <div class="grid grid-cols-2 gap-3">
            <div class="info-row" style="border-bottom: none;">
                <span class="label">Nouveaux membres</span>
                <span class="value">{{ $report->new_members }}</span>
            </div>
            <div class="info-row" style="border-bottom: none;">
                <span class="label">Nouveaux clients</span>
                <span class="value">{{ $report->new_clients }}</span>
            </div>
        </div>
    </div>

    {{-- OBSERVATIONS --}}
    @if($report->notes)
        <div class="report-section">
            <h3>Observations du caissier</h3>
            <p class="text-sm text-[var(--text-secondary)] whitespace-pre-line">{{ $report->notes }}</p>
        </div>
    @endif

    {{-- SIGNATURE --}}
    <div class="report-section">
        <h3>Signature</h3>
        <div class="info-row">
            <span class="label">Signé par</span>
            <span class="value">{{ $report->signature_name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date de signature</span>
            <span class="value">{{ $report->signature_at?->format('d/m/Y H:i') }}</span>
        </div>
        @if($report->approver)
            <div class="info-row">
                <span class="label">Approuvé par</span>
                <span class="value">{{ $report->approver->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date d'approbation</span>
                <span class="value">{{ $report->approved_at?->format('d/m/Y H:i') }}</span>
            </div>
        @endif
    </div>
</div>
@endsection
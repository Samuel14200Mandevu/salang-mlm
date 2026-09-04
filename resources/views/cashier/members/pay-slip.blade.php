@extends('cashier.layouts.app')

@push('styles')
<style>
    .pay-slip-header {
        background: var(--primary-navy);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 6px 6px 0 0;
    }
    .pay-slip-header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
    }
    .pay-slip-header small {
        opacity: 0.8;
        font-weight: 400;
    }
    
    .pay-slip-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        overflow: hidden;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        background: var(--bg-secondary);
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-item .label {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .info-item .value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .info-item .value.text-success { color: #1F7B4D; }
    .info-item .value.text-warning { color: #A65A0E; }
    .info-item .value.text-info { color: #0A2A6C; }
    .info-item .value.text-primary { color: var(--primary-navy); }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 0.75rem;
        padding: 1rem 1.25rem;
    }
    .summary-item {
        text-align: center;
        padding: 0.75rem 0.5rem;
        border-radius: 4px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-light);
    }
    .summary-item .amount {
        font-size: 1.2rem;
        font-weight: 800;
    }
    .summary-item .label {
        font-size: 0.55rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        font-weight: 600;
        margin-top: 2px;
    }
    .summary-item.total {
        background: var(--primary-navy);
        color: white;
        border-color: var(--primary-navy);
    }
    .summary-item.total .label { color: rgba(255,255,255,0.7); }
    .summary-item.total .amount { color: white; }
    
    .summary-item .sponsor-color { color: #1F7B4D; }
    .summary-item .direct-color { color: #4F46E5; }
    .summary-item .indirect-color { color: #2563EB; }
    .summary-item .leadership-color { color: #A65A0E; }
    .summary-item .cash-color { color: #16a34a; }
    
    .badge-type {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.55rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-sponsor { background: rgba(31, 123, 77, 0.12); color: #1F7B4D; }
    .badge-direct { background: rgba(79, 70, 229, 0.12); color: #4F46E5; }
    .badge-indirect { background: rgba(37, 99, 235, 0.12); color: #2563EB; }
    .badge-leadership { background: rgba(166, 90, 14, 0.12); color: #A65A0E; }
    .badge-cash_pos { background: rgba(22, 163, 74, 0.12); color: #16a34a; }
    .badge-client { background: rgba(37, 99, 235, 0.08); color: #2563EB; }
    .badge-member { background: rgba(31, 123, 77, 0.08); color: #1F7B4D; }
    
    .btn-pdf {
        background: #b32a2a;
        color: white;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-pdf:hover {
        background: #8f2121;
        color: white;
    }
    
    .period-selector {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .period-selector select {
        padding: 0.3rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: white;
        font-size: 0.875rem;
    }
    .period-selector .btn {
        padding: 0.3rem 1rem;
        font-size: 0.75rem;
    }
    
    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 0.75rem 1.25rem;
        background: rgba(31, 123, 77, 0.03);
        border-top: 1px solid var(--border-light);
        font-size: 0.7rem;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .legend-dot.sponsor { background: #1F7B4D; }
    .legend-dot.direct { background: #4F46E5; }
    .legend-dot.indirect { background: #2563EB; }
    .legend-dot.leadership { background: #A65A0E; }
    .legend-dot.cash { background: #16a34a; }
    
    @media print {
        .no-print { display: none !important; }
        .pay-slip-header { background: #0F2B4F !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .summary-item.total { background: #0F2B4F !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-type { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .legend-dot { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    
    @media (max-width: 640px) {
        .info-grid { grid-template-columns: 1fr 1fr; gap: 0.5rem; padding: 0.75rem; }
        .summary-grid { grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; padding: 0.75rem; }
        .summary-item .amount { font-size: 1rem; }
        .pay-slip-header { padding: 0.75rem 1rem; }
        .pay-slip-header h2 { font-size: 1rem; }
    }
</style>
@endpush

@section('title', 'Fiche de paie - ' . $member->name)

@section('content')
<div class="space-y-4">
    
    {{-- En-tête avec actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <div>
            <h1 class="text-xl font-bold text-[var(--text-primary)]">Fiche de paie</h1>
            <p class="text-sm text-[var(--text-secondary)]">{{ $member->name }} • Période: {{ $period }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <form method="GET" action="{{ route('cashier.members.pay-slip', $member->id) }}" class="period-selector">
                <select name="period" onchange="this.form.submit()">
                    @foreach($periods as $p)
                        <option value="{{ $p }}" {{ $p == $period ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </form>
            
            {{-- FORMULAIRE POUR TÉLÉCHARGER LE PDF SANS OUVRIR DE NOUVELLE PAGE --}}
            <form action="{{ route('cashier.members.pay-slip-pdf', $member->id) }}" 
                  method="GET" 
                  style="display: inline-block;">
                <input type="hidden" name="period" value="{{ $period }}">
                <button type="submit" class="btn-pdf" style="border: none; cursor: pointer;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Télécharger PDF
                </button>
            </form>
            
            <a href="{{ route('cashier.members.show', $member->id) }}" class="btn btn-outline btn-sm">
                ← Retour
            </a>
        </div>
    </div>

    {{-- Fiche de paie --}}
    <div class="pay-slip-card" id="paySlip">
        
        {{-- Entête --}}
        <div class="pay-slip-header">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <div>
                    <h2>SALANG GROUP SARL</h2>
                    <small>FICHE DE PAIE DES COMMISSIONS</small>
                </div>
                <div class="text-right">
                    <div style="font-size: 1.1rem; font-weight: 700;">Période: {{ $period }}</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;">Date: {{ now()->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
        
        {{-- Infos membre --}}
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Nom complet</span>
                <span class="value">{{ $member->name }}</span>
            </div>
            <div class="info-item">
                <span class="label">Code membre</span>
                <span class="value">{{ $member->sponsor_id }}</span>
            </div>
            <div class="info-item">
                <span class="label">Grade</span>
                <span class="value text-info">{{ $member->rank ?? 'Distributeur' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Package</span>
                <span class="value">{{ $member->package?->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">PV Mensuel</span>
                <span class="value text-warning">{{ number_format($monthlyPv, 0) }} PV</span>
            </div>
            <div class="info-item">
                <span class="label">PV Réseau</span>
                <span class="value text-success">{{ number_format($teamPv, 0) }} PV</span>
            </div>
            <div class="info-item">
                <span class="label">Filleuls directs</span>
                <span class="value text-info">{{ $directSponsors }}</span>
            </div>
            <div class="info-item">
                <span class="label">Clients POS</span>
                <span class="value text-primary">{{ $posClients }}</span>
            </div>
            <div class="info-item">
                <span class="label">Parrain</span>
                <span class="value">{{ $member->parrain?->name ?? 'Aucun' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Téléphone</span>
                <span class="value">{{ $member->phone ?? 'N/A' }}</span>
            </div>
        </div>
        
        {{-- Résumé des commissions --}}
        <div class="summary-grid">
            <div class="summary-item">
                <div class="amount sponsor-color">${{ number_format($totals['sponsor'] ?? 0, 2) }}</div>
                <div class="label">Sponsor Bonus</div>
            </div>
            <div class="summary-item">
                <div class="amount direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</div>
                <div class="label">Direct Bonus</div>
            </div>
            <div class="summary-item">
                <div class="amount indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</div>
                <div class="label">Indirect Bonus</div>
            </div>
            <div class="summary-item">
                <div class="amount leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</div>
                <div class="label">Leadership Bonus</div>
            </div>
            <div class="summary-item">
                <div class="amount cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</div>
                <div class="label">CASH POS Bonus</div>
            </div>
            <div class="summary-item total">
                <div class="amount">${{ number_format($totalCommissions, 2) }}</div>
                <div class="label">TOTAL</div>
            </div>
        </div>
        
        {{-- Détail par filleul/client --}}
        @if($commissionDetails->count() > 0)
        <div style="padding: 0 1.25rem 1rem;">
            <h4 style="font-weight: 600; margin-bottom: 0.75rem; font-size: 0.875rem;">
                Détail par filleul / client POS
                <span style="font-weight: 400; font-size: 0.7rem; color: var(--text-secondary);">
                    ({{ $commissionDetails->count() }} personne{{ $commissionDetails->count() > 1 ? 's' : '' }})
                </span>
            </h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                    <thead>
                        <tr style="background: var(--bg-secondary);">
                            <th style="padding: 0.4rem 0.6rem; text-align: left; font-weight: 600;">Personne</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: center; font-weight: 600;">Type</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 600; color: #1F7B4D;">Sponsor</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 600; color: #4F46E5;">Direct</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 600; color: #2563EB;">Indirect</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 600; color: #A65A0E;">Leadership</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 600; color: #16a34a;">CASH POS</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 600;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissionDetails as $detail)
                            <tr style="border-bottom: 1px solid var(--border-light);">
                                <td style="padding: 0.4rem 0.6rem;">
                                    <strong>{{ $detail['user']?->name ?? 'N/A' }}</strong>
                                    <br><span style="font-size: 0.6rem; color: var(--text-secondary);">Code: {{ $detail['user']?->sponsor_id ?? 'N/A' }}</span>
                                </td>
                                <td style="padding: 0.4rem 0.6rem; text-align: center;">
                                    @if($detail['user_type'] == 'member')
                                        <span class="badge-type badge-member">Membre</span>
                                    @elseif($detail['user_type'] == 'client')
                                        <span class="badge-type badge-client">Client POS</span>
                                    @else
                                        <span class="badge-type" style="background: #e8eaee; color: #666;">Inconnu</span>
                                    @endif
                                </td>
                                <td style="padding: 0.4rem 0.6rem; text-align: right; color: #1F7B4D;">
                                    ${{ number_format($detail['sponsor'] ?? 0, 2) }}
                                </td>
                                <td style="padding: 0.4rem 0.6rem; text-align: right; color: #4F46E5;">
                                    ${{ number_format($detail['direct'] ?? 0, 2) }}
                                </td>
                                <td style="padding: 0.4rem 0.6rem; text-align: right; color: #2563EB;">
                                    ${{ number_format($detail['indirect'] ?? 0, 2) }}
                                </td>
                                <td style="padding: 0.4rem 0.6rem; text-align: right; color: #A65A0E;">
                                    ${{ number_format($detail['leadership'] ?? 0, 2) }}
                                </td>
                                <td style="padding: 0.4rem 0.6rem; text-align: right; color: #16a34a;">
                                    ${{ number_format($detail['cash_pos'] ?? 0, 2) }}
                                </td>
                                <td style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 700;">
                                    ${{ number_format($detail['total'] ?? 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--bg-secondary); font-weight: 700;">
                            <td style="padding: 0.5rem 0.6rem;">TOTAL GÉNÉRAL</td>
                            <td style="padding: 0.5rem 0.6rem; text-align: center;">—</td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; color: #1F7B4D;">${{ number_format($totals['sponsor'] ?? 0, 2) }}</td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; color: #4F46E5;">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; color: #2563EB;">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; color: #A65A0E;">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; color: #16a34a;">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; color: var(--primary-navy); font-size: 1.1rem;">
                                ${{ number_format($totalCommissions, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @else
        <div style="padding: 1.5rem; text-align: center; color: var(--text-secondary);">
            <p>Aucune commission payée pour la période <strong>{{ $period }}</strong>.</p>
        </div>
        @endif
        
        {{-- Pied de page --}}
        <div style="padding: 0.6rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; font-size: 0.65rem; color: var(--text-secondary); flex-wrap: wrap; gap: 0.5rem;">
            <div>Généré le {{ now()->format('d/m/Y H:i') }}</div>
            <div>Salang Group SARL</div>
        </div>
    </div>
    
</div>
@endsection
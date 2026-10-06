@extends('cashier.layouts.app')

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
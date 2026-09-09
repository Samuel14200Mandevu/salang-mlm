{{-- resources/views/admin/pv/commission-details.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
:root {
    --bg-page: #F4F5F7;
    --bg-card: #F8F9FA;
    --bg-hover: #EEF0F2;
    --bg-input: #F8F9FA;
    
    --text-primary: #1A1D23;
    --text-secondary: #5A626A;
    --text-muted: #8E959C;
    
    --border-color: #DDE0E3;
    --border-light: #E8EBEE;
    
    --radius: 6px;
    
    --direct-color: #4F46E5;
    --indirect-color: #2563EB;
    --leadership-color: #A65A0E;
    --cash-color: #16a34a;
}

* { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
body { background: var(--bg-page); color: var(--text-primary); }

.page-header {
    padding: 1.5rem 0 1rem 0;
    border-bottom: 1px solid var(--border-color);
}
.page-header .top-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.page-title { font-size: 1.25rem; font-weight: 600; letter-spacing: -0.01em; color: var(--text-primary); margin: 0; }
.page-subtitle { font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.125rem; }

.header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1.25rem;
}
.card-title {
    font-size: 0.813rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: var(--radius);
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    transition: background 0.15s ease;
}
.btn-outline { background: transparent; color: var(--text-primary); border-color: var(--border-color); }
.btn-outline:hover { background: var(--bg-hover); }
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
.btn .icon { width: 16px; height: 16px; flex-shrink: 0; }

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.688rem;
    font-weight: 500;
    border: 1px solid transparent;
}
.badge-direct { background: rgba(79, 70, 229, 0.10); color: var(--direct-color); }
.badge-indirect { background: rgba(37, 99, 235, 0.10); color: var(--indirect-color); }
.badge-leadership { background: rgba(166, 90, 14, 0.10); color: var(--leadership-color); }
.badge-cash { background: rgba(22, 163, 74, 0.10); color: var(--cash-color); }

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table th {
    padding: 0.5rem 0.75rem;
    text-align: left;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--border-color);
    background: var(--bg-input);
}
.table td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-primary);
}
.table tr:hover td { background: var(--bg-hover); }
.table .text-right { text-align: right; }
.table .text-center { text-align: center; }

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.summary-item {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 0.75rem 1rem;
    text-align: center;
}
.summary-item .amount { font-size: 1.25rem; font-weight: 700; }
.summary-item .label {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    margin-top: 0.125rem;
}
.summary-item.total {
    background: var(--text-primary);
    color: white;
    border-color: var(--text-primary);
}
.summary-item.total .label { color: rgba(255,255,255,0.7); }
.direct-color { color: var(--direct-color); }
.indirect-color { color: var(--indirect-color); }
.leadership-color { color: var(--leadership-color); }
.cash-color { color: var(--cash-color); }
.total-color { color: #fff; }

.flex { display: flex; }
.flex-wrap { flex-wrap: wrap; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mb-4 { margin-bottom: 1rem; }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.text-muted { color: var(--text-muted); }
.text-secondary { color: var(--text-secondary); }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }

@media (max-width: 640px) {
    .summary-grid { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
    .page-header .top-row { flex-direction: column; align-items: stretch; }
    .header-actions { justify-content: stretch; }
    .header-actions .btn { flex: 1; }
    .card { padding: 1rem; }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="top-row">
        <div>
            <h1 class="page-title">Détail des commissions</h1>
            <p class="page-subtitle">Période {{ $period }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.pv.commission-history.index') }}" class="btn btn-outline btn-sm">
                <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>
</div>

<div class="summary-grid">
    <div class="summary-item"><div class="amount direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</div><div class="label">Direct</div></div>
    <div class="summary-item"><div class="amount indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</div><div class="label">Indirect</div></div>
    <div class="summary-item"><div class="amount leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</div><div class="label">Leadership</div></div>
    <div class="summary-item"><div class="amount cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</div><div class="label">Cash POS</div></div>
    <div class="summary-item total"><div class="amount total-color">${{ number_format($totals['total'] ?? 0, 2) }}</div><div class="label">Total</div></div>
</div>

<div class="card">
    <div class="card-title">Liste des commissions - {{ $period }}</div>
    @if($commissions->count() > 0)
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Bénéficiaire</th>
                    <th>De</th>
                    <th class="text-right">Montant</th>
                    <th class="text-center">Taux</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissions as $commission)
                <tr>
                    <td><span class="badge badge-{{ $commission->type }}">{{ ucfirst($commission->type) }}</span></td>
                    <td>{{ $commission->user?->name ?? 'N/A' }}</td>
                    <td>{{ $commission->fromUser?->name ?? 'N/A' }}</td>
                    <td class="text-right font-medium">${{ number_format($commission->amount, 2) }}</td>
                    <td class="text-center">{{ $commission->percentage }}%</td>
                    <td class="text-sm text-secondary">{{ $commission->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center text-muted py-4">Aucune commission pour cette période.</div>
    @endif
</div>
@endsection
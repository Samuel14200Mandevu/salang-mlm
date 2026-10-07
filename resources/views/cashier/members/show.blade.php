@extends('cashier.layouts.app')

@section('title', 'Détails du membre')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] tracking-tight">
                Détails du membre
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                ID: {{ $member->id }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('cashier.members') }}" class="btn btn-outline btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Retour
            </a>
            <a href="{{ route('cashier.members.orders', $member->id) }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                </svg>
                Commandes
            </a>
            <a href="{{ route('cashier.members.pay-slip', $member->id) }}?period={{ date('Y-m') }}" class="btn btn-danger btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4v16h16V4zM8 12h8M12 8v8"/>
                </svg>
                Fiche de paie
            </a>
            <button type="button" id="btnPrintAdhesion" class="btn btn-success btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9V3h12v6M6 21h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    <path d="M18 9V3H6v6z"/>
                    <path d="M8 12v4h8v-4"/>
                </svg>
                Adhésion PDF
            </button>
        </div>
    </div>

    {{-- ALERTE AVEC LES IDENTIFIANTS --}}
    @if(session('success') && session('password') && session('email'))
        <div class="alert-credentials">
            <div class="alert-title">{{ session('success') }}</div>
            <div class="credentials-box">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <div class="cred-label">Email</div>
                        <div class="cred-value text-primary">{{ session('email') }}</div>
                    </div>
                    <div>
                        <div class="cred-label">Mot de passe</div>
                        <div class="cred-value text-danger">{{ session('password') }}</div>
                    </div>
                    <div>
                        <div class="cred-label">Code membre</div>
                        <div class="cred-value text-success">{{ session('member_code') ?? $member->sponsor_id }}</div>
                    </div>
                </div>
                <div class="alert-warning-text">
                    <strong>Important :</strong> Transmettez ces identifiants au nouveau membre.
                    Il pourra se connecter sur <a href="{{ route('login') }}" target="_blank">{{ route('login') }}</a>
                </div>
            </div>
        </div>
    @elseif(session('success'))
        <div class="alert-credentials" style="border-color: #86EFAC; background: #F0FDF4;">
            <div class="alert-title" style="color: #065F46;">{{ session('success') }}</div>
        </div>
    @endif

    {{-- INFORMATIONS DU MEMBRE --}}
    <div class="detail-card">
        <div class="flex flex-wrap items-start gap-4">
            <div class="avatar-lg">
                {{ strtoupper(substr($member->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-[var(--text-primary)] tracking-tight">{{ $member->name }}</h2>
                <div class="flex flex-wrap gap-2 mt-1">
                    <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $member->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    @php
                        $roleName = $member->getRoleNames()->first() ?? 'user';
                    @endphp
                    @if($roleName != 'user')
                        <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $roleName)) }}</span>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Email</p>
                            <p class="text-sm text-[var(--text-primary)]">{{ $member->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Téléphone</p>
                            <p class="text-sm text-[var(--text-primary)]">{{ $member->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Code</p>
                            <p class="text-sm font-mono text-[var(--navy)]">{{ $member->sponsor_id ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Grade</p>
                            <p class="text-sm">
                                @php
                                    $level = (int) ($member->rank_level ?? 0);
                                @endphp
                                <span class="{{ \App\Support\MlmRank::badgeClass($level) }}">
                                    {{ \App\Support\MlmRank::label($level) }}
                                </span>
                                <span class="text-xs text-[var(--text-tertiary)] font-normal ml-1">(Niv. {{ $level }})</span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">PV</p>
                            <p class="text-sm font-mono text-[var(--navy)]">{{ number_format((float) ($member->pv_balance ?? 0)) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--text-tertiary)] font-medium uppercase tracking-wide">Parrain</p>
                            <p class="text-sm font-medium text-[var(--navy)]">
                                @if($member->parrain)
                                    {{ $member->parrain->name }}
                                    <span class="text-xs text-[var(--text-secondary)] font-normal">
                                        (Code: {{ $member->parrain->sponsor_id ?? 'N/A' }})
                                    </span>
                                @else
                                    <span class="text-[var(--text-tertiary)]">Aucun parrain</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION PV DU MEMBRE --}}
    <div class="detail-card">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-[var(--text-primary)] text-sm">Points de Volume (PV)</h3>
                <p class="text-xs text-[var(--text-secondary)] mt-0.5">
                    PV Personnel et portefeuille de distribution (ventes POS)
                </p>
            </div>
            <div class="pv-actions">
                @if(($memberPv['available_pv'] ?? 0) > 0)
                    <a href="{{ route('cashier.pv.distribute', ['member_id' => $member->id]) }}" class="btn btn-primary btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Distribuer PV
                    </a>
                @endif
                <a href="{{ route('cashier.pv.dashboard', ['member_id' => $member->id]) }}" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                    Consulter les PV
                </a>
            </div>
        </div>

        <div class="pv-summary">
            <div class="pv-item">
                <div class="number">{{ number_format($memberPv['cumulative_pv'] ?? 0) }}</div>
                <span class="label">PV Personnel </span>
            </div>
            <div class="pv-item pv-item--available">
                <div class="number">{{ number_format($memberPv['available_pv'] ?? 0) }}</div>
                <span class="label">PV Disponibles</span>
            </div>
            <div class="pv-item pv-item--allocated">
                <div class="number">{{ number_format($memberPv['allocated_pv'] ?? 0) }}</div>
                <span class="label">PV Alloués</span>
            </div>
            <div class="pv-item pv-item--pending">
                <div class="number">{{ number_format($memberPv['pending_pv'] ?? 0) }}</div>
                <span class="label">PV en Attente</span>
            </div>
        </div>
        @if(($memberPv['wallet_total_pv'] ?? 0) > 0)
            <p class="text-xs text-[var(--text-tertiary)] mt-2">
                Crédits portefeuille (ventes à distribuer)&nbsp;:
                <span class="font-semibold text-[var(--text-primary)]">{{ number_format($memberPv['wallet_total_pv']) }} PV</span>
            </p>
        @endif

    </div>

    {{-- LISTE DES FILLEULS --}}
    <div class="detail-card p-3 sm:p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm">
                Filleuls
                <span class="text-xs text-[var(--text-secondary)] font-normal">({{ $downlines->total() ?? 0 }})</span>
            </h3>
            <div class="flex flex-wrap gap-2 text-[10px] sm:text-xs">
                <span class="badge badge-success text-[8px]">Actif</span>
                <span class="badge badge-danger text-[8px]">Inactif</span>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th class="hidden sm:table-cell">Email</th>
                        <th>Code</th>
                        <th class="hidden md:table-cell">Grade</th>
                        <th class="hidden lg:table-cell">PV</th>
                        <th>Statut</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="downlinesTable">
                    @forelse($downlines ?? [] as $downline)
                        @php
                            $level = (int) ($downline->rank_level ?? 0);
                        @endphp
                        <tr class="filleul-row"
                            data-name="{{ strtolower($downline->name) }}"
                            data-email="{{ strtolower($downline->email) }}"
                            data-level="{{ $level }}"
                            data-status="{{ $downline->is_active ? 1 : 0 }}">
                            <td class="font-mono text-xs text-[var(--text-secondary)]">{{ $downline->id }}</td>
                            <td>
                                <div class="font-medium text-sm text-[var(--text-primary)]">{{ $downline->name }}</div>
                                <div class="text-xs text-[var(--text-tertiary)]">Tél: {{ $downline->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $downline->email }}
                            </td>
                            <td>
                                <span class="font-mono text-xs text-[var(--navy)]">{{ $downline->sponsor_id ?? 'N/A' }}</span>
                            </td>
                            <td class="hidden md:table-cell">
                                <span class="{{ \App\Support\MlmRank::badgeClass($level) }}">
                                    {{ \App\Support\MlmRank::label($level) }}
                                </span>
                                <span class="text-xs text-[var(--text-tertiary)]">(Niv. {{ $level }})</span>
                            </td>
                            <td class="hidden lg:table-cell text-sm font-medium text-[var(--ui-stat-success)]">
                                {{ number_format($downline->pv_balance ?? 0) }}
                            </td>
                            <td>
                                <span class="badge {{ $downline->is_active ? 'badge-success' : 'badge-danger' }} text-[10px]">
                                    {{ $downline->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('cashier.members.show', $downline->id) }}"
                                   class="btn btn-primary btn-sm" title="Voir">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-[var(--text-tertiary)]">
                                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 010 7.75"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucun filleul</p>
                                <p class="text-sm text-[var(--text-tertiary)] mt-1">Ce membre n'a pas encore de filleuls</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($downlines) && $downlines->hasPages())
            <div class="mt-3 sm:mt-4">
                <x-salang-pagination :paginator="$downlines" />
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
window.filterDownlines = function filterDownlines() {
    const search = document.getElementById('downlineSearch').value.trim().toLowerCase();
    const level = document.getElementById('downlineLevelFilter').value;
    const status = document.getElementById('downlineStatusFilter').value;
    const rows = document.querySelectorAll('#downlinesTable tr');

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const rowLevel = row.dataset.level || '0';
        const rowStatus = row.dataset.status || '1';

        let show = true;

        if (search && !name.includes(search) && !email.includes(search)) {
            show = false;
        }

        if (level !== '' && parseInt(rowLevel) !== parseInt(level)) {
            show = false;
        }

        if (status !== '' && parseInt(rowStatus) !== parseInt(status)) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

window.resetDownlineFilters = function resetDownlineFilters() {
    document.getElementById('downlineSearch').value = '';
    document.getElementById('downlineLevelFilter').value = '';
    document.getElementById('downlineStatusFilter').value = '';
    filterDownlines();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('downlineSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterDownlines);
    }
    const levelFilter = document.getElementById('downlineLevelFilter');
    if (levelFilter) {
        levelFilter.addEventListener('change', filterDownlines);
    }
    const statusFilter = document.getElementById('downlineStatusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterDownlines);
    }
});

// Impression du formulaire d'adhésion
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnPrintAdhesion');
    
    if (btn) {
        btn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            
            this.disabled = true;
            this.innerHTML = `
                <span class="spinner"></span>
                Téléchargement...
            `;

            const url = '{{ route("cashier.members.adhesion-pdf", $member->id) }}';
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP: ' + response.status);
                    }
                    return response.blob();
                })
                .then(blob => {
                    if (blob.size === 0) {
                        throw new Error('Le fichier PDF est vide');
                    }
                    
                    const pdfUrl = URL.createObjectURL(blob);
                    
                    const link = document.createElement('a');
                    link.href = pdfUrl;
                    link.download = 'formulaire_adhesion_{{ $member->sponsor_id }}.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    const printWindow = window.open(pdfUrl, '_blank');
                    if (printWindow) {
                        printWindow.onload = function() {
                            setTimeout(() => {
                                printWindow.print();
                            }, 300);
                        };
                    }
                    
                    setTimeout(() => URL.revokeObjectURL(pdfUrl), 5000);
                    
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du téléchargement. Veuillez réessayer.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });
    }
});
</script>
@endpush
@endsection
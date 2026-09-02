{{-- resources/views/cashier/customers.blade.php --}}
@extends('cashier.layouts.app')

@push('styles')
<style>
    :root {
        --primary-navy: #0F2B4F;
        --primary-navy-dark: #091E3B;
        --primary-navy-light: #1A3F6A;
        --bg-base: #F5F6F8;
        --bg-card: #FFFFFF;
        --bg-secondary: #EEF0F3;
        --bg-hover: #E8EAEE;
        --text-primary: #1A1A1E;
        --text-secondary: #4A4A52;
        --text-tertiary: #7A7A82;
        --border-color: #DCDEE3;
        --border-light: #E8EAEE;
        --success: #1F7B4D;
        --danger: #B32A2A;
        --warning: #A65A0E;
        --info: #0A2A6C;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.875rem 1rem;
        transition: border-color 0.15s ease;
    }

    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-card .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
    }

    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-total { background: rgba(15, 43, 79, 0.10); color: var(--primary-navy); }
    .stat-icon-active { background: rgba(34, 197, 94, 0.10); color: #1F7B4D; }
    .stat-icon-sponsored { background: rgba(139, 92, 246, 0.10); color: #8b5cf6; }
    .stat-icon-sponsors { background: rgba(245, 158, 11, 0.10); color: #A65A0E; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.813rem;
        transition: background 0.15s ease, border-color 0.15s ease;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-navy);
        color: white;
        border-color: var(--primary-navy);
    }
    .btn-primary:hover {
        background: var(--primary-navy-dark);
        border-color: var(--primary-navy-dark);
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border-color: var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-hover);
        border-color: var(--border-color);
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-md {
        padding: 0.5rem 1.25rem;
        font-size: 0.813rem;
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.25rem;
        transition: border-color 0.15s ease;
    }

    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .table thead th {
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.688rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        border-bottom: 2px solid var(--border-color);
    }

    .table tbody td {
        padding: 0.5rem 0.75rem;
        color: var(--text-primary);
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }

    .table tbody tr:hover td {
        background: var(--bg-hover);
    }

    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.625rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .badge-success {
        background: #E6F4EC;
        color: #1F7B4D;
        border-color: #B8DFCC;
    }
    .badge-danger {
        background: #FDE8E8;
        color: #B32A2A;
        border-color: #F5C8C8;
    }

    .input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-primary);
        transition: border-color 0.15s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary-navy);
    }

    /* ===== RECHERCHE ===== */
    .header-search .search-wrapper {
        position: relative;
    }

    .header-search .search-wrapper .search-input {
        padding: 0.375rem 0.75rem 0.375rem 2.25rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 0.813rem;
        width: 220px;
        transition: border-color 0.15s ease, width 0.2s ease;
        outline: none;
    }

    .header-search .search-wrapper .search-input:focus {
        border-color: var(--primary-navy);
        width: 280px;
    }

    .header-search .search-wrapper .search-input::placeholder {
        color: var(--text-tertiary);
    }

    /* ===== MODAL ===== */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: 10px;
        padding: 1.5rem;
        max-width: 500px;
        width: 90%;
        border: 1px solid var(--border-color);
        transform: scale(0.95);
        transition: transform 0.25s ease;
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .modal-actions .btn {
        flex: 1;
        justify-content: center;
    }

    .flex-1 { flex: 1; }

    @media (max-width: 640px) {
        .header-search .search-wrapper .search-input {
            width: 100%;
        }
        .header-search .search-wrapper .search-input:focus {
            width: 100%;
        }
        .stat-card { padding: 0.625rem; }
        .stat-card .stat-value { font-size: 1.25rem; }
        .stat-icon { width: 2rem; height: 2rem; }
        .stat-icon svg { width: 1.25rem; height: 1.25rem; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .card { padding: 0.875rem; }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .modal-box { padding: 1rem; }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .table thead th, .table tbody td {
            padding: 0.25rem 0.375rem;
            font-size: 0.6rem;
        }
    }
</style>
@endpush

@section('title', 'Clients POS')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                Clients POS
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $customers->total() ?? 0 }} clients
                @if(request('search'))
                    <span class="text-xs text-[var(--text-tertiary)] ml-2">
                        · Résultats pour "{{ request('search') }}"
                    </span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="header-search">
                <div class="search-wrapper">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Rechercher un client"
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </div>
            <button onclick="openNewCustomerModal()" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau client
            </button>
        </div>
    </div>

    {{-- STATISTIQUES --}}
    @php
        $totalCustomers = $customers->total() ?? 0;
        $activeCustomers = $customers->where('is_active', true)->count();
        $sponsoredCustomers = $customers->whereNotNull('parrain_id')->count();
        $uniqueSponsors = $customers->whereNotNull('parrain_id')->unique('parrain_id')->count();
    @endphp

    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total clients</p>
                    <p class="stat-value text-[var(--primary-navy)]">{{ $totalCustomers }}</p>
                </div>
                <div class="stat-icon stat-icon-total">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Actifs</p>
                    <p class="stat-value text-[#1F7B4D]">{{ $activeCustomers }}</p>
                </div>
                <div class="stat-icon stat-icon-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Avec parrain</p>
                    <p class="stat-value text-[#8b5cf6]">{{ $sponsoredCustomers }}</p>
                </div>
                <div class="stat-icon stat-icon-sponsored">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Parrains uniques</p>
                    <p class="stat-value text-[#A65A0E]">{{ $uniqueSponsors }}</p>
                </div>
                <div class="stat-icon stat-icon-sponsors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- LISTE --}}
    <div class="card p-3 sm:p-4">
        <div class="table-wrap" id="tableContainer">
            <table class="table table-striped" id="customersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th class="hidden sm:table-cell">Téléphone</th>
                        <th class="hidden md:table-cell">Parrain</th>
                        <th class="hidden lg:table-cell">Code client</th>
                        <th class="hidden lg:table-cell">Inscrit</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($customers ?? [] as $customer)
                        <tr>
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $customer->id }}</td>
                            <td>
                                <div class="font-medium text-sm">{{ $customer->name }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $customer->email }}</div>
                                @if($customer->address)
                                    <div class="text-[10px] text-[var(--text-tertiary)]">{{ $customer->address }}{{ $customer->city ? ', ' . $customer->city : '' }}</div>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $customer->phone ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell">
                                @if($customer->parrain)
                                    <span class="text-sm text-[var(--primary-navy)]">{{ $customer->parrain->name }}</span>
                                    <div class="text-xs text-[var(--text-secondary)]">Code: {{ $customer->parrain->sponsor_id }}</div>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">Aucun</span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell font-mono text-xs text-[var(--text-secondary)]">
                                {{ $customer->sponsor_id ?? 'N/A' }}
                            </td>
                            <td class="hidden lg:table-cell text-xs text-[var(--text-secondary)]">
                                {{ $customer->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $customer->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-base font-medium text-[var(--text-primary)]">Aucun client POS</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les clients apparaîtront ici après leur première vente au guichet</p>
                                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Faire une vente
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($customers) && $customers->hasPages())
            <div class="mt-3 sm:mt-4" id="paginationContainer">
                {{ $customers->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL NOUVEAU CLIENT --}}
<div id="customerModal" class="modal-overlay" onclick="if(event.target === this) closeModal('customerModal')">
    <div class="modal-box">
        <div class="flex justify-between items-center mb-3">
            <h3 class="modal-title">
                <svg class="inline-block w-5 h-5 mr-2 text-[var(--primary-navy)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Nouveau client POS
            </h3>
            <button onclick="closeModal('customerModal')" class="text-[var(--text-tertiary)] hover:text-[var(--text-primary)] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="customerFormModal">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet <span class="text-[#B32A2A]">*</span></label>
                    <input type="text" id="newNameModal" class="input" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Email <span class="text-[#B32A2A]">*</span></label>
                    <input type="email" id="newEmailModal" class="input" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                    <input type="text" id="newPhoneModal" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                    <input type="text" id="newAddressModal" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Ville</label>
                    <input type="text" id="newCityModal" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Pays</label>
                    <input type="text" id="newCountryModal" class="input">
                </div>
                <div class="text-xs text-[var(--text-secondary)] p-2 rounded-lg border border-[var(--border-color)]">
                    Le client pourra être parrainé lors de sa première vente
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeModal('customerModal')" class="btn btn-outline">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            searchTimeout = setTimeout(() => {
                const url = new URL(window.location.href);
                if (query) {
                    url.searchParams.set('search', query);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }, 500);
        });
    }
});

function openNewCustomerModal() {
    const modal = document.getElementById('customerModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('customerFormModal').reset();
}

document.getElementById('customerFormModal').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Création...
    `;

    const data = {
        name: document.getElementById('newNameModal').value,
        email: document.getElementById('newEmailModal').value,
        phone: document.getElementById('newPhoneModal').value,
        address: document.getElementById('newAddressModal').value,
        city: document.getElementById('newCityModal')?.value,
        country: document.getElementById('newCountryModal')?.value,
    };

    fetch('{{ route('cashier.customers.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showToast(' Client créé avec succès !', 'success');
            closeModal('customerModal');
            document.getElementById('customerFormModal').reset();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            window.showToast(' Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        window.showToast(' Erreur: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Créer
        `;
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('customerModal');
    }
});
</script>
@endpush
@endsection
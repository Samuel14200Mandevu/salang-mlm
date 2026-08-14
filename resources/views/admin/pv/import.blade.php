<?php
// resources/views/admin/pv/import.blade.php
?>

@extends('admin.layouts.app')

@push('styles')
<style>
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        display: block;
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    .form-group label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    .form-group input[readonly] {
        background: var(--bg-secondary);
        cursor: not-allowed;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.813rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.4);
    }
    .btn-secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }
    .btn-secondary:hover {
        background: var(--bg-hover);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    .btn-danger {
        background: var(--gradient-danger);
        color: white;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
    }
    .form-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .form-input:focus {
        border-color: var(--primary-500);
        outline: none;
        box-shadow: 0 0 0 3px var(--border-focus);
    }
    .grid-cols-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .grid-cols-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1.5rem;
    }
    .mt-4 { margin-top: 1rem; }
    .mb-4 { margin-bottom: 1rem; }
    .space-y-6 > * + * { margin-top: 1.5rem; }
    .flex { display: flex; }
    .gap-4 { gap: 1rem; }
    .flex-1 { flex: 1; }
    .items-end { align-items: flex-end; }
    .items-center { align-items: center; }
    .hidden { display: none; }
    .inline { display: inline; }
    .mr-2 { margin-right: 0.5rem; }
    .w-4 { width: 1rem; }
    .h-4 { height: 1rem; }
    .text-sm { font-size: 0.875rem; }
    .text-lg { font-size: 1.125rem; }
    .text-2xl { font-size: 1.5rem; }
    .font-bold { font-weight: 700; }
    .font-semibold { font-weight: 600; }
    .text-primary-500 { color: var(--primary-500); }
    .text-green-500 { color: #22c55e; }
    .text-blue-500 { color: #3b82f6; }
    .text-red-500 { color: #ef4444; }
    .text-secondary { color: var(--text-secondary); }
    .bg-green-500\/10 { background: rgba(34, 197, 94, 0.1); }
    .bg-blue-500\/10 { background: rgba(59, 130, 246, 0.1); }
    .bg-red-500\/10 { background: rgba(239, 68, 68, 0.1); }
    .border { border: 1px solid var(--border-color); }
    .border-green-500\/20 { border-color: rgba(34, 197, 94, 0.2); }
    .border-blue-500\/20 { border-color: rgba(59, 130, 246, 0.2); }
    .border-red-500\/20 { border-color: rgba(239, 68, 68, 0.2); }
    .border-l-4 { border-left-width: 4px; }
    .border-l-primary-500 { border-left-color: var(--primary-500); }
    .border-l-green-500 { border-left-color: #22c55e; }
    .border-l-blue-500 { border-left-color: #3b82f6; }
    .rounded-lg { border-radius: var(--radius-lg); }
    .p-4 { padding: 1rem; }
    .p-6 { padding: 1.5rem; }
    .whitespace-pre-line { white-space: pre-line; }
    .bg-secondary { background: var(--bg-secondary); }
    .hover-underline:hover { text-decoration: underline; }
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    
    .user-info-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .user-info-card .avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        background: var(--gradient-primary);
        color: white;
        flex-shrink: 0;
    }
    .pv-stat {
        text-align: center;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
    }
    .pv-stat .value {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .pv-stat .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Toast notification */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-width: 400px;
        width: 100%;
    }
    .toast {
        padding: 1rem 1.25rem;
        border-radius: var(--radius-md);
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        animation: slideInRight 0.4s ease;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .toast-success { background: #22c55e; }
    .toast-error { background: #ef4444; }
    .toast-warning { background: #f59e0b; }
    .toast-info { background: #3b82f6; }
    .toast .toast-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }
    
    /* Custom Confirm Dialog */
    .confirm-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .confirm-overlay.active {
        display: flex;
    }
    .confirm-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 480px;
        width: 95%;
        animation: modalIn 0.3s ease;
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .confirm-box .icon {
        font-size: 3rem;
        text-align: center;
        margin-bottom: 0.5rem;
    }
    .confirm-box h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
        margin-bottom: 0.5rem;
    }
    .confirm-box p {
        color: var(--text-secondary);
        font-size: 0.938rem;
        text-align: center;
        margin-bottom: 0.25rem;
    }
    .confirm-box .warning-text {
        color: #ef4444;
        font-weight: 600;
        font-size: 0.875rem;
        margin: 0.75rem 0 1.5rem 0;
        padding: 0.75rem;
        background: rgba(239, 68, 68, 0.08);
        border-radius: var(--radius-sm);
        border-left: 3px solid #ef4444;
    }
    .confirm-box .btn-group {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .confirm-box .btn-group .btn {
        min-width: 120px;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    
    .help-text {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    
    @media (max-width: 640px) {
        .grid-cols-2, .grid-cols-3 {
            grid-template-columns: 1fr;
        }
        .flex.gap-4 {
            flex-direction: column;
        }
        .btn {
            width: 100%;
        }
        .p-6 { padding: 1rem; }
        .user-info-card .flex {
            flex-direction: column;
            text-align: center;
        }
        .user-info-card .avatar {
            margin: 0 auto;
        }
        .toast-container {
            top: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">
        Import des PV Mensuels
    </h1>

    <div id="toastContainer" class="toast-container"></div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 whitespace-pre-line">
            {{ session('success') }}
            @if(session('user_id'))
                <br>
                <a href="{{ route('admin.pv.show', session('user_id')) }}" class="text-primary-500 hover-underline font-semibold">
                    Voir l'historique des PV de l'utilisateur
                </a>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Section -->
    <div class="card">

        <div id="userResult" class="mt-4 {{ $user ? '' : 'hidden' }}">
            <div id="userInfo">
                @if($user)
                <div class="user-info-card">
                    <div class="flex items-center gap-4">
                        <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-bold text-[var(--text-primary)]">{{ $user->name }}</h3><br>
                                <span class="badge badge-purple">{{ $user->rank ?? 'Distributeur' }} (Niv. {{ $user->rank_level ?? 1 }})</span>
                                <span class="badge badge-info">Code: {{ $user->sponsor_id ?? 'N/A' }}</span>
                            </div><br>
                            @if($user->parrain)
                                <p class="text-sm text-secondary">
                                    Parrain: <span class="text-primary-500">{{ $user->parrain->name }}</span> 
                                    ({{ $user->parrain->sponsor_id }})
                                </p>
                            @else
                                <p class="text-sm text-secondary">Aucun parrain</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid-cols-3 mt-4">
                        <div class="pv-stat border-l-4 border-l-primary-500">
                            <div class="value text-primary-500">{{ number_format($user->pv_balance ?? 0, 1, ',', ' ') }}</div>
                            <div class="label">PV Total</div>
                        </div>
                        <div class="pv-stat border-l-4 border-l-green-500">
                            <div class="value text-green-500">{{ number_format($user->monthly_pv ?? 0, 1, ',', ' ') }}</div>
                            <div class="label">PV Mensuel</div>
                        </div>
                        <div class="pv-stat border-l-4 border-l-blue-500">
                            <div class="value text-blue-500">{{ number_format($user->team_pv ?? 0, 1, ',', ' ') }}</div>
                            <div class="label">PV Equipe</div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                        <p class="text-sm text-secondary">
                            <span class="font-semibold text-[var(--text-primary)]">Membre sélectionne</span>
                            — Vous pouvez maintenant saisir les PV pour ce membre.
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- PV Form Section -->
    <div id="pvFormSection" class="{{ $user ? '' : 'hidden' }}">
        <div class="card">
            <form id="addMonthlyForm" method="POST" action="{{ $user ? route('admin.pv.add-monthly', $user->id) : '' }}">
                @csrf
                
                <div class="grid-cols-3">
                    <div class="form-group">
                        <label for="monthly_period">Periode <span class="required">*</span></label>
                        <input type="month" name="period" id="monthly_period" value="{{ date('Y-m') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="monthly_amount">Montant PV <span class="required">*</span></label>
                        <input type="number" name="amount" id="monthly_amount" step="0.1" required min="0.1">
                        <small class="help-text">Saisir le montant en PV (ex: 100.5)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="monthly_type">Type de PV <span class="required">*</span></label>
                        <select name="type" id="monthly_type">
                            <option value="personal">PV Personnel</option>
                            <option value="monthly">PV Mensuel</option>
                            <option value="team">PV Equipe</option>
                        </select>
                        <small class="help-text">Type de PV a ajouter</small>
                    </div>
                </div>
                
                <div class="flex gap-4 mt-4 flex-wrap">
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter les PV
                    </button>
                    <button type="button" onclick="resetForm()" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Confirm Dialog -->
    <div id="confirmDialog" class="confirm-overlay">
        <div class="confirm-box">
            <h3>Confirmation</h3>
            <p id="confirmMessage">Voulez-vous continuer ?</p>
            <div class="warning-text" id="confirmWarning"></div>
            <div class="btn-group">
                <button onclick="closeConfirm()" class="btn btn-secondary">Annuler</button>
                <button onclick="confirmAction()" class="btn btn-success" id="confirmBtn">Confirmer</button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// TOAST NOTIFICATION SYSTEM
// ============================================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    
    let icon = '';
    if (type === 'success') icon = '✓';
    else if (type === 'error') icon = '✗';
    else if (type === 'warning') icon = '⚠';
    else icon = 'ℹ';
    
    toast.innerHTML = '<span class="toast-icon">' + icon + '</span> ' + message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.4s ease forwards';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

// ============================================================
// CUSTOM CONFIRM DIALOG
// ============================================================
let confirmCallback = null;

function showConfirm(message, warning = '', callback) {
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmWarning').textContent = warning;
    document.getElementById('confirmWarning').style.display = warning ? 'block' : 'none';
    document.getElementById('confirmDialog').classList.add('active');
    confirmCallback = callback;
}

function closeConfirm() {
    document.getElementById('confirmDialog').classList.remove('active');
    confirmCallback = null;
}

function confirmAction() {
    if (typeof confirmCallback === 'function') {
        confirmCallback();
    }
    closeConfirm();
}

// Fermer en cliquant à l'extérieur
document.getElementById('confirmDialog').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});

// Raccourci ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeConfirm();
});

// ============================================================
// SEARCH USER
// ============================================================
let selectedUserId = {{ $user->id ?? 'null' }};

document.addEventListener('DOMContentLoaded', function() {
    @if($user)
        document.getElementById('addMonthlyForm').action = '/admin/pv/' + {{ $user->id }} + '/add-monthly';
        document.getElementById('pvFormSection').classList.remove('hidden');
        document.getElementById('monthly_amount').focus();
    @endif
});

function searchUser() {
    const query = document.getElementById('searchUser').value.trim();
    
    if (!query || query.length < 2) {
        showToast('Veuillez saisir au moins 2 caracteres (nom, email ou code sponsor)', 'warning');
        return;
    }
    
    document.getElementById('userInfo').innerHTML = `
        <div class="user-info-card">
            <div class="flex items-center gap-4">
                <div class="avatar">?</div>
                <div>
                    <p class="text-secondary">Recherche en cours...</p>
                </div>
            </div>
        </div>
    `;
    document.getElementById('userResult').classList.remove('hidden');
    document.getElementById('pvFormSection').classList.add('hidden');
    
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    
    fetch('{{ url("admin/pv/search-user") }}?search=' + encodeURIComponent(query), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.error || 'Erreur ' + response.status);
            }).catch(() => {
                throw new Error('Erreur ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            document.getElementById('userInfo').innerHTML = `
                <div class="user-info-card">
                    <div class="flex items-center gap-4">
                        <div class="avatar" style="background:#ef4444;">!</div>
                        <div>
                            <p class="text-red-500 font-semibold">${data.error}</p>
                            <p class="text-sm text-secondary">Veuillez essayer avec d'autres criteres.</p>
                        </div>
                    </div>
                </div>
            `;
            return;
        }
        
        selectedUserId = data.id;
        document.getElementById('addMonthlyForm').action = '/admin/pv/' + data.id + '/add-monthly';
        
        document.getElementById('userInfo').innerHTML = `
            <div class="user-info-card">
                <div class="flex items-center gap-4">
                    <div class="avatar">${data.name.charAt(0).toUpperCase()}</div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-lg font-bold text-[var(--text-primary)]">${data.name}</h3>
                            <span class="badge badge-purple">${data.rank_name} (Niv. ${data.rank_level})</span>
                            <span class="badge badge-info">Code: ${data.sponsor_id}</span>
                        </div>
                        ${data.parrain_name ? `<p class="text-sm text-secondary">Parrain: <span class="text-primary-500">${data.parrain_name}</span> (${data.parrain_sponsor_id})</p>` : '<p class="text-sm text-secondary">Aucun parrain</p>'}
                    </div>
                </div>
                
                <div class="grid-cols-3 mt-4">
                    <div class="pv-stat border-l-4 border-l-primary-500">
                        <div class="value text-primary-500">${data.pv_balance}</div>
                        <div class="label">PV Total</div>
                    </div>
                    <div class="pv-stat border-l-4 border-l-green-500">
                        <div class="value text-green-500">${data.monthly_pv}</div>
                        <div class="label">PV Mensuel</div>
                    </div>
                    <div class="pv-stat border-l-4 border-l-blue-500">
                        <div class="value text-blue-500">${data.team_pv}</div>
                        <div class="label">PV Equipe</div>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                    <p class="text-sm text-secondary">
                        <span class="font-semibold text-[var(--text-primary)]">Membre selectionne</span>
                        — Vous pouvez maintenant saisir les PV pour ce membre.
                    </p>
                </div>
            </div>
        `;
        
        document.getElementById('pvFormSection').classList.remove('hidden');
        document.getElementById('monthly_amount').focus();
    })
    .catch(err => {
        console.error('Erreur:', err);
        document.getElementById('userInfo').innerHTML = `
            <div class="user-info-card">
                <div class="flex items-center gap-4">
                    <div class="avatar" style="background:#ef4444;">!</div>
                    <div>
                        <p class="text-red-500 font-semibold">Erreur: ${err.message}</p>
                        <p class="text-sm text-secondary">Verifiez que le serveur est accessible.</p>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('pvFormSection').classList.add('hidden');
    });
}

function resetForm() {
    document.getElementById('userResult').classList.add('hidden');
    document.getElementById('pvFormSection').classList.add('hidden');
    document.getElementById('searchUser').value = '';
    document.getElementById('searchUser').focus();
    selectedUserId = null;
}

// ============================================================
// FORM SUBMISSION WITH CONFIRMATION
// ============================================================
document.getElementById('addMonthlyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const amount = document.getElementById('monthly_amount').value;
    const period = document.getElementById('monthly_period').value;
    const typeSelect = document.getElementById('monthly_type');
    const type = typeSelect.options[typeSelect.selectedIndex].text;
    
    if (!amount || parseFloat(amount) <= 0) {
        showToast('Veuillez saisir un montant PV valide (superieur a 0)', 'warning');
        return;
    }
    
    showConfirm(
        'Ajouter ' + amount + ' PV (' + type + ') pour la periode ' + period + ' ?',
        'Cette action est reversible via l\'historique.',
        function() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = 'Ajout en cours...';
            document.getElementById('addMonthlyForm').submit();
        }
    );
});

// ============================================================
// KEYBOARD SHORTCUTS
// ============================================================
document.getElementById('searchUser').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchUser();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        resetForm();
    }
});
</script>
@endsection
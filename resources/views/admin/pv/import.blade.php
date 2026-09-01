{{-- resources/views/admin/pv/import.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --bg-page: #F4F5F7;
    --bg-card: #FFFFFF;
    --bg-input: #F8F9FA;
    --bg-hover: #EEF0F2;
    --text-primary: #1A1D23;
    --text-secondary: #5A626A;
    --text-muted: #8E959C;
    --border-color: #DDE0E3;
    --border-focus: rgba(26, 29, 35, 0.12);
    --primary: #1A1D23;
    --primary-hover: #2D333B;
    --success: #1C7E4A;
    --success-hover: #14633A;
    --danger: #B91C1C;
    --radius: 8px;
    --radius-lg: 12px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
}

* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

body {
    background: var(--bg-page);
    color: var(--text-primary);
}

.page-header {
    padding: 2rem 0 1.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    color: var(--text-primary);
}

.page-subtitle {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.card-title {
    font-size: 0.813rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
}

.form-label .required {
    color: var(--danger);
    margin-left: 0.125rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-control:focus {
    border-color: var(--text-primary);
    outline: none;
    box-shadow: 0 0 0 3px var(--border-focus);
}

.form-control[readonly] {
    background: var(--bg-hover);
    cursor: default;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1.5rem;
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
    transition: background 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.btn-primary:hover {
    background: var(--primary-hover);
    border-color: var(--primary-hover);
}

.btn-success {
    background: var(--success);
    color: white;
    border-color: var(--success);
}

.btn-success:hover {
    background: var(--success-hover);
    border-color: var(--success-hover);
}

.btn-secondary {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}

.btn-outline:hover {
    background: var(--bg-hover);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.btn .icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.items-start { align-items: flex-start; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.gap-6 { gap: 1.5rem; }
.flex-1 { flex: 1; }
.flex-shrink-0 { flex-shrink: 0; }

.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 0.75rem; }
.mt-4 { margin-top: 1rem; }
.mt-6 { margin-top: 1.5rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-4 { margin-bottom: 1rem; }

.hidden { display: none !important; }
.w-full { width: 100%; }
.max-w-md { max-width: 28rem; }

.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.text-muted { color: var(--text-muted); }
.text-secondary { color: var(--text-secondary); }
.text-success { color: var(--success); }
.text-danger { color: var(--danger); }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }

.border-t { border-top: 1px solid var(--border-color); }
.rounded { border-radius: var(--radius); }

.alert {
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    border: 1px solid transparent;
}

.alert-success {
    background: #ECFDF3;
    border-color: #A6F4C5;
    color: #067647;
}

.alert-error {
    background: #FEF3F2;
    border-color: #FECDCA;
    color: #B42318;
}

.alert .link {
    color: var(--text-primary);
    text-decoration: underline;
    font-weight: 500;
}

.alert .link:hover {
    text-decoration: none;
}

/* User card */
.user-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-input);
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
}

.user-info .name {
    font-weight: 600;
    font-size: 1rem;
    color: var(--text-primary);
}

.user-info .meta {
    font-size: 0.813rem;
    color: var(--text-secondary);
}

.user-info .meta .sponsor {
    color: var(--text-primary);
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-top: 0.75rem;
}

.stat-item {
    background: var(--bg-card);
    padding: 0.625rem 0.75rem;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    text-align: center;
}

.stat-item .value {
    font-weight: 600;
    font-size: 1.125rem;
    color: var(--text-primary);
}

.stat-item .label {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    margin-top: 0.125rem;
}

.spacer {
    height: 1.5rem;
}

/* Toast */
.toast-container {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-width: 380px;
    width: 100%;
}

.toast {
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    background: var(--text-primary);
    color: white;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: var(--shadow-md);
    animation: slideIn 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.toast-success { background: var(--success); }
.toast-error { background: var(--danger); }
.toast-warning { background: #B54708; }

@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(20px); }
}

/* Confirm dialog */
.confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
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
    max-width: 440px;
    width: 95%;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
}

.confirm-box h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    text-align: center;
    margin-bottom: 0.5rem;
}

.confirm-box p {
    color: var(--text-secondary);
    font-size: 0.938rem;
    text-align: center;
    margin-bottom: 0.5rem;
}

.confirm-box .warning {
    margin: 1rem 0 1.5rem 0;
    padding: 0.625rem 0.875rem;
    background: #FEF3F2;
    border-radius: var(--radius);
    border-left: 3px solid var(--danger);
    font-size: 0.813rem;
    color: var(--danger);
}

.confirm-box .actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}

.confirm-box .actions .btn {
    min-width: 100px;
}

/* Footer */
.footer-links {
    margin-top: 2.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 1.5rem;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.footer-links a {
    color: var(--text-secondary);
    text-decoration: none;
}

.footer-links a:hover {
    color: var(--text-primary);
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 640px) {
    .grid-2, .grid-3 {
        grid-template-columns: 1fr;
    }
    .user-stats {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
    }
    .user-card {
        flex-direction: column;
        text-align: center;
    }
    .user-info .meta {
        font-size: 0.75rem;
    }
    .btn {
        width: 100%;
    }
    .actions {
        flex-direction: column;
    }
    .confirm-box .actions .btn {
        min-width: auto;
    }
    .card {
        padding: 1rem;
    }
    .page-title {
        font-size: 1.25rem;
    }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Import des PV mensuels</h1>
    <p class="page-subtitle">Ajoutez des PV à un membre via fichier CSV ou saisie manuelle</p>
</div>

<div id="toastContainer" class="toast-container"></div>

@if(session('success'))
    <div class="alert alert-success">
        {!! nl2br(e(session('success'))) !!}
        @if(session('user_id'))
            <br>
            <a href="{{ route('admin.pv.show', session('user_id')) }}" class="link">Voir l'historique</a>
        @endif
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

<!-- Résultat -->
<div id="userResult" class="{{ $user ? '' : 'hidden' }}">
    <div id="userInfo">
        @if($user)
        <div class="card">
            <div class="user-card">
                <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div class="user-info flex-1">
                    <div class="name">{{ $user->name }}</div>
                    <div class="meta">
                        Code: <span class="sponsor">{{ $user->sponsor_id ?? 'N/A' }}</span>
                        @if($user->rank)
                            · Grade: {{ $user->rank }}
                        @endif
                    </div>
                    @if($user->parrain)
                        <div class="meta">Parrain: {{ $user->parrain->name }} ({{ $user->parrain->sponsor_id }})</div>
                    @endif
                </div>
            </div>
            
            <div class="user-stats">
                <div class="stat-item">
                    <div class="value">{{ number_format($user->pv_balance ?? 0, 1, ',', ' ') }}</div>
                    <div class="label">PV Total</div>
                </div>
                <div class="stat-item">
                    <div class="value">{{ number_format($user->monthly_pv ?? 0, 1, ',', ' ') }}</div>
                    <div class="label">PV Mensuel</div>
                </div>
                <div class="stat-item">
                    <div class="value">{{ number_format($user->team_pv ?? 0, 1, ',', ' ') }}</div>
                    <div class="label">PV Équipe</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Formulaire -->
<div id="pvFormSection" class="{{ $user ? '' : 'hidden' }}">
    <div class="card">
        <div class="card-title">Ajouter des PV</div>
        <form id="addMonthlyForm" method="POST" action="{{ $user ? route('admin.pv.add-monthly', $user->id) : '' }}">
            @csrf
            
            <div class="grid-3">
                <div class="form-group">
                    <label class="form-label">Période <span class="required">*</span></label>
                    <input type="month" name="period" id="monthly_period" class="form-control" value="{{ date('Y-m') }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Montant PV <span class="required">*</span></label>
                    <input type="number" name="amount" id="monthly_amount" class="form-control" step="0.1" min="0.1" required>
                    <span class="form-help">Valeur en PV, minimum 0.1</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Type de PV <span class="required">*</span></label>
                    <select name="type" id="monthly_type" class="form-control">
                        <option value="personal">PV Personnel</option>
                        <option value="monthly">PV Mensuel</option>
                        <option value="team">PV Équipe</option>
                    </select>
                    <span class="form-help">Les PV personnel et mensuel remontent aux parrains</span>
                </div>
            </div>
            
            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter
                </button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm dialog -->
<div id="confirmDialog" class="confirm-overlay">
    <div class="confirm-box">
        <h3>Confirmer l'ajout</h3>
        <p id="confirmMessage">Voulez-vous continuer ?</p>
        <div class="warning" id="confirmWarning"></div>
        <div class="actions">
            <button onclick="closeConfirm()" class="btn btn-secondary">Annuler</button>
            <button onclick="confirmAction()" class="btn btn-success" id="confirmBtn">Confirmer</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ========== Toast ==========
function showToast(message, type) {
    type = type || 'success';
    var container = document.getElementById('toastContainer');
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(function() {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(function() { toast.remove(); }, 400);
    }, 4000);
}

// ========== Confirm ==========
var confirmCallback = null;

function showConfirm(message, warning, callback) {
    document.getElementById('confirmMessage').textContent = message;
    var warningEl = document.getElementById('confirmWarning');
    if (warning) {
        warningEl.textContent = warning;
        warningEl.style.display = 'block';
    } else {
        warningEl.style.display = 'none';
    }
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

document.getElementById('confirmDialog').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeConfirm();
});

// ========== Search ==========
var selectedUserId = {{ $user->id ?? 'null' }};

document.addEventListener('DOMContentLoaded', function() {
    @if($user)
        document.getElementById('addMonthlyForm').action = '/admin/pv/' + {{ $user->id }} + '/add-monthly';
        document.getElementById('pvFormSection').classList.remove('hidden');
        document.getElementById('monthly_amount').focus();
    @endif
});

function searchUser() {
    var query = document.getElementById('searchUser').value.trim();
    
    if (!query || query.length < 2) {
        showToast('Saisissez au moins 2 caractères', 'warning');
        return;
    }
    
    var info = document.getElementById('userInfo');
    info.innerHTML = '<div class="card"><p class="text-secondary">Recherche en cours...</p></div>';
    document.getElementById('userResult').classList.remove('hidden');
    document.getElementById('pvFormSection').classList.add('hidden');
    
    var token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
    
    fetch('{{ url("admin/pv/search-user") }}?search=' + encodeURIComponent(query), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        }
    })
    .then(function(response) {
        return response.json().then(function(data) {
            if (!response.ok) throw new Error(data.error || 'Erreur ' + response.status);
            return data;
        });
    })
    .then(function(data) {
        if (data.error) {
            info.innerHTML = '<div class="card"><p class="text-danger">' + data.error + '</p></div>';
            return;
        }
        
        selectedUserId = data.id;
        document.getElementById('addMonthlyForm').action = '/admin/pv/' + data.id + '/add-monthly';
        
        var sponsorInfo = data.parrain_name ? '<div class="meta">Parrain: ' + data.parrain_name + ' (' + data.parrain_sponsor_id + ')</div>' : '';
        var rankInfo = data.rank_name ? '· Grade: ' + data.rank_name : '';
        
        info.innerHTML = 
            '<div class="card">' +
                '<div class="user-card">' +
                    '<div class="user-avatar">' + data.name.charAt(0).toUpperCase() + '</div>' +
                    '<div class="user-info flex-1">' +
                        '<div class="name">' + data.name + '</div>' +
                        '<div class="meta">Code: <span class="sponsor">' + data.sponsor_id + '</span> ' + rankInfo + '</div>' +
                        sponsorInfo +
                    '</div>' +
                '</div>' +
                '<div class="user-stats">' +
                    '<div class="stat-item"><div class="value">' + data.pv_balance + '</div><div class="label">PV Total</div></div>' +
                    '<div class="stat-item"><div class="value">' + data.monthly_pv + '</div><div class="label">PV Mensuel</div></div>' +
                    '<div class="stat-item"><div class="value">' + data.team_pv + '</div><div class="label">PV Équipe</div></div>' +
                '</div>' +
            '</div>';
        
        document.getElementById('pvFormSection').classList.remove('hidden');
        document.getElementById('monthly_amount').focus();
    })
    .catch(function(err) {
        console.error('Erreur:', err);
        info.innerHTML = '<div class="card"><p class="text-danger">Erreur: ' + err.message + '</p></div>';
    });
}

function resetForm() {
    document.getElementById('userResult').classList.add('hidden');
    document.getElementById('pvFormSection').classList.add('hidden');
    document.getElementById('searchUser').value = '';
    document.getElementById('searchUser').focus();
    selectedUserId = null;
}

// ========== Form submit ==========
document.getElementById('addMonthlyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var amount = document.getElementById('monthly_amount').value;
    var period = document.getElementById('monthly_period').value;
    var typeSelect = document.getElementById('monthly_type');
    var type = typeSelect.options[typeSelect.selectedIndex].text;
    
    if (!amount || parseFloat(amount) <= 0) {
        showToast('Saisissez un montant valide (supérieur à 0)', 'warning');
        return;
    }
    
    showConfirm(
        'Ajouter ' + amount + ' PV (' + type + ') pour ' + period + ' ?',
        'Cette opération est traçable dans l\'historique.',
        function() {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Ajout en cours...';
            document.getElementById('addMonthlyForm').submit();
        }
    );
});

// ========== Shortcuts ==========
document.getElementById('searchUser').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchUser();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('confirmDialog').classList.contains('active')) {
            closeConfirm();
        } else {
            resetForm();
        }
    }
});
</script>
@endpush
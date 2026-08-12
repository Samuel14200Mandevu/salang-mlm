{{-- resources/views/admin/pv/import.blade.php --}}
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
    .text-[var(--text-primary)] { color: var(--text-primary); }
    .text-[var(--text-secondary)] { color: var(--text-secondary); }
    .text-primary-500 { color: var(--primary-500); }
    .text-green-500 { color: #22c55e; }
    .text-blue-500 { color: #3b82f6; }
    .text-red-500 { color: #ef4444; }
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
    .bg-[var(--bg-secondary)] { background: var(--bg-secondary); }
    .hover\:underline:hover { text-decoration: underline; }
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
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">
         Import des PV Mensuels
    </h1>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 whitespace-pre-line">
            {{ session('success') }}
            @if(session('user_id'))
                <br>
                <a href="{{ route('admin.pv.show', session('user_id')) }}" class="text-primary-500 hover:underline font-semibold">
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

    <!-- ÉTAPE 1: Rechercher un membre -->
    <div class="card">
        
        <!-- Résultat de la recherche -->
        <div id="userResult" class="mt-4 {{ $user ? '' : 'hidden' }}">
            <div id="userInfo">
                @if($user)
                <div class="user-info-card">
                    <div class="flex items-center gap-4">
                        <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-bold text-[var(--text-primary)]">{{ $user->name }}</h3>
                                <span class="badge badge-purple">{{ $user->rank_name ?? 'Distributeur' }} (Niv. {{ $user->rank_level ?? 1 }})</span>
                                <span class="badge badge-info">Code: {{ $user->sponsor_id ?? 'N/A' }}</span>
                            </div>
                            @if($user->parrain)
                                <p class="text-sm text-[var(--text-secondary)]">
                                    Parrain: <span class="text-primary-500">{{ $user->parrain->name }}</span> 
                                    ({{ $user->parrain->sponsor_id }})
                                </p>
                            @else
                                <p class="text-sm text-[var(--text-secondary)]">Aucun parrain</p>
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
                            <div class="label">PV Équipe</div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                        <p class="text-sm text-[var(--text-secondary)]">
                            <span class="font-semibold text-[var(--text-primary)]"> Membre sélectionné</span> 
                            — Vous pouvez maintenant saisir les PV pour ce membre.
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ÉTAPE 2: Saisir les PV (affiché après recherche) -->
    <div id="pvFormSection" class="{{ $user ? '' : 'hidden' }}">
        <div class="card">
            <form id="addMonthlyForm" method="POST" action="{{ $user ? route('admin.pv.add-monthly', $user->id) : '' }}">
                @csrf
                
                <div class="grid-cols-3">
                    <div class="form-group">
                        <label for="monthly_period">Période <span class="required">*</span></label>
                        <input type="month" name="period" id="monthly_period" value="{{ date('Y-m') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="monthly_amount">Montant PV <span class="required">*</span></label>
                        <input type="number" name="amount" id="monthly_amount" step="0.1" required min="0.1" placeholder="Ex: 100.5">
                        <small class="help-text">Saisir le montant en PV (ex: 100.5)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="monthly_type">Type de PV <span class="required">*</span></label>
                        <select name="type" id="monthly_type">
                            <option value="personal">PV Personnel</option>
                            <option value="monthly">PV Mensuel</option>
                            <option value="team">PV Équipe</option>
                        </select>
                        <small class="help-text">Type de PV à ajouter</small>
                    </div>
                </div>
                
                <div class="flex gap-4 mt-4">
                    <button type="submit" class="btn btn-success">
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
                    @if($user)
                    <a href="{{ route('admin.pv.show', $user->id) }}" class="btn btn-outline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour au membre
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Lien vers l'import CSV -->
    <div class="card">
        <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4"> Import CSV</h2>
        <p class="text-sm text-[var(--text-secondary)] mb-4">
            Vous pouvez également importer plusieurs PV en une seule fois via un fichier CSV.
        </p>
        <a href="{{ route('admin.pv.import.index') }}" class="btn btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Importer un fichier CSV
        </a>
    </div>
</div>

<script>
// Variable pour stocker l'ID de l'utilisateur sélectionné
let selectedUserId = {{ $user->id ?? 'null' }};

// Si un utilisateur est pré-sélectionné, configurer le formulaire
document.addEventListener('DOMContentLoaded', function() {
    @if($user)
        // Mettre à jour l'action du formulaire
        document.getElementById('addMonthlyForm').action = '/admin/pv/' + {{ $user->id }} + '/add-monthly';
        // Afficher le formulaire
        document.getElementById('pvFormSection').classList.remove('hidden');
        // Mettre le focus sur le champ montant
        document.getElementById('monthly_amount').focus();
    @endif
});

function searchUser() {
    const query = document.getElementById('searchUser').value;
    
    if (!query || query.length < 2) {
        alert('Veuillez saisir au moins 2 caractères (nom, email ou code sponsor)');
        return;
    }
    
    // Afficher un indicateur de chargement
    document.getElementById('userInfo').innerHTML = `
        <div class="user-info-card">
            <div class="flex items-center gap-4">
                <div class="avatar">?</div>
                <div>
                    <p class="text-[var(--text-secondary)]">🔍 Recherche en cours...</p>
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
                throw new Error('Erreur ' + response.status + ' - ' + response.statusText);
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
                            <p class="text-red-500 font-semibold"> ${data.error}</p>
                            <p class="text-sm text-[var(--text-secondary)]">Veuillez essayer avec d'autres critères.</p>
                        </div>
                    </div>
                </div>
            `;
            return;
        }
        
        // Stocker l'ID de l'utilisateur
        selectedUserId = data.id;
        
        // Mettre à jour l'action du formulaire
        document.getElementById('addMonthlyForm').action = '/admin/pv/' + data.id + '/add-monthly';
        
        // Afficher les informations complètes du membre
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
                        ${data.parrain_name ? `<p class="text-sm text-[var(--text-secondary)]">Parrain: <span class="text-primary-500">${data.parrain_name}</span> (${data.parrain_sponsor_id})</p>` : '<p class="text-sm text-[var(--text-secondary)]">Aucun parrain</p>'}
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
                        <div class="label">PV Équipe</div>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-[var(--border-color)]">
                    <p class="text-sm text-[var(--text-secondary)]">
                        <span class="font-semibold text-[var(--text-primary)]"> Membre sélectionné</span> 
                        — Vous pouvez maintenant saisir les PV pour ce membre.
                    </p>
                </div>
            </div>
        `;
        
        // Afficher le formulaire de saisie des PV
        document.getElementById('pvFormSection').classList.remove('hidden');
        
        // Mettre automatiquement le focus sur le champ montant
        document.getElementById('monthly_amount').focus();
    })
    .catch(err => {
        console.error('Erreur détaillée:', err);
        document.getElementById('userInfo').innerHTML = `
            <div class="user-info-card">
                <div class="flex items-center gap-4">
                    <div class="avatar" style="background:#ef4444;">!</div>
                    <div>
                        <p class="text-red-500 font-semibold"> Erreur: ${err.message}</p>
                        <p class="text-sm text-[var(--text-secondary)]">Vérifiez que le serveur est accessible et que vous êtes connecté.</p>
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

// Soumission du formulaire avec confirmation
document.getElementById('addMonthlyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const amount = document.getElementById('monthly_amount').value;
    const period = document.getElementById('monthly_period').value;
    const type = document.getElementById('monthly_type').options[document.getElementById('monthly_type').selectedIndex].text;
    
    if (!amount || amount <= 0) {
        alert('Veuillez saisir un montant PV valide (supérieur à 0)');
        return;
    }
    
    if (!confirm(`Confirmez-vous l'ajout de ${amount} PV (${type}) pour la période ${period} ?`)) {
        return;
    }
    
    this.submit();
});

// Recherche avec la touche Entrée
document.getElementById('searchUser').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchUser();
    }
});

// Raccourci: Échap pour réinitialiser
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        resetForm();
    }
});
</script>
@endsection
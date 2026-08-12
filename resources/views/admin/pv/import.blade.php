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
    .mt-4 { margin-top: 1rem; }
    .mb-4 { margin-bottom: 1rem; }
    .space-y-6 > * + * { margin-top: 1.5rem; }
    .flex { display: flex; }
    .gap-4 { gap: 1rem; }
    .flex-1 { flex: 1; }
    .items-end { align-items: flex-end; }
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
    .bg-green-500\/10 { background: rgba(34, 197, 94, 0.1); }
    .border { border: 1px solid var(--border-color); }
    .border-green-500\/20 { border-color: rgba(34, 197, 94, 0.2); }
    .rounded-lg { border-radius: var(--radius-lg); }
    .p-4 { padding: 1rem; }
    .p-6 { padding: 1.5rem; }
    .whitespace-pre-line { white-space: pre-line; }
    .bg-red-500\/10 { background: rgba(239, 68, 68, 0.1); }
    .border-red-500\/20 { border-color: rgba(239, 68, 68, 0.2); }
    .text-red-500 { color: #ef4444; }
    .bg-[var(--bg-secondary)] { background: var(--bg-secondary); }
    .hover\:underline:hover { text-decoration: underline; }
    code {
        background: var(--bg-secondary);
        padding: 0.125rem 0.375rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        color: var(--text-primary);
    }
    
    @media (max-width: 640px) {
        .grid-cols-2 {
            grid-template-columns: 1fr;
        }
        .flex.gap-4 {
            flex-direction: column;
        }
        .btn {
            width: 100%;
        }
        .p-6 { padding: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <h1 class="text-2xl font-bold text-[var(--text-primary)]">
        📥 Import des PV Mensuels
    </h1>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 whitespace-pre-line">
            {{ session('success') }}
            @if(session('user_id'))
                <br>
                <a href="{{ route('admin.pv.show', session('user_id')) }}" class="text-primary-500 hover:underline font-semibold">
                    📊 Voir l'historique des PV de l'utilisateur
                </a>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500">
            {{ session('error') }}
        </div>
    @endif

    <!-- Import CSV -->
    <div class="card">
        <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4">📄 Import CSV</h2>
        <p class="text-sm text-[var(--text-secondary)] mb-4">
            Format : <code>member_id,member_name,product_code,product_name,quantity,unit_pv,total_pv,order_date</code>
        </p>
        
        <form method="POST" action="{{ route('admin.pv.import.csv') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="grid-cols-2">
                <div class="form-group">
                    <label for="period">Période <span class="required">*</span></label>
                    <input type="month" id="period" name="period" value="{{ date('Y-m') }}" required>
                </div>
                
                <div class="form-group">
                    <label for="csv_file">Fichier CSV <span class="required">*</span></label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Importer
                </button>
            </div>
        </form>
    </div>

    <!-- Recherche utilisateur -->
    <div class="card">
        <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4">🔍 Rechercher un membre</h2>
        
        <div class="flex gap-4">
            <input type="text" id="searchUser" placeholder="Nom ou code sponsor..." class="form-input flex-1">
            <button onclick="searchUser()" class="btn btn-secondary">Rechercher</button>
        </div>
        
        <div id="userResult" class="mt-4 hidden">
            <div class="p-4 bg-[var(--bg-secondary)] rounded-lg">
                <div id="userInfo"></div>
                <div class="mt-4">
                    <form id="addMonthlyForm" method="POST" class="flex gap-4 items-end">
                        @csrf
                        <div class="form-group flex-1">
                            <label for="monthly_amount">Montant PV</label>
                            <input type="number" name="amount" id="monthly_amount" step="0.1" required min="0.1">
                        </div>
                        <div class="form-group flex-1">
                            <label for="monthly_period">Période</label>
                            <input type="month" name="period" id="monthly_period" value="{{ date('Y-m') }}" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="monthly_type">Type</label>
                            <select name="type" id="monthly_type">
                                <option value="personal">PV Personnel</option>
                                <option value="monthly">PV Mensuel</option>
                                <option value="team">PV Équipe</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lien vers la gestion des PV -->
    <div class="card">
        <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4">📊 Gestion des PV</h2>
        <p class="text-sm text-[var(--text-secondary)] mb-4">
            Retourner à la gestion des PV pour voir l'historique complet.
        </p>
        <a href="{{ route('admin.pv.index') }}" class="btn btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la gestion des PV
        </a>
    </div>
</div>

<script>
function searchUser() {
    const query = document.getElementById('searchUser').value;
    
    // Vérifier que la recherche n'est pas vide
    if (!query || query.length < 2) {
        alert('Veuillez saisir au moins 2 caractères (nom ou code sponsor)');
        return;
    }
    
    // Afficher un indicateur de chargement
    document.getElementById('userInfo').innerHTML = '<p class="text-[var(--text-secondary)]">🔍 Recherche en cours...</p>';
    document.getElementById('userResult').classList.remove('hidden');
    
    // Récupérer le token CSRF
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    
    // Utiliser l'URL absolue
    fetch('{{ url("admin/pv/user-stats") }}?search=' + encodeURIComponent(query), {
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
            if (response.status === 401 || response.status === 403) {
                throw new Error('Session expirée. Veuillez rafraîchir la page.');
            }
            if (response.status === 404) {
                throw new Error('Aucun membre trouvé avec ces critères.');
            }
            throw new Error('Erreur ' + response.status + ' - ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            document.getElementById('userInfo').innerHTML = `
                <p class="text-red-500">❌ ${data.error}</p>
            `;
            return;
        }
        
        // Afficher les informations
        document.getElementById('userInfo').innerHTML = `
            <div class="space-y-2">
                <p><strong>${data.name}</strong> <span class="text-sm text-[var(--text-secondary)]">(${data.sponsor_id})</span></p>
                <p>Grade: <span class="badge badge-purple">${data.rank_name} (Niv. ${data.rank_level})</span></p>
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <div><span class="text-[var(--text-secondary)]">PV Total:</span> <strong class="text-primary-500">${data.pv_balance}</strong></div>
                    <div><span class="text-[var(--text-secondary)]">PV Mensuel:</span> <strong class="text-green-500">${data.monthly_pv}</strong></div>
                    <div><span class="text-[var(--text-secondary)]">PV Équipe:</span> <strong class="text-blue-500">${data.team_pv}</strong></div>
                </div>
                ${data.parrain_name ? `<p>Parrain: <span class="text-primary-500">${data.parrain_name}</span> (${data.parrain_sponsor_id})</p>` : '<p class="text-[var(--text-secondary)]">Aucun parrain</p>'}
            </div>
        `;
        
        // Mettre à jour l'action du formulaire
        document.getElementById('addMonthlyForm').action = `/admin/pv/${data.id}/add-monthly`;
    })
    .catch(err => {
        console.error('Erreur détaillée:', err);
        document.getElementById('userInfo').innerHTML = `
            <p class="text-red-500">❌ Erreur: ${err.message}</p>
            <p class="text-sm text-[var(--text-secondary)]">Vérifiez que le serveur est accessible et que vous êtes connecté.</p>
        `;
    });
}
</script>
@endsection
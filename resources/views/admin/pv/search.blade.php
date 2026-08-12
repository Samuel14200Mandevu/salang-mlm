{{-- resources/views/admin/pv/search.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<style>
    .search-container {
        padding: 1.5rem;
    }
    .search-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .search-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .search-form {
        display: flex;
        gap: 0.75rem;
        flex: 1;
        max-width: 500px;
    }
    .search-form input {
        flex: 1;
        padding: 0.5rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: 0.875rem;
        min-width: 200px;
    }
    .search-form input:focus {
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
        white-space: nowrap;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
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
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34, 197, 94, 0.4);
    }
    .btn-info {
        background: var(--gradient-info);
        color: white;
    }
    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(59, 130, 246, 0.4);
    }
    
    .users-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    .users-table thead {
        background: var(--bg-secondary);
    }
    .users-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
    }
    .users-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
    }
    .users-table tbody tr:hover {
        background: var(--bg-hover);
    }
    .users-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .badge-danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .badge-purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .badge-neutral { background: var(--bg-secondary); color: var(--text-secondary); }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--text-secondary);
    }
    .empty-state .icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    .empty-state h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .pagination-container {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }
    .pagination-container nav {
        display: inline-flex;
        gap: 0.25rem;
    }
    .pagination-container .page-link {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        text-decoration: none;
        font-size: 0.813rem;
        transition: all 0.2s ease;
    }
    .pagination-container .page-link:hover {
        background: var(--bg-hover);
    }
    .pagination-container .active .page-link {
        background: var(--primary-500);
        color: white;
        border-color: var(--primary-500);
    }
    .pagination-container .disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
        .search-header {
            flex-direction: column;
            align-items: stretch;
        }
        .search-form {
            max-width: 100%;
            flex-wrap: wrap;
        }
        .search-form input {
            min-width: 100%;
        }
        .users-table {
            font-size: 0.75rem;
        }
        .users-table th,
        .users-table td {
            padding: 0.5rem;
        }
        .btn-sm {
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@section('content')
<div class="search-container">
    <!-- En-tête -->
    <div class="search-header">
        <h1>🔍 Recherche des membres</h1>
        <div class="search-form">
            <form method="GET" action="{{ route('admin.pv.search') }}" style="display:flex;gap:0.5rem;flex:1;">
                <input type="text" name="search" placeholder="Nom, email ou code sponsor..." value="{{ $search ?? '' }}" required>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Rechercher
                </button>
            </form>
            <a href="{{ url('/admin/pv') }}" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Résultats -->
    @if(isset($results) && $results->count() > 0)
        <div class="card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Membre</th>
                        <th>Code Sponsor</th>
                        <th>Grade</th>
                        <th>PV Total</th>
                        <th>PV Mensuel</th>
                        <th>PV Équipe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <div class="text-xs text-[var(--text-secondary)]">{{ $user->email }}</div>
                                </div>
                            </td>
                            <td><code>{{ $user->sponsor_id ?? 'N/A' }}</code></td>
                            <td>
                                <span class="badge badge-purple">{{ $user->rank_name ?? 'Distributeur' }}</span>
                            </td>
                            <td><strong class="text-primary-500">{{ number_format($user->pv_balance ?? 0, 1, ',', ' ') }}</strong></td>
                            <td><strong class="text-green-500">{{ number_format($user->monthly_pv ?? 0, 1, ',', ' ') }}</strong></td>
                            <td><strong class="text-blue-500">{{ number_format($user->team_pv ?? 0, 1, ',', ' ') }}</strong></td>
                            <td>
                                <div class="flex gap-1" style="display:flex;gap:0.25rem;flex-wrap:wrap;">
                                    <a href="{{ url('/admin/pv/' . $user->id) }}" class="btn btn-sm btn-info">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ url('/admin/users/' . $user->id) }}" class="btn btn-sm btn-secondary">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if(method_exists($results, 'links'))
                <div class="pagination-container">
                    {{ $results->appends(['search' => $search ?? ''])->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:2rem;">
            <div class="empty-state">
                <div class="icon">🔍</div>
                <h3>Aucun résultat trouvé</h3>
                <p>Aucun membre ne correspond à votre recherche.</p>
                @if(isset($search) && $search)
                    <p class="text-sm text-[var(--text-secondary)]">Recherche : "{{ $search }}"</p>
                @endif
                <div style="margin-top:1rem;">
                    <a href="{{ url('/admin/pv') }}" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour à la gestion des PV
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
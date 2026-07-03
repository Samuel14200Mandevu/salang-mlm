@extends('admin.layouts.app')

@push('styles')
<style>
    .kyc-row:hover { background: var(--bg-hover); }
    .kyc-preview-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .kyc-preview-img:hover { transform: scale(1.1); }
    
    @media (max-width: 640px) {
        .kyc-preview-img { width: 32px; height: 32px; }
        .table thead th, .table tbody td { padding: 0.375rem 0.5rem; font-size: 0.7rem; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        .btn-sm svg { width: 0.875rem; height: 0.875rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
        .avatar-sm { width: 1.5rem; height: 1.5rem; font-size: 0.6rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Verifications KYC</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Gerez les demandes de verification d'identite</p>
        </div>
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
            <span class="badge badge-warning text-[10px] sm:text-xs">{{ $pendingDocs->total() ?? 0 }} en attente</span>
            <span class="badge badge-success text-[10px] sm:text-xs">{{ \App\Models\KycDocument::where('status', 'verified')->count() }} verifies</span>
            <span class="badge badge-danger text-[10px] sm:text-xs">{{ \App\Models\KycDocument::where('status', 'rejected')->count() }} rejetes</span>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">En attente</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $pendingDocs->total() ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Verifies</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ \App\Models\KycDocument::where('status', 'verified')->count() }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-red-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Rejetes</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ \App\Models\KycDocument::where('status', 'rejected')->count() }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Utilisateurs verifies</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ \App\Models\User::where('kyc_status', 'verified')->count() }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[120px] sm:min-w-[150px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="statusFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="verified">Verifie</option>
            <option value="rejected">Rejete</option>
        </select>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">Utilisateur</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm">Document</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Date</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="kycTable">
                    @forelse($pendingDocs ?? [] as $doc)
                        <tr class="kyc-row" 
                            data-status="{{ $doc->status }}"
                            data-user="{{ strtolower($doc->user?->name ?? '') }}">
                            <td>
                                <div class="flex items-center gap-1.5 sm:gap-2">
                                    <div class="avatar avatar-sm avatar-gradient">
                                        {{ substr($doc->user?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-[var(--text-primary)] text-xs sm:text-sm truncate max-w-[60px] sm:max-w-[100px] md:max-w-none">{{ $doc->user?->name ?? 'N/A' }}</p>
                                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate max-w-[60px] sm:max-w-[100px] md:max-w-none">{{ $doc->user?->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">{{ ucfirst(str_replace('_', ' ', $doc->document_type ?? 'Document')) }}</span>
                            </td>
                            <td>
                                @if($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="inline-block">
                                        @if(str_starts_with($doc->mime_type, 'image/'))
                                            <img src="{{ asset('storage/' . $doc->file_path) }}" 
                                                 alt="{{ $doc->file_name }}"
                                                 class="kyc-preview-img">
                                        @else
                                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-[var(--text-secondary)] text-xs sm:text-sm">N/A</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $doc->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge {{ $doc->status == 'pending' ? 'badge-warning' : ($doc->status == 'verified' ? 'badge-success' : 'badge-danger') }} text-[10px] sm:text-xs">
                                    {{ $doc->status_label ?? ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if($doc->status == 'pending')
                                    <div class="flex items-center justify-end gap-1">
                                        <form action="{{ route('admin.kyc.verify', ['id' => $doc->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="verified">
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" 
                                                    onclick="return confirm('Verifier ce document ?')" title="Verifier">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.kyc.verify', ['id' => $doc->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="hidden" name="rejection_reason" value="Document non conforme">
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" 
                                                    onclick="return confirm('Rejeter ce document ?')" title="Rejeter">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 sm:py-8 text-[var(--text-secondary)]">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <h4 class="text-sm sm:text-base font-semibold text-[var(--text-primary)]">Aucune demande en attente</h4>
                                <p class="text-xs sm:text-sm mt-0.5 sm:mt-1">Tous les documents ont ete traites.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($pendingDocs) && $pendingDocs->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $pendingDocs->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('#kycTable tr');

    function filterRows() {
        const search = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;

        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status || '';
            const rowUser = row.dataset.user || '';

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
});
</script>
@endpush
@endsection
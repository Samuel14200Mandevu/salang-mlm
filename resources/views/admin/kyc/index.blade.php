@extends('admin.layouts.app')

@push('styles')
<style>
    .kyc-row:hover { background: var(--bg-hover); }
    .kyc-preview-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .kyc-preview-img:hover { transform: scale(1.1); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🪪 Vérifications KYC</h1>
            <p class="text-[var(--text-secondary)] mt-1">Gérez les demandes de vérification d'identité</p>
        </div>
        <div class="flex gap-2">
            <span class="badge badge-warning">{{ $pendingDocs->total() ?? 0 }} en attente</span>
            <span class="badge badge-success">{{ \App\Models\KycDocument::where('status', 'verified')->count() }} vérifiés</span>
            <span class="badge badge-danger">{{ \App\Models\KycDocument::where('status', 'rejected')->count() }} rejetés</span>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-yellow-500">
            <p class="text-sm text-[var(--text-secondary)]">En attente</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $pendingDocs->total() ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Vérifiés</p>
            <p class="text-2xl font-bold text-green-500">{{ \App\Models\KycDocument::where('status', 'verified')->count() }}</p>
        </div>
        <div class="card-stats border-l-4 border-red-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Rejetés</p>
            <p class="text-2xl font-bold text-red-500">{{ \App\Models\KycDocument::where('status', 'rejected')->count() }}</p>
        </div>
        <div class="card-stats border-l-4 border-primary-500 animate-fadeInUp delay-4">
            <p class="text-sm text-[var(--text-secondary)]">Utilisateurs vérifiés</p>
            <p class="text-2xl font-bold text-primary-500">{{ \App\Models\User::where('kyc_status', 'verified')->count() }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-3 animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[150px] max-w-sm">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Rechercher..." class="input pl-9">
        </div>
        <select id="statusFilter" class="input w-auto min-w-[140px]">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="verified">Vérifié</option>
            <option value="rejected">Rejeté</option>
        </select>
    </div>

    <!-- Tableau -->
    <div class="card animate-fadeInUp delay-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Type</th>
                        <th>Document</th>
                        <th class="hidden md:table-cell">Date</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="kycTable">
                    @forelse($pendingDocs ?? [] as $doc)
                        <tr class="kyc-row" 
                            data-status="{{ $doc->status }}"
                            data-user="{{ strtolower($doc->user?->name ?? '') }}">
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar avatar-sm avatar-gradient">
                                        {{ substr($doc->user?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-[var(--text-primary)]">{{ $doc->user?->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-[var(--text-secondary)]">{{ $doc->user?->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    @if($doc->document_type == 'id_card') 🪪 Carte d'identité
                                    @elseif($doc->document_type == 'passport') 📘 Passeport
                                    @elseif($doc->document_type == 'proof_of_address') 📬 Justificatif de domicile
                                    @elseif($doc->document_type == 'selfie') 🤳 Selfie
                                    @else 📄 {{ $doc->document_type }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="inline-block">
                                        @if(str_starts_with($doc->mime_type, 'image/'))
                                            <img src="{{ asset('storage/' . $doc->file_path) }}" 
                                                 alt="{{ $doc->file_name }}"
                                                 class="kyc-preview-img">
                                        @else
                                            <span class="text-2xl">📄</span>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-[var(--text-secondary)]">N/A</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell text-sm text-[var(--text-secondary)]">
                                {{ $doc->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge {{ $doc->status == 'pending' ? 'badge-warning' : ($doc->status == 'verified' ? 'badge-success' : 'badge-danger') }}">
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
                                                    onclick="return confirm('✅ Vérifier ce document ?')" title="Vérifier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.kyc.verify', ['id' => $doc->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="hidden" name="rejection_reason" value="Document non conforme">
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" 
                                                    onclick="return confirm('❌ Rejeter ce document ?')" title="Rejeter">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <td colspan="6" class="text-center py-8 text-[var(--text-secondary)]">
                                <div class="text-6xl mb-4"></div>
                                <h4 class="text-lg font-semibold text-[var(--text-primary)]">Aucune demande en attente</h4>
                                <p class="text-sm mt-1">Tous les documents ont été traités.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($pendingDocs) && $pendingDocs->hasPages())
            <div class="mt-4">
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

        rows.forEach(row => {
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
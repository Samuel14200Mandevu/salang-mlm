@extends('admin.layouts.app')

@push('styles')
<style>
    .kyc-row {
        transition: all 0.2s ease;
    }
    .kyc-row:hover {
        background: var(--bg-hover);
    }
    .kyc-preview-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }
    .kyc-preview-img:hover {
        transform: scale(1.1);
        border-color: var(--primary-500);
    }
    
    .stats-mini {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
    @media (max-width: 640px) {
        .kyc-preview-img {
            width: 32px;
            height: 32px;
        }
        .table thead th, .table tbody td {
            padding: 0.375rem 0.5rem;
            font-size: 0.7rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        .btn-sm svg {
            width: 0.875rem;
            height: 0.875rem;
        }
        .badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .avatar-sm {
            width: 1.5rem;
            height: 1.5rem;
            font-size: 0.6rem;
        }
        .filters-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
        .filters-wrapper .input {
            width: 100% !important;
        }
        .stats-mini .badge {
            font-size: 0.55rem;
            padding: 0.1rem 0.4rem;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .filters-wrapper .input {
            min-width: 120px;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">KYC Verification</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Manage identity verification requests</p>
        </div>
        <div class="stats-mini">
            <span class="badge badge-warning text-[10px] sm:text-xs">{{ $pendingDocs->total() ?? 0 }} Pending</span>
            <span class="badge badge-success text-[10px] sm:text-xs">{{ \App\Models\KycDocument::where('status', 'verified')->count() }} Verified</span>
            <span class="badge badge-danger text-[10px] sm:text-xs">{{ \App\Models\KycDocument::where('status', 'rejected')->count() }} Rejected</span>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-yellow-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Pending</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-yellow-500">{{ $pendingDocs->total() ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Verified</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ \App\Models\KycDocument::where('status', 'verified')->count() }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-red-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Rejected</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-red-500">{{ \App\Models\KycDocument::where('status', 'rejected')->count() }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Verified Users</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ \App\Models\User::where('kyc_status', 'verified')->count() }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-wrapper flex flex-wrap items-center gap-2 sm:gap-3 animate-fadeInUp delay-5">
        <div class="relative flex-1 min-w-[120px] sm:min-w-[150px] max-w-xs sm:max-w-sm">
            <span class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-[var(--text-tertiary)]">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Search user..." class="input pl-7 sm:pl-9 text-sm sm:text-base">
        </div>
        <select id="statusFilter" class="input w-auto min-w-[110px] sm:min-w-[140px] text-sm sm:text-base">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <!-- KYC List -->
    <div class="card animate-fadeInUp delay-6 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">User</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Type</th>
                        <th class="text-xs sm:text-sm">Document</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Date</th>
                        <th class="text-xs sm:text-sm">Status</th>
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
                                        <p class="font-medium text-[var(--text-primary)] text-xs sm:text-sm truncate max-w-[60px] sm:max-w-[100px] md:max-w-none">
                                            {{ $doc->user?->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] truncate max-w-[60px] sm:max-w-[100px] md:max-w-none">
                                            {{ $doc->user?->email ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell">
                                <span class="badge badge-info text-[10px] sm:text-xs">
                                    {{ ucfirst(str_replace('_', ' ', $doc->document_type ?? 'Document')) }}
                                </span>
                            </td>
                            <td>
                                @if($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="inline-block">
                                        @if(str_starts_with($doc->mime_type ?? '', 'image/'))
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
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if($doc->status == 'pending')
                                    <div class="flex items-center justify-end gap-1">
                                        <form action="{{ route('admin.kyc.verify', $doc->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="verified">
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" 
                                                    onclick="return confirm('Verify this document?')" title="Verify">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <button type="button" 
                                                onclick="openRejectModal('{{ $doc->id }}', '{{ $doc->user?->name ?? 'User' }}')" 
                                                class="btn btn-danger btn-sm btn-icon" 
                                                title="Reject">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
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
                                <h4 class="text-sm sm:text-base font-semibold text-[var(--text-primary)]">No pending requests</h4>
                                <p class="text-xs sm:text-sm mt-0.5 sm:mt-1">All documents have been processed</p>
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

<!-- ============================================================
REJECT MODAL
============================================================ -->
<div id="rejectModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center; z-index:9999;">
    <div class="modal-box" style="background:var(--bg-card); border-radius:var(--radius-lg); padding:2rem; max-width:450px; width:90%; box-shadow:var(--shadow-xl); border:1px solid var(--border-color);">
        <div class="modal-icon modal-icon-danger" style="width:4rem; height:4rem; border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; background:rgba(239,68,68,0.1); color:#ef4444;">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h3 class="modal-title" style="text-align:center; font-size:1.25rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem;">Confirm Rejection</h3>
        <p class="modal-text" style="text-align:center; font-size:0.875rem; color:var(--text-secondary); margin-bottom:1.5rem; line-height:1.6;">
            Are you sure you want to <strong style="color:#ef4444;">reject</strong> the KYC document of <strong id="rejectUserName"></strong> ?
            <br>
            The user will need to resubmit their documents.
        </p>
        <form id="rejectForm" action="" method="POST" class="modal-actions" style="display:flex; gap:0.75rem; justify-content:center;">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <input type="hidden" name="rejection_reason" value="Document does not meet requirements">
            <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm" style="padding:0.375rem 1rem; font-size:0.75rem; border:2px solid var(--border-color); background:transparent; color:var(--text-primary); border-radius:var(--radius-md); cursor:pointer;">
                Cancel
            </button>
            <button type="submit" class="btn btn-danger btn-sm" style="padding:0.375rem 1rem; font-size:0.75rem; background:var(--gradient-danger); color:white; border:none; border-radius:var(--radius-md); cursor:pointer; display:flex; align-items:center; gap:0.5rem;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reject
            </button>
        </form>
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

            let show = true;

            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
});

// ============================================================
// REJECT MODAL
// ============================================================
function openRejectModal(docId, userName) {
    document.getElementById('rejectModal').style.display = 'flex';
    document.getElementById('rejectUserName').textContent = userName;
    document.getElementById('rejectForm').action = '/admin/kyc/' + docId + '/verify';
    document.body.style.overflow = 'hidden';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(function(modal) {
            if (modal.style.display === 'flex') {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
});
</script>
@endpush
@endsection
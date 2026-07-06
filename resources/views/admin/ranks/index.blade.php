@extends('admin.layouts.app')

@push('styles')
<style>
    .rank-row {
        transition: all 0.2s ease;
    }
    .rank-row:hover {
        background: var(--bg-hover);
    }
    .rank-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
        background: var(--gradient-primary);
        color: white;
    }
    
    @media (max-width: 640px) {
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
        .rank-badge {
            font-size: 0.6rem;
            padding: 0.125rem 0.5rem;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Rank Management</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Configure ranks and their bonuses</p>
        </div>
        <div class="flex gap-1.5 sm:gap-2">
            <a href="{{ route('admin.ranks.history') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="hidden xs:inline">History</span>
            </a>
            <a href="{{ route('admin.ranks.create') }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden xs:inline">Add</span>
                <span class="inline xs:hidden">+</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 animate-fadeInUp delay-1">
        <div class="card-stats p-3 sm:p-4 border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Ranks</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-primary-500">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Active</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-500">{{ $stats['active'] ?? 0 }}</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-blue-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Max Level</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-blue-500">{{ $ranks->max('min_pv') ?? 0 }} PV</p>
        </div>
        <div class="card-stats p-3 sm:p-4 border-l-4 border-purple-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Max Bonus</p>
            <p class="text-lg sm:text-xl md:text-2xl font-bold text-purple-500">{{ $ranks->max('bonus_percentage') ?? 0 }}%</p>
        </div>
    </div>

    <!-- Rank List -->
    <div class="card animate-fadeInUp delay-5 p-3 sm:p-4 md:p-6">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">ID</th>
                        <th class="text-xs sm:text-sm">Rank</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Min PV</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Min BV</th>
                        <th class="text-xs sm:text-sm">Bonus</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Users</th>
                        <th class="text-xs sm:text-sm hidden xl:table-cell">Status</th>
                        <th class="text-xs sm:text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ranks ?? [] as $rank)
                        @php
                            $userCount = $stats['users_by_rank'][$rank->id] ?? 0;
                        @endphp
                        <tr class="rank-row">
                            <td class="font-mono text-xs sm:text-sm">#{{ $rank->id }}</td>
                            <td class="font-medium text-sm sm:text-base">
                                <span class="rank-badge">{{ $rank->name }}</span>
                            </td>
                            <td class="hidden sm:table-cell text-sm sm:text-base">{{ number_format($rank->min_pv) }}</td>
                            <td class="hidden md:table-cell text-sm sm:text-base">{{ number_format($rank->min_bv ?? 0) }}</td>
                            <td class="font-bold text-primary-500 text-sm sm:text-base">{{ $rank->bonus_percentage }}%</td>
                            <td class="hidden lg:table-cell">
                                <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $userCount }} user(s)</span>
                            </td>
                            <td class="hidden xl:table-cell">
                                <span class="badge {{ $rank->is_active ? 'badge-success' : 'badge-danger' }} text-[10px] sm:text-xs">
                                    {{ $rank->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.ranks.edit', $rank->id) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="Edit">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.ranks.toggle-status', $rank->id) }}" 
                                       class="btn btn-outline btn-sm btn-icon" 
                                       title="{{ $rank->is_active ? 'Deactivate' : 'Activate' }}">
                                        @if($rank->is_active)
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <button type="button" 
                                            onclick="openDeleteModal('{{ $rank->id }}', '{{ $rank->name }}', {{ $userCount }})" 
                                            class="btn btn-outline btn-sm btn-icon text-red-500 hover:text-red-700" 
                                            title="Delete">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">No ranks configured</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Start by creating your first rank</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================
DELETE MODAL
============================================================ -->
<div id="deleteModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center; z-index:9999;">
    <div class="modal-box" style="background:var(--bg-card); border-radius:var(--radius-lg); padding:2rem; max-width:450px; width:90%; box-shadow:var(--shadow-xl); border:1px solid var(--border-color);">
        <div class="modal-icon modal-icon-danger" style="width:4rem; height:4rem; border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; background:rgba(239,68,68,0.1); color:#ef4444;">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title" style="text-align:center; font-size:1.25rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem;">Confirm Deletion</h3>
        <p class="modal-text" style="text-align:center; font-size:0.875rem; color:var(--text-secondary); margin-bottom:1.5rem; line-height:1.6;">
            Are you sure you want to <strong style="color:#ef4444;">permanently delete</strong> <strong id="rankNameDisplay"></strong> ?
            <br>
            <span id="userCountWarning" style="color:#f59e0b; font-weight:600;"></span>
            This action is <strong style="color:#ef4444;">irreversible</strong>.
        </p>
        <div class="modal-actions" style="display:flex; gap:0.75rem; justify-content:center;">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm" style="padding:0.375rem 1rem; font-size:0.75rem; border:2px solid var(--border-color); background:transparent; color:var(--text-primary); border-radius:var(--radius-md); cursor:pointer;">
                Cancel
            </button>
            <form id="deleteForm" action="" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="padding:0.375rem 1rem; font-size:0.75rem; background:var(--gradient-danger); color:white; border:none; border-radius:var(--radius-md); cursor:pointer; display:flex; align-items:center; gap:0.5rem;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Rank
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
// DELETE MODAL
// ============================================================
function openDeleteModal(rankId, rankName, userCount) {
    document.getElementById('deleteModal').style.display = 'flex';
    document.getElementById('rankNameDisplay').textContent = rankName;
    document.getElementById('deleteForm').action = '/admin/ranks/' + rankId;
    document.body.style.overflow = 'hidden';
    
    const warning = document.getElementById('userCountWarning');
    if (userCount > 0) {
        warning.textContent = userCount + ' user(s) currently have this rank. ';
    } else {
        warning.textContent = '';
    }
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
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
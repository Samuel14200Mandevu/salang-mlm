@extends('admin.layouts.app')

@push('styles')
<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-box {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        box-shadow: var(--shadow-xl);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 1px solid var(--border-color);
    }
    .modal-overlay.active .modal-box {
        transform: scale(1);
    }
    .modal-icon {
        width: 4rem;
        height: 4rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .modal-icon-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .modal-icon-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    .modal-icon-success {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }
    .modal-title {
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .modal-text {
        text-align: center;
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    .modal-text strong {
        color: var(--text-primary);
    }
    .modal-text .text-danger {
        color: #ef4444;
    }
    .modal-text .text-warning {
        color: #f59e0b;
    }
    .modal-text .text-success {
        color: #22c55e;
    }
    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    .modal-actions .btn {
        min-width: 100px;
        justify-content: center;
    }
    
    .info-row {
        display: flex;
        flex-direction: column;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-light);
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-row .label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-tertiary);
    }
    .info-row .value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-top: 0.125rem;
    }
    
    @media (max-width: 640px) {
        .modal-box { padding: 1.5rem; }
        .modal-actions { flex-direction: column; }
        .modal-actions .btn { width: 100%; }
        .info-row .value { font-size: 0.85rem; }
        .info-grid {
            grid-template-columns: 1fr !important;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .info-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                User Details
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                ID: #{{ $user->id }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden xs:inline">Back</span>
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

    <!-- User Information -->
    <div class="card p-3 sm:p-4 md:p-6 animate-fadeInUp delay-1">
        <div class="info-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-[var(--border-light)]">
            
            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row">
                    <span class="label">Full Name</span>
                    <span class="value">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email</span>
                    <span class="value text-sm">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Phone</span>
                    <span class="value">{{ $user->phone ?? 'Not provided' }}</span>
                </div>
            </div>

            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Role</span>
                    <span class="value">
                        <span class="badge {{ $user->hasRole('admin') ? 'badge-purple' : 'badge-neutral' }}">
                            {{ $user->hasRole('admin') ? 'Administrator' : 'User' }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">KYC</span>
                    <span class="value">
                        <span class="badge {{ $user->kyc_status === 'verified' ? 'badge-success' : ($user->kyc_status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $user->kyc_status_label ?? 'Not Verified' }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="px-0 sm:px-4 py-2 sm:py-0">
                <div class="info-row">
                    <span class="label">Package</span>
                    <span class="value">{{ $user->package?->name ?? 'None' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Sponsor Code</span>
                    <span class="value font-mono text-primary-500">{{ $user->sponsor_id ?? 'None' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Sponsor</span>
                    <span class="value">
                        <!-- ✅ CORRIGÉ : Recherche du sponsor par code -->
                        @if($user->sponsor_id)
                            @php
                                $sponsor = App\Models\User::where('sponsor_id', $user->sponsor_id)->first();
                            @endphp
                            {{ $sponsor?->name ?? 'Inconnu (Code: ' . $user->sponsor_id . ')' }}
                        @else
                            None
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Registered</span>
                    <span class="value text-sm">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="mt-4 pt-4 border-t border-[var(--border-color)] grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-primary-500">{{ $downlinesCount ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Downlines</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-500">{{ $commissionsCount ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Commissions</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-500">${{ number_format($totalCommissions ?? 0, 2) }}</p>
                <p class="text-xs text-[var(--text-secondary)]">Total Commissions</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-500">{{ $user->pv_balance ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)]">PV</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-[var(--border-color)] flex flex-wrap gap-2 sm:gap-3">
            
            @if($user->is_active)
                <button type="button" 
                        onclick="openDeactivateModal()" 
                        class="btn btn-warning btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Deactivate
                </button>
            @else
                <button type="button" 
                        onclick="openActivateModal()" 
                        class="btn btn-success btn-sm sm:btn-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Activate
                </button>
            @endif

            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>

            <button type="button" 
                    onclick="openDeleteModal()" 
                    class="btn btn-danger btn-sm sm:btn-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
DEACTIVATE MODAL
============================================================ -->
<div id="deactivateModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirm Deactivation</h3>
        <p class="modal-text">
            Are you sure you want to <strong class="text-warning">deactivate</strong> the account of <strong>{{ $user->name }}</strong> ?
            <br>
            The user will not be able to login until reactivated.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeactivateModal()" class="btn btn-outline btn-sm">
                Cancel
            </button>
            <a href="{{ route('admin.users.toggle-status', $user->id) }}" class="btn btn-warning btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Deactivate
            </a>
        </div>
    </div>
</div>

<!-- ============================================================
ACTIVATE MODAL
============================================================ -->
<div id="activateModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-success">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirm Activation</h3>
        <p class="modal-text">
            Are you sure you want to <strong class="text-success">activate</strong> the account of <strong>{{ $user->name }}</strong> ?
            <br>
            The user will be able to login again.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeActivateModal()" class="btn btn-outline btn-sm">
                Cancel
            </button>
            <a href="{{ route('admin.users.toggle-status', $user->id) }}" class="btn btn-success btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Activate
            </a>
        </div>
    </div>
</div>

<!-- ============================================================
DELETE MODAL
============================================================ -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="modal-title">Confirm Deletion</h3>
        <p class="modal-text">
            Are you sure you want to <strong class="text-danger">permanently delete</strong> <strong>{{ $user->name }}</strong> ?
            <br>
            This action is <strong class="text-danger">irreversible</strong> and all data will be lost.
        </p>
        <div class="modal-actions">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm">
                Cancel
            </button>
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openDeactivateModal() {
    document.getElementById('deactivateModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeactivateModal() {
    document.getElementById('deactivateModal').classList.remove('active');
    document.body.style.overflow = '';
}

function openActivateModal() {
    document.getElementById('activateModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeActivateModal() {
    document.getElementById('activateModal').classList.remove('active');
    document.body.style.overflow = '';
}

function openDeleteModal() {
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
</script>
@endpush
@endsection
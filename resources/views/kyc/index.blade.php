@extends('layouts.app')

@push('styles')
<style>
    .kyc-status-card {
        transition: all 0.3s ease;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
    }
    .kyc-status-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .kyc-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    .kyc-status-badge-not_submitted {
        background: rgba(156,163,175,0.15);
        color: #9ca3af;
    }
    .kyc-status-badge-pending {
        background: rgba(245,158,11,0.15);
        color: #f59e0b;
    }
    .kyc-status-badge-partial {
        background: rgba(59,130,246,0.15);
        color: #3b82f6;
    }
    .kyc-status-badge-verified {
        background: rgba(34,197,94,0.15);
        color: #22c55e;
    }
    .kyc-status-badge-rejected {
        background: rgba(239,68,68,0.15);
        color: #ef4444;
    }
    
    .document-card {
        transition: all 0.3s ease;
        cursor: default;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
    }
    .document-card:hover {
        border-color: var(--primary-500);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .document-card .doc-icon svg {
        width: 2.5rem;
        height: 2.5rem;
        margin: 0 auto 0.5rem;
        color: var(--text-primary);
    }
    .document-card h4 {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .document-card p {
        font-size: 0.7rem;
        color: var(--text-secondary);
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-warning {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .badge-neutral {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
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
        padding: 0.375rem 1rem;
        font-size: 0.75rem;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.75rem;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }
    .delay-6 { animation-delay: 0.30s; }
    
    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .kyc-status-card { padding: 0.75rem; }
        .kyc-status-card .text-2xl { font-size: 1.25rem; }
        .document-card { padding: 0.75rem; }
        .document-card .doc-icon svg { width: 2rem; height: 2rem; }
        .badge { font-size: 0.55rem; padding: 0.125rem 0.5rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.875rem; }
        .info-grid {
            grid-template-columns: 1fr !important;
        }
        .doc-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .kyc-status-badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.75rem;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
    
    @media (max-width: 480px) {
        .card { padding: 0.75rem; }
        .doc-grid {
            grid-template-columns: 1fr !important;
        }
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .document-card .doc-icon svg { width: 1.75rem; height: 1.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">KYC Verification</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Verify your identity to secure your account</p>
        </div>
        <a href="{{ route('kyc.create') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Submit Document
        </a>
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

    <!-- KYC Status -->
    <div class="stats-grid grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        
        <div class="kyc-status-card border-l-4 border-primary-500">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">KYC Status</p>
            <div class="mt-1 sm:mt-2">
                <span class="kyc-status-badge kyc-status-badge-{{ $user->kyc_status ?? 'not_submitted' }}">
                    @if($user->kyc_status == 'not_submitted')
                        Not Submitted
                    @elseif($user->kyc_status == 'pending')
                        Pending
                    @elseif($user->kyc_status == 'partial')
                        Partial
                    @elseif($user->kyc_status == 'verified')
                        Verified
                    @elseif($user->kyc_status == 'rejected')
                        Rejected
                    @else
                        Not Submitted
                    @endif
                </span>
            </div>
        </div>
        
        <div class="kyc-status-card border-l-4 border-blue-500 animate-fadeInUp delay-2">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Documents Submitted</p>
            <p class="text-2xl sm:text-3xl font-bold text-blue-500">{{ $documents->count() }}</p>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">
                {{ $documents->where('status', 'verified')->count() }} verified
            </p>
        </div>
        
        <div class="kyc-status-card border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Verification Level</p>
            <div class="mt-1 sm:mt-2 flex items-center gap-2">
                <div class="flex-1 h-1.5 sm:h-2 bg-[var(--bg-secondary)] rounded-full overflow-hidden">
                    @php
                        $progress = 0;
                        $required = ['id_card', 'proof_of_address'];
                        $verified = $documents->where('status', 'verified')->pluck('document_type')->toArray();
                        foreach ($required as $doc) {
                            if (in_array($doc, $verified)) $progress += 50;
                        }
                    @endphp
                    <div class="h-full bg-primary-500 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                </div>
                <span class="text-xs sm:text-sm font-semibold text-primary-500">{{ $progress }}%</span>
            </div>
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">
                @if($progress == 100)
                    Verification complete
                @else
                    Required: ID Card + Proof of Address
                @endif
            </p>
        </div>
    </div>

    <!-- Documents -->
    <div class="card animate-fadeInUp delay-4">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base">My Documents</h3>
            <span class="badge badge-neutral text-[10px] sm:text-xs">{{ $documents->count() }} document(s)</span>
        </div>

        @if($documents->count() > 0)
            <div class="doc-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                @foreach($documents as $doc)
                    <div class="document-card">
                        <span class="doc-icon">
                            @if($doc->document_type == 'id_card')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                            @elseif($doc->document_type == 'passport')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            @elseif($doc->document_type == 'proof_of_address')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            @elseif($doc->document_type == 'selfie')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @endif
                        </span>
                        <h4>{{ $doc->document_type_label }}</h4>
                        <p>{{ $doc->document_number ?? 'N/A' }}</p>
                        <p class="mt-1">{{ $doc->file_name }} ({{ number_format($doc->file_size / 1024, 1) }} KB)</p>
                        <div class="mt-2 sm:mt-3">
                            <span class="badge 
                                {{ $doc->status == 'pending' ? 'badge-warning' : 
                                   ($doc->status == 'verified' ? 'badge-success' : 
                                   ($doc->status == 'rejected' ? 'badge-danger' : 'badge-neutral')) }}">
                                {{ $doc->status_label }}
                            </span>
                        </div>
                        @if($doc->status == 'rejected' && $doc->rejection_reason)
                            <p class="text-[10px] sm:text-xs text-red-500 mt-2">
                                {{ $doc->rejection_reason }}
                            </p>
                        @endif
                        @if($doc->status == 'verified' && $doc->verified_at)
                            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-2">
                                Verified on {{ $doc->verified_at->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 sm:py-8 text-[var(--text-secondary)]">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h4 class="text-sm sm:text-base font-semibold text-[var(--text-primary)]">No documents submitted</h4>
                <p class="text-xs sm:text-sm mt-1">Submit your documents to verify your identity.</p>
                <a href="{{ route('kyc.create') }}" class="btn btn-primary btn-sm mt-3 sm:mt-4">
                    Submit Document
                </a>
            </div>
        @endif
    </div>

    <!-- Information -->
    <div class="card animate-fadeInUp delay-5 border-l-4 border-primary-500">
        <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-2 sm:mb-3">Why verify your identity?</h3>
        <div class="info-grid grid grid-cols-1 md:grid-cols-3 gap-2 sm:gap-4 text-xs sm:text-sm">
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <div>
                    <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">Security</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Protects your account from fraud</p>
                </div>
            </div>
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">Withdrawals</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Required for withdrawals over $5,000</p>
                </div>
            </div>
            <div class="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-[var(--bg-secondary)] rounded-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <div>
                    <p class="font-semibold text-[var(--text-primary)] text-xs sm:text-sm">Credibility</p>
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)]">Builds trust in your network</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-2 sm:gap-3 animate-fadeInUp delay-6">
        <a href="{{ route('kyc.create') }}" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Submit Document
        </a>
        <button onclick="window.print()" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>
</div>
@endsection
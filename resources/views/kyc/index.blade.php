@extends('layouts.app')

@push('styles')
<style>
    .kyc-status-card {
        transition: all 0.3s ease;
    }
    .kyc-status-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .kyc-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.5rem;
        border-radius: var(--radius-full);
        font-size: 0.875rem;
        font-weight: 600;
    }
    .kyc-status-badge-not_submitted { background: rgba(156,163,175,0.15); color: #9ca3af; }
    .kyc-status-badge-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .kyc-status-badge-partial { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .kyc-status-badge-verified { background: rgba(34,197,94,0.15); color: #22c55e; }
    .kyc-status-badge-rejected { background: rgba(239,68,68,0.15); color: #ef4444; }
    .document-card {
        transition: all 0.3s ease;
        cursor: default;
    }
    .document-card:hover {
        border-color: var(--primary-500);
    }
    .document-card .doc-icon {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">🪪 Vérification KYC</h1>
            <p class="text-[var(--text-secondary)] mt-1">Vérifiez votre identité pour sécuriser votre compte</p>
        </div>
        <a href="{{ route('kyc.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Soumettre un document
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 animate-fadeIn">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 animate-fadeIn">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- Statut KYC -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 animate-fadeInUp delay-1">
        <div class="kyc-status-card card-stats border-l-4 border-primary-500">
            <p class="text-sm text-[var(--text-secondary)]">Statut KYC</p>
            <div class="mt-2">
                <span class="kyc-status-badge kyc-status-badge-{{ $user->kyc_status ?? 'not_submitted' }}">
                    @if($user->kyc_status == 'not_submitted') 📤 Non soumis
                    @elseif($user->kyc_status == 'pending') ⏳ En attente
                    @elseif($user->kyc_status == 'partial') ⚠️ Partiel
                    @elseif($user->kyc_status == 'verified') ✅ Vérifié
                    @elseif($user->kyc_status == 'rejected') ❌ Rejeté
                    @else 📤 Non soumis
                    @endif
                </span>
            </div>
        </div>
        <div class="kyc-status-card card-stats border-l-4 border-blue-500 animate-fadeInUp delay-2">
            <p class="text-sm text-[var(--text-secondary)]">Documents soumis</p>
            <p class="text-3xl font-bold text-blue-500">{{ $documents->count() }}</p>
            <p class="text-xs text-[var(--text-secondary)]">Sur {{ $documents->where('status', 'verified')->count() }} vérifiés</p>
        </div>
        <div class="kyc-status-card card-stats border-l-4 border-green-500 animate-fadeInUp delay-3">
            <p class="text-sm text-[var(--text-secondary)]">Niveau de vérification</p>
            <div class="mt-2 flex items-center gap-2">
                <div class="flex-1 h-2 bg-[var(--bg-secondary)] rounded-full overflow-hidden">
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
                <span class="text-sm font-semibold text-primary-500">{{ $progress }}%</span>
            </div>
            <p class="text-xs text-[var(--text-secondary)] mt-1">
                @if($progress == 100) 🎉 Vérification complète
                @else Documents requis : Carte d'identité + Justificatif de domicile
                @endif
            </p>
        </div>
    </div>

    <!-- Documents soumis -->
    <div class="card animate-fadeInUp delay-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--text-primary)]">📄 Mes documents</h3>
            <span class="badge badge-neutral text-xs">{{ $documents->count() }} document(s)</span>
        </div>

        @if($documents->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($documents as $doc)
                    <div class="document-card card p-4 text-center">
                        <span class="doc-icon">
                            @if($doc->document_type == 'id_card') 🪪
                            @elseif($doc->document_type == 'passport') 📘
                            @elseif($doc->document_type == 'proof_of_address') 📬
                            @elseif($doc->document_type == 'selfie') 🤳
                            @else 📄
                            @endif
                        </span>
                        <h4 class="font-semibold text-[var(--text-primary)]">{{ $doc->document_type_label }}</h4>
                        <p class="text-sm text-[var(--text-secondary)]">
                            {{ $doc->document_number ?? 'N°: Non spécifié' }}
                        </p>
                        <p class="text-xs text-[var(--text-secondary)] mt-1">
                            {{ $doc->file_name }} ({{ number_format($doc->file_size / 1024, 1) }} KB)
                        </p>
                        <div class="mt-3">
                            <span class="badge {{ $doc->status == 'pending' ? 'badge-warning' : ($doc->status == 'verified' ? 'badge-success' : ($doc->status == 'rejected' ? 'badge-danger' : 'badge-neutral')) }}">
                                {{ $doc->status_label }}
                            </span>
                        </div>
                        @if($doc->status == 'rejected' && $doc->rejection_reason)
                            <p class="text-xs text-red-500 mt-2">
                                ❌ {{ $doc->rejection_reason }}
                            </p>
                        @endif
                        @if($doc->status == 'verified' && $doc->verified_at)
                            <p class="text-xs text-[var(--text-secondary)] mt-2">
                                ✅ Vérifié le {{ $doc->verified_at->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-[var(--text-secondary)]">
                <div class="text-6xl mb-4">📤</div>
                <h4 class="text-lg font-semibold text-[var(--text-primary)]">Aucun document soumis</h4>
                <p class="text-sm mt-1">Soumettez vos documents pour vérifier votre identité.</p>
                <a href="{{ route('kyc.create') }}" class="btn btn-primary btn-sm mt-4">
                    📤 Soumettre un document
                </a>
            </div>
        @endif
    </div>

    <!-- Informations utiles -->
    <div class="card animate-fadeInUp delay-5 border-l-4 border-primary-500">
        <h3 class="font-semibold text-[var(--text-primary)] mb-3">ℹ️ Pourquoi vérifier mon identité ?</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="flex items-start gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                <span class="text-2xl">🔒</span>
                <div>
                    <p class="font-semibold text-[var(--text-primary)]">Sécurité</p>
                    <p class="text-[var(--text-secondary)]">Protège votre compte contre les fraudes</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                <span class="text-2xl">💰</span>
                <div>
                    <p class="font-semibold text-[var(--text-primary)]">Retraits</p>
                    <p class="text-[var(--text-secondary)]">Nécessaire pour les retraits de plus de 5000$</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                <span class="text-2xl">🏆</span>
                <div>
                    <p class="font-semibold text-[var(--text-primary)]">Crédibilité</p>
                    <p class="text-[var(--text-secondary)]">Renforce la confiance de votre réseau</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-3 animate-fadeInUp delay-6">
        <a href="{{ route('kyc.create') }}" class="btn btn-primary btn-sm">
            📤 Soumettre un document
        </a>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            🖨️ Imprimer
        </button>
    </div>
</div>
@endsection
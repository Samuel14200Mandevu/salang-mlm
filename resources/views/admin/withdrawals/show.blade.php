@extends('admin.layouts.app')

@push('styles')
<style>
    @media (max-width: 640px) {
        .card { padding: 0.75rem; }
        .badge { font-size: 0.6rem; padding: 0.125rem 0.5rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Detail du Retrait #{{ $withdrawal->id }}</h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Informations completes sur la demande</p>
        </div>
        <a href="{{ route('admin.withdrawals') }}" class="btn btn-outline btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <!-- Informations generales -->
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Informations generales</h3>
            <div class="space-y-2 sm:space-y-3">
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">ID</span>
                    <span class="font-mono text-xs sm:text-sm">#{{ $withdrawal->id }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Utilisateur</span>
                    <span class="font-medium text-sm sm:text-base">{{ $withdrawal->user?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Email</span>
                    <span class="text-xs sm:text-sm">{{ $withdrawal->user?->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Montant</span>
                    <span class="font-bold text-primary-500 text-sm sm:text-base">${{ number_format($withdrawal->amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Frais (2.5%)</span>
                    <span class="text-red-500 text-xs sm:text-sm">${{ number_format($withdrawal->fee, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Montant net</span>
                    <span class="font-bold text-green-500 text-sm sm:text-base">${{ number_format($withdrawal->net_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Methode</span>
                    <span class="badge badge-info text-[10px] sm:text-xs">{{ ucfirst($withdrawal->method) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Statut</span>
                    <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'processing' ? 'badge-info' : 'badge-danger')) }} text-[10px] sm:text-xs">
                        {{ ucfirst($withdrawal->status) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Date de creation</span>
                    <span class="text-xs sm:text-sm">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Details paiement -->
        <div class="card p-3 sm:p-4 md:p-6">
            <h3 class="font-semibold text-[var(--text-primary)] text-sm sm:text-base mb-3 sm:mb-4">Details du paiement</h3>
            <div class="space-y-2 sm:space-y-3">
                @if($withdrawal->payment_address)
                <div class="border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Adresse de paiement</span>
                    <p class="font-mono text-xs sm:text-sm break-all mt-1">{{ $withdrawal->payment_address }}</p>
                </div>
                @endif

                @if($withdrawal->phone_number)
                <div class="border-b border-[var(--border-color)] pb-2">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Numero de telephone</span>
                    <p class="font-medium text-xs sm:text-sm mt-1">{{ $withdrawal->phone_number }}</p>
                </div>
                @endif

                @if($withdrawal->bank_details)
                <div>
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Coordonnees bancaires</span>
                    <p class="whitespace-pre-line text-xs sm:text-sm mt-1">{{ $withdrawal->bank_details }}</p>
                </div>
                @endif

                @if($withdrawal->notes)
                <div class="border-t border-[var(--border-color)] pt-3 mt-3">
                    <span class="text-xs sm:text-sm text-[var(--text-secondary)]">Notes</span>
                    <p class="text-xs sm:text-sm mt-1">{{ $withdrawal->notes }}</p>
                </div>
                @endif
            </div>

            @if($withdrawal->status == 'pending' || $withdrawal->status == 'processing')
            <div class="mt-4 pt-4 border-t border-[var(--border-color)] flex flex-col sm:flex-row gap-2 sm:gap-3">
                <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" class="w-full sm:flex-1">
                    @csrf
                    <button type="submit" class="btn btn-success w-full text-sm sm:text-base py-2 sm:py-2.5" onclick="return confirm('Approuver ce retrait ?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approuver
                    </button>
                </form>
                <button onclick="showRejectModal('{{ $withdrawal->id }}')" class="btn btn-danger w-full sm:flex-1 text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-[var(--bg-card)] rounded-xl shadow-2xl max-w-md w-full mx-3 sm:mx-4 p-4 sm:p-6 border border-[var(--border-color)]">
        <div class="text-center">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-red-500 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="text-lg sm:text-xl font-bold text-[var(--text-primary)]">Rejeter le retrait</h3>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-1 sm:mt-2">
                Veuillez indiquer la raison du rejet.
            </p>
        </div>

        <form id="rejectForm" method="POST">
            @csrf
            <div class="mt-3 sm:mt-4">
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Motif du rejet *
                </label>
                <textarea name="reason" rows="3" class="input text-sm sm:text-base" placeholder="Motif du rejet..." required></textarea>
            </div>
            <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                <button type="submit" class="btn btn-danger w-full sm:flex-1 text-sm sm:text-base py-2 sm:py-2.5">
                    Rejeter
                </button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline w-full sm:flex-1 text-sm sm:text-base py-2 sm:py-2.5">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal(withdrawalId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.withdrawals.reject", ["id" => ":id"]) }}'.replace(':id', withdrawalId);
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRejectModal();
});
</script>
@endpush
@endsection
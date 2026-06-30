@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📋 Détail du Retrait #{{ $withdrawal->id }}</h1>
            <p class="text-[var(--text-secondary)] mt-1">Informations complètes sur la demande</p>
        </div>
        <a href="{{ route('admin.withdrawals') }}" class="btn btn-outline btn-sm">
            ← Retour
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 animate-fadeInUp delay-1">
        <!-- Informations générales -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">📋 Informations générales</h3>
            <div class="space-y-3">
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">ID</span>
                    <span class="font-mono">#{{ $withdrawal->id }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Utilisateur</span>
                    <span class="font-medium">{{ $withdrawal->user?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Email</span>
                    <span>{{ $withdrawal->user?->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Montant</span>
                    <span class="font-bold text-primary-500">${{ number_format($withdrawal->amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Frais (2.5%)</span>
                    <span class="text-red-500">${{ number_format($withdrawal->fee, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Montant net</span>
                    <span class="font-bold text-green-500">${{ number_format($withdrawal->net_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Méthode</span>
                    <span class="badge badge-info">{{ ucfirst($withdrawal->method) }}</span>
                </div>
                <div class="flex justify-between border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Statut</span>
                    <span class="badge {{ $withdrawal->status == 'pending' ? 'badge-warning' : ($withdrawal->status == 'completed' ? 'badge-success' : ($withdrawal->status == 'processing' ? 'badge-info' : 'badge-danger')) }}">
                        {{ ucfirst($withdrawal->status) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--text-secondary)]">Date de création</span>
                    <span>{{ $withdrawal->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Détails paiement -->
        <div class="card">
            <h3 class="font-semibold text-[var(--text-primary)] mb-4">💳 Détails du paiement</h3>
            <div class="space-y-3">
                @if($withdrawal->payment_address)
                <div class="border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Adresse de paiement</span>
                    <p class="font-mono text-sm break-all mt-1">{{ $withdrawal->payment_address }}</p>
                </div>
                @endif

                @if($withdrawal->phone_number)
                <div class="border-b border-[var(--border-color)] pb-2">
                    <span class="text-[var(--text-secondary)]">Numéro de téléphone</span>
                    <p class="font-medium mt-1">{{ $withdrawal->phone_number }}</p>
                </div>
                @endif

                @if($withdrawal->bank_details)
                <div>
                    <span class="text-[var(--text-secondary)]">Coordonnées bancaires</span>
                    <p class="whitespace-pre-line mt-1">{{ $withdrawal->bank_details }}</p>
                </div>
                @endif

                @if($withdrawal->notes)
                <div class="border-t border-[var(--border-color)] pt-3 mt-3">
                    <span class="text-[var(--text-secondary)]">Notes</span>
                    <p class="mt-1">{{ $withdrawal->notes }}</p>
                </div>
                @endif
            </div>

            @if($withdrawal->status == 'pending' || $withdrawal->status == 'processing')
            <div class="mt-4 pt-4 border-t border-[var(--border-color)] flex gap-3">
                <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="btn btn-success w-full" onclick="return confirm('Approuver ce retrait ?')">
                        ✅ Approuver
                    </button>
                </form>
                <button onclick="showRejectModal('{{ $withdrawal->id }}')" class="btn btn-danger flex-1">
                    ❌ Rejeter
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-[var(--bg-card)] rounded-xl shadow-2xl max-w-md w-full p-6 border border-[var(--border-color)]">
        <div class="text-center">
            <div class="text-5xl mb-4">❌</div>
            <h3 class="text-xl font-bold text-[var(--text-primary)]">Rejeter le retrait</h3>
            <p class="text-sm text-[var(--text-secondary)] mt-2">
                Veuillez indiquer la raison du rejet.
            </p>
        </div>

        <form id="rejectForm" method="POST">
            @csrf
            <div class="mt-4">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Motif du rejet *
                </label>
                <textarea name="reason" rows="3" class="input" placeholder="Motif du rejet..." required></textarea>
            </div>
            <div class="mt-4 flex gap-3">
                <button type="submit" class="btn btn-danger flex-1">
                    ✅ Rejeter
                </button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline flex-1">
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
{{-- resources/views/cashier/customers.blade.php --}}
@extends('cashier.layouts.app')

@section('title', 'Clients POS')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                <span class="text-primary-500"></span> Clients POS
            </h1>
            <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                Liste des clients enregistrés au guichet
            </p>
        </div>
        <button onclick="openNewCustomerModal()" class="btn btn-primary btn-sm sm:btn-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau client
        </button>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 animate-fadeInUp delay-1">
        <div class="card-stats border-l-4 border-primary-500">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total clients</p>
            <p class="text-xl sm:text-2xl font-bold text-primary-500">{{ $customers->total() ?? 0 }}</p>
        </div>
        <div class="card-stats border-l-4 border-green-500 animate-fadeInUp delay-2">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Actifs</p>
            <p class="text-xl sm:text-2xl font-bold text-green-500">{{ $customers->where('is_active', true)->count() }}</p>
        </div>
        <div class="card-stats border-l-4 border-purple-500 animate-fadeInUp delay-3">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Avec parrain</p>
            <p class="text-xl sm:text-2xl font-bold text-purple-500">{{ $customers->whereNotNull('parrain_id')->count() }}</p>
        </div>
    </div>

    <!-- Liste -->
    <div class="card animate-fadeInUp delay-3">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-xs sm:text-sm">#</th>
                        <th class="text-xs sm:text-sm">Nom</th>
                        <th class="text-xs sm:text-sm hidden sm:table-cell">Téléphone</th>
                        <th class="text-xs sm:text-sm hidden md:table-cell">Parrain</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Code client</th>
                        <th class="text-xs sm:text-sm hidden lg:table-cell">Inscrit</th>
                        <th class="text-xs sm:text-sm">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers ?? [] as $customer)
                        <tr>
                            <td class="font-mono text-xs text-[var(--text-secondary)]">#{{ $customer->id }}</td>
                            <td>
                                <div class="font-medium text-sm sm:text-base">{{ $customer->name }}</div>
                                <div class="text-xs text-[var(--text-secondary)]">{{ $customer->email }}</div>
                            </td>
                            <td class="hidden sm:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $customer->phone ?? 'N/A' }}
                            </td>
                            <td class="hidden md:table-cell">
                                @if($customer->parrain)
                                    <span class="text-sm text-primary-500">{{ $customer->parrain->name }}</span>
                                    <div class="text-xs text-[var(--text-secondary)]">Code: {{ $customer->parrain->sponsor_id }}</div>
                                @else
                                    <span class="text-xs text-[var(--text-tertiary)]">Aucun</span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell font-mono text-xs text-[var(--text-secondary)]">
                                {{ $customer->sponsor_id ?? 'N/A' }}
                            </td>
                            <td class="hidden lg:table-cell text-[var(--text-secondary)] text-xs sm:text-sm">
                                {{ $customer->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $customer->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 sm:py-8 text-[var(--text-secondary)] text-sm sm:text-base">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-[var(--text-tertiary)] mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-base sm:text-lg font-medium">Aucun client POS</p>
                                <p class="text-sm text-[var(--text-tertiary)]">Les clients apparaîtront ici après leur première vente au guichet</p>
                                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-sm mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Faire une vente
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($customers) && $customers->hasPages())
            <div class="mt-3 sm:mt-4">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Nouveau Client -->
<div id="customerModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-[var(--bg-card)] rounded-lg p-6 max-w-md w-full border border-[var(--border-color)]">
        <h3 class="text-lg font-bold text-[var(--text-primary)] mb-4">
            <svg class="inline-block w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Nouveau client POS
        </h3>
        <form id="customerFormModal">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" id="newNameModal" placeholder="Jean Dupont" class="input" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="newEmailModal" placeholder="client@email.com" class="input" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                    <input type="text" id="newPhoneModal" placeholder="+225 07 00 00 00 00" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                    <input type="text" id="newAddressModal" placeholder="Adresse complète" class="input">
                </div>
                <div class="text-xs text-[var(--text-secondary)]">
                    <span class="text-yellow-500">ℹ️</span> Le client pourra être parrainé lors de sa première vente
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer
                </button>
                <button type="button" onclick="closeModal('customerModal')" class="btn btn-outline flex-1">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openNewCustomerModal() {
    document.getElementById('customerModal').classList.add('flex');
    document.getElementById('customerModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

document.getElementById('customerFormModal').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const data = {
        name: document.getElementById('newNameModal').value,
        email: document.getElementById('newEmailModal').value,
        phone: document.getElementById('newPhoneModal').value,
        address: document.getElementById('newAddressModal').value,
    };

    fetch('/cashier/customers', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            toast('✅ Client créé avec succès !', 'success');
            closeModal('customerModal');
            document.getElementById('customerFormModal').reset();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            toast('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        toast('❌ Erreur: ' + error.message, 'error');
    });
});
</script>
@endpush
@endsection
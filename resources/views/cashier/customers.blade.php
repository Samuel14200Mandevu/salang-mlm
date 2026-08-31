{{-- resources/views/cashier/customers.blade.php --}}
@extends('cashier.layouts.app')

@section('title', 'Clients POS')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                Clients POS
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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 animate-fadeInUp delay-1">
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
        <div class="card-stats border-l-4 border-orange-500 animate-fadeInUp delay-4">
            <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] uppercase tracking-wider">Parrains uniques</p>
            <p class="text-xl sm:text-2xl font-bold text-orange-500">{{ $customers->whereNotNull('parrain_id')->unique('parrain_id')->count() }}</p>
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
                                @if($customer->address)
                                    <div class="text-[10px] text-[var(--text-tertiary)]">{{ $customer->address }}{{ $customer->city ? ', ' . $customer->city : '' }}</div>
                                @endif
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
<div id="customerModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" onclick="if(event.target === this) closeModal('customerModal')">
    <div class="bg-[var(--bg-card)] rounded-lg p-6 max-w-md w-full border border-[var(--border-color)] shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--text-primary)]">
                <svg class="inline-block w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Nouveau client POS
            </h3>
            <button onclick="closeModal('customerModal')" class="text-[var(--text-tertiary)] hover:text-[var(--text-primary)] transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="customerFormModal">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" id="newNameModal" class="input" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="newEmailModal"  class="input" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Téléphone</label>
                    <input type="text" id="newPhoneModal"  class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Adresse</label>
                    <input type="text" id="newAddressModal"  class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Ville</label>
                    <input type="text" id="newCityModal" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Pays</label>
                    <input type="text" id="newCountryModal"  class="input">
                </div>
                <div class="text-xs text-[var(--text-secondary)] bg-blue-500/5 p-2 rounded-lg border border-blue-500/10">
                    <span class="text-yellow-500"></span> Le client pourra être parrainé lors de sa première vente
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

@push('styles')
<style>
    .card-stats {
        background: var(--bg-card);
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .card-stats p:last-child {
        margin-bottom: 0;
    }
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        min-width: 480px;
    }
    .table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-secondary);
    }
    .table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .table-striped tbody tr:nth-child(even) {
        background: var(--bg-secondary);
    }
    .table-striped tbody tr:hover {
        background: var(--bg-hover);
    }
    .badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
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
    .flex-1 {
        flex: 1;
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    @media (max-width: 640px) {
        .card-stats {
            padding: 0.75rem;
        }
        .card-stats .text-2xl {
            font-size: 1.25rem;
        }
        .card {
            padding: 0.875rem;
        }
        .table th,
        .table td {
            padding: 0.375rem 0.5rem;
            font-size: 0.65rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function openNewCustomerModal() {
    const modal = document.getElementById('customerModal');
    modal.classList.add('flex');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
    document.getElementById('customerFormModal').reset();
}

document.getElementById('customerFormModal').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Création...
    `;
    
    const data = {
        name: document.getElementById('newNameModal').value,
        email: document.getElementById('newEmailModal').value,
        phone: document.getElementById('newPhoneModal').value,
        address: document.getElementById('newAddressModal').value,
        city: document.getElementById('newCityModal')?.value,
        country: document.getElementById('newCountryModal')?.value,
    };

    fetch('{{ route('cashier.customers.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showToast(' Client créé avec succès !', 'success');
            closeModal('customerModal');
            document.getElementById('customerFormModal').reset();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            window.showToast(' Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        window.showToast(' Erreur: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Créer
        `;
    });
});

// Fermer la modal avec la touche Echap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('customerModal');
    }
});
</script>
@endpush
@endsection
@extends('cashier.layouts.app')

@section('title', 'Nouvelle dépense')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Nouvelle dépense</h1>
        <a href="{{ route('cashier.expenses.index') }}" class="btn btn-outline">← Retour</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-3 text-red-800 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cashier.expenses.store') }}" enctype="multipart/form-data"
          class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-[var(--radius-md)] p-4 sm:p-6 space-y-4">
        @csrf

        {{-- Type --}}
        <div>
            <label class="form-label">Type de dépense *</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="expense_type" value="caisse" class="peer sr-only"
                           {{ old('expense_type', 'caisse') === 'caisse' ? 'checked' : '' }}>
                    <div class="border-2 border-[var(--border-color)] rounded-md p-3 text-center peer-checked:border-[var(--primary)] peer-checked:bg-blue-50 transition-all">
                        <span class="text-sm font-semibold">Caisse</span>
                        <p class="text-xs text-[var(--text-tertiary)]">Fournitures, transport...</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="expense_type" value="membre" class="peer sr-only"
                           {{ old('expense_type') === 'membre' ? 'checked' : '' }}>
                    <div class="border-2 border-[var(--border-color)] rounded-md p-3 text-center peer-checked:border-[var(--primary)] peer-checked:bg-blue-50 transition-all">
                        <span class="text-sm font-semibold">Membre</span>
                        <p class="text-xs text-[var(--text-tertiary)]">Remboursement, avance...</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Membre lié --}}
        <div id="relatedUserBlock" class="hidden">
            <label class="form-label">Membre / Client *</label>
            <select name="related_user_id" id="related_user_id" class="form-control">
                <option value="">-- Sélectionner un membre --</option>
            </select>
            <p class="text-xs text-[var(--text-tertiary)] mt-1">Tapez 2 lettres pour rechercher</p>
        </div>

        {{-- Catégorie --}}
        <div>
            <label class="form-label">Catégorie *</label>
            <select name="category" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Titre --}}
        <div>
            <label class="form-label">Titre *</label>
            <input type="text" name="title" value="{{ old('title') }}" class="form-control"
                   placeholder="Ex: Achat stylos pour bureau" required maxlength="255">
        </div>

        {{-- Description --}}
        <div>
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control" maxlength="2000"
                      placeholder="Détails supplémentaires...">{{ old('description') }}</textarea>
        </div>

        {{-- Montant + Devise --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label class="form-label">Montant *</label>
                <input type="number" name="amount" value="{{ old('amount') }}" class="form-control"
                       step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div>
                <label class="form-label">Devise *</label>
                <select name="currency" class="form-control" required>
                    @foreach($currencies as $key => $label)
                        <option value="{{ $key }}" {{ old('currency', 'USD') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Date + Paiement --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="form-label">Date *</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}"
                       class="form-control" required>
            </div>
            <div>
                <label class="form-label">Mode de paiement *</label>
                <select name="payment_method" class="form-control" required>
                    @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_method', 'cash') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Référence --}}
        <div>
            <label class="form-label">Référence / N° reçu</label>
            <input type="text" name="reference" value="{{ old('reference') }}" class="form-control"
                   maxlength="100" placeholder="Ex: FACT-001">
        </div>

        {{-- Justificatif --}}
        <div>
            <label class="form-label">Justificatif (photo ou PDF)</label>
            <input type="file" name="receipt_image" accept="image/*,application/pdf"
                   class="form-control">
            <p class="text-xs text-[var(--text-tertiary)] mt-1">Optionnel - Max 5 MB</p>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-2 pt-3 border-t border-[var(--border-color)]">
            <a href="{{ route('cashier.expenses.index') }}" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="expense_type"]');
    const relatedBlock = document.getElementById('relatedUserBlock');
    const relatedSelect = document.getElementById('related_user_id');

    function toggleRelatedBlock() {
        const selected = document.querySelector('input[name="expense_type"]:checked')?.value;
        if (selected === 'membre') {
            relatedBlock.classList.remove('hidden');
            relatedSelect.required = true;
        } else {
            relatedBlock.classList.add('hidden');
            relatedSelect.required = false;
        }
    }

    typeRadios.forEach(radio => radio.addEventListener('change', toggleRelatedBlock));
    toggleRelatedBlock();
});
</script>
@endsection
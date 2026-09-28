@extends('cashier.layouts.app')

@section('title', 'Modifier la dépense')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Modifier la dépense</h1>
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

    <form method="POST" action="{{ route('cashier.expenses.update', $expense->id) }}"
          enctype="multipart/form-data"
          class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-[var(--radius-md)] p-4 sm:p-6 space-y-4">
        @csrf
        @method('PUT')

        {{-- Type --}}
        <div>
            <label class="form-label">Type de dépense *</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="expense_type" value="caisse" class="peer sr-only"
                           {{ old('expense_type', $expense->expense_type) === 'caisse' ? 'checked' : '' }}>
                    <div class="border-2 border-[var(--border-color)] rounded-md p-3 text-center peer-checked:border-[var(--primary)] peer-checked:bg-blue-50">
                        <span class="text-sm font-semibold">Caisse</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="expense_type" value="membre" class="peer sr-only"
                           {{ old('expense_type', $expense->expense_type) === 'membre' ? 'checked' : '' }}>
                    <div class="border-2 border-[var(--border-color)] rounded-md p-3 text-center peer-checked:border-[var(--primary)] peer-checked:bg-blue-50">
                        <span class="text-sm font-semibold">Membre</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Membre lié --}}
        <div id="relatedUserBlock" class="{{ old('expense_type', $expense->expense_type) === 'membre' ? '' : 'hidden' }}">
            <label class="form-label">Membre / Client *</label>
            <select name="related_user_id" class="form-control">
                <option value="">-- Sélectionner --</option>
                @if($expense->relatedUser)
                    <option value="{{ $expense->relatedUser->id }}" selected>
                        {{ $expense->relatedUser->name }} ({{ $expense->relatedUser->sponsor_id }})
                    </option>
                @endif
            </select>
        </div>

        {{-- Catégorie --}}
        <div>
            <label class="form-label">Catégorie *</label>
            <select name="category" class="form-control" required>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ old('category', $expense->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Titre --}}
        <div>
            <label class="form-label">Titre *</label>
            <input type="text" name="title" value="{{ old('title', $expense->title) }}" class="form-control" required>
        </div>

        {{-- Description --}}
        <div>
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $expense->description) }}</textarea>
        </div>

        {{-- Montant + Devise --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label class="form-label">Montant *</label>
                <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}"
                       class="form-control" step="0.01" min="0.01" required>
            </div>
            <div>
                <label class="form-label">Devise *</label>
                <select name="currency" class="form-control" required>
                    @foreach($currencies as $key => $label)
                        <option value="{{ $key }}" {{ old('currency', $expense->currency) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Date + Paiement --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="form-label">Date *</label>
                <input type="date" name="expense_date"
                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                       class="form-control" required>
            </div>
            <div>
                <label class="form-label">Mode de paiement *</label>
                <select name="payment_method" class="form-control" required>
                    @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_method', $expense->payment_method) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Référence --}}
        <div>
            <label class="form-label">Référence</label>
            <input type="text" name="reference" value="{{ old('reference', $expense->reference) }}" class="form-control">
        </div>

        {{-- Justificatif actuel --}}
        @if($expense->receipt_image)
            <div>
                <label class="form-label">Justificatif actuel</label>
                <a href="{{ $expense->receipt_url }}" target="_blank" class="text-[var(--primary)] underline text-sm">
                    📎 Voir le justificatif
                </a>
            </div>
        @endif

        {{-- Nouveau justificatif --}}
        <div>
            <label class="form-label">Remplacer le justificatif (optionnel)</label>
            <input type="file" name="receipt_image" accept="image/*,application/pdf" class="form-control">
        </div>

        {{-- Actions --}}
        <div class="flex justify-between gap-2 pt-3 border-t border-[var(--border-color)]">
            <form method="POST" action="{{ route('cashier.expenses.destroy', $expense->id) }}"
                  onsubmit="return confirm('Supprimer cette dépense ?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('cashier.expenses.index') }}" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="expense_type"]');
    const relatedBlock = document.getElementById('relatedUserBlock');

    function toggleRelatedBlock() {
        const selected = document.querySelector('input[name="expense_type"]:checked')?.value;
        if (selected === 'membre') {
            relatedBlock.classList.remove('hidden');
        } else {
            relatedBlock.classList.add('hidden');
        }
    }

    typeRadios.forEach(radio => radio.addEventListener('change', toggleRelatedBlock));
});
</script>
@endsection
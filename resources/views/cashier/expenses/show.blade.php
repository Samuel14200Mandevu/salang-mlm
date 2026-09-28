@extends('cashier.layouts.app')

@section('title', 'Détails de la dépense')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Détails de la dépense</h1>
        <div class="flex gap-2">
            <a href="{{ route('cashier.expenses.edit', $expense->id) }}" class="btn btn-outline">Modifier</a>
            <a href="{{ route('cashier.expenses.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-[var(--radius-md)] p-4 sm:p-6 space-y-4">

        <div class="flex items-center justify-between pb-4 border-b border-[var(--border-color)]">
            <div>
                <span class="badge {{ $expense->expense_type === 'caisse' ? 'badge-info' : 'badge-warning' }}">
                    {{ $expense->expense_type_label }}
                </span>
                <span class="badge {{ $expense->status_badge_class }} ml-2">
                    {{ $expense->status_label }}
                </span>
            </div>
            <div class="text-right">
                <p class="text-xs text-[var(--text-tertiary)]">Montant</p>
                <p class="text-2xl font-bold text-[var(--danger)]">{{ $expense->formatted_amount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Date</p>
                <p class="text-sm">{{ $expense->expense_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Catégorie</p>
                <p class="text-sm">{{ $expense->category_label }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Titre</p>
                <p class="text-sm">{{ $expense->title }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Mode de paiement</p>
                <p class="text-sm">{{ $expense->payment_method_label }}</p>
            </div>
            @if($expense->reference)
                <div>
                    <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Référence</p>
                    <p class="text-sm">{{ $expense->reference }}</p>
                </div>
            @endif
            @if($expense->relatedUser)
                <div>
                    <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Membre lié</p>
                    <p class="text-sm">{{ $expense->relatedUser->name }} ({{ $expense->relatedUser->sponsor_id }})</p>
                </div>
            @endif
            <div>
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Enregistré par</p>
                <p class="text-sm">{{ $expense->user->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold">Date d'enregistrement</p>
                <p class="text-sm">{{ $expense->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if($expense->description)
            <div class="pt-4 border-t border-[var(--border-color)]">
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold mb-1">Description</p>
                <p class="text-sm text-[var(--text-secondary)] whitespace-pre-line">{{ $expense->description }}</p>
            </div>
        @endif

        @if($expense->receipt_image)
            <div class="pt-4 border-t border-[var(--border-color)]">
                <p class="text-xs text-[var(--text-tertiary)] uppercase font-semibold mb-2">Justificatif</p>
                @if(Str::endsWith($expense->receipt_image, ['.jpg', '.jpeg', '.png']))
                    <img src="{{ $expense->receipt_url }}" alt="Justificatif" class="max-w-full rounded-md border border-[var(--border-color)]">
                @else
                    <a href="{{ $expense->receipt_url }}" target="_blank" class="btn btn-outline">
                        📎 Voir le justificatif
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
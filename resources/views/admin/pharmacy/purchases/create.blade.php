@extends('admin.layouts.app')

@section('title', 'Nouvel achat pharmacie')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Nouvel achat',
    'subtitle' => 'Réception fournisseur → stock',
    'actions' => '<a href="'.route('admin.pharmacy.purchases.index').'" class="btn btn-outline btn-sm">Retour</a>',
])

@include('admin.pharmacy.partials.nav')

@if($suppliers->isEmpty())
    <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 mb-4 text-sm">
        Aucun fournisseur. <a class="underline text-[var(--primary)]" href="{{ route('admin.pharmacy.registers.index', ['tab' => 'suppliers']) }}">Créer un fournisseur</a> d’abord.
    </div>
@endif

<form method="POST" action="{{ route('admin.pharmacy.purchases.store') }}" class="space-y-4" x-data="purchaseForm()">
    @csrf
    <div class="grid md:grid-cols-3 gap-3 rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
        <div>
            <label class="form-label">Date *</label>
            <input type="date" name="purchased_at" value="{{ old('purchased_at', now()->toDateString()) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">N° facture</label>
            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Fournisseur *</label>
            <select name="supplier_id" class="form-input" required>
                <option value="">—</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Montant payé</label>
            <input type="number" step="0.01" min="0" name="paid_amount" value="{{ old('paid_amount', 0) }}" class="form-input">
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" value="{{ old('notes') }}" class="form-input">
        </div>
    </div>

    <div class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold">Lignes</h2>
            <button type="button" class="btn btn-outline btn-sm" @click="addLine()">+ Ligne</button>
        </div>
        <template x-for="(line, index) in lines" :key="index">
            <div class="grid md:grid-cols-4 gap-2 mb-2">
                <select :name="`items[${index}][product_id]`" class="form-input md:col-span-2" x-model="line.product_id" @change="onProduct(index)" required>
                    <option value="">Produit</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-cost="{{ $p->cost }}">{{ $p->name }} ({{ $p->sku ?: 'n/a' }} · stock {{ $p->stock }})</option>
                    @endforeach
                </select>
                <input type="number" min="1" :name="`items[${index}][quantity]`" class="form-input" x-model.number="line.quantity" required placeholder="Qté">
                <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" class="form-input" x-model.number="line.unit_cost" required placeholder="Coût unit.">
            </div>
        </template>
        <p class="text-sm text-[var(--text-secondary)] mt-2">Total estimé : <span class="font-semibold" x-text="total().toFixed(2) + ' $'"></span></p>
    </div>

    <button type="submit" class="btn btn-primary" @if($suppliers->isEmpty()) disabled @endif>Enregistrer l’achat</button>
</form>

@push('scripts')
<script>
function purchaseForm() {
    return {
        lines: [{ product_id: '', quantity: 1, unit_cost: 0 }],
        addLine() { this.lines.push({ product_id: '', quantity: 1, unit_cost: 0 }); },
        onProduct(i) {
            const sel = document.querySelector(`select[name="items[${i}][product_id]"]`);
            const opt = sel?.selectedOptions?.[0];
            if (opt?.dataset?.cost) this.lines[i].unit_cost = parseFloat(opt.dataset.cost) || 0;
        },
        total() {
            return this.lines.reduce((s, l) => s + (Number(l.quantity) || 0) * (Number(l.unit_cost) || 0), 0);
        }
    }
}
</script>
@endpush
@endsection

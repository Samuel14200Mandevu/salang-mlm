@extends('admin.layouts.app')

@section('title', 'Nouvelle vente pharmacie')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Nouvelle vente',
    'subtitle' => 'Décrémente le stock · journal caisse',
    'actions' => '<a href="'.route('admin.pharmacy.sales.index').'" class="btn btn-outline btn-sm">Retour</a>',
])

@include('admin.pharmacy.partials.nav')

<form method="POST" action="{{ route('admin.pharmacy.sales.store') }}" class="space-y-4"
      x-data="saleForm(@js($insurers->mapWithKeys(fn($i) => [(string)$i->id => (float)$i->coverage_rate])))">
    @csrf
    <div class="grid md:grid-cols-3 gap-3 rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4">
        <div>
            <label class="form-label">Date *</label>
            <input type="date" name="sold_at" value="{{ old('sold_at', now()->toDateString()) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Patient</label>
            <select name="patient_id" class="form-input" x-model="patientId" @change="onPatient()">
                <option value="">— passage / anonyme —</option>
                @foreach($patients as $p)
                    <option value="{{ $p->id }}"
                            data-insurer="{{ $p->insurer_id }}"
                            data-rate="{{ $p->insurer?->coverage_rate ?? 0 }}"
                            @selected(old('patient_id') == $p->id)>
                        {{ $p->name }}@if($p->insurer) ({{ $p->insurer->name }})@endif
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Nom client (si pas patient)</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Paiement *</label>
            <select name="payment_method" class="form-input" x-model="method" required>
                @foreach(\App\Models\Pharmacy\Sale::PAYMENT_METHODS as $k => $label)
                    <option value="{{ $k }}" @selected(old('payment_method', 'cash') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="method === 'insurance'" x-cloak>
            <label class="form-label">Assurance</label>
            <select name="insurer_id" class="form-input" x-model="insurerId" @change="syncRate()">
                <option value="">—</option>
                @foreach($insurers as $i)
                    <option value="{{ $i->id }}" @selected(old('insurer_id') == $i->id)>{{ $i->name }} ({{ number_format($i->coverage_rate * 100, 0) }}%)</option>
                @endforeach
            </select>
        </div>
        <div x-show="method === 'insurance'" x-cloak>
            <label class="form-label">Taux couverture (0–1)</label>
            <input type="number" step="0.01" min="0" max="1" name="coverage_rate" class="form-input" x-model.number="coverage">
        </div>
        <div class="md:col-span-3">
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
                        <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-stock="{{ $p->stock }}">
                            {{ $p->name }} (stock {{ $p->stock }} · {{ number_format($p->price, 2) }} $)
                        </option>
                    @endforeach
                </select>
                <input type="number" min="1" :name="`items[${index}][quantity]`" class="form-input" x-model.number="line.quantity" required>
                <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" class="form-input" x-model.number="line.unit_price" required>
            </div>
        </template>
        <div class="text-sm text-[var(--text-secondary)] mt-2 space-y-1">
            <p>Total : <strong x-text="subtotal().toFixed(2) + ' $'"></strong></p>
            <p x-show="method === 'insurance'">Part assurance : <strong x-text="insShare().toFixed(2) + ' $'"></strong>
               · Part patient : <strong x-text="patShare().toFixed(2) + ' $'"></strong></p>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Enregistrer la vente</button>
</form>

@push('scripts')
<script>
function saleForm(rates) {
    return {
        method: @js(old('payment_method', 'cash')),
        insurerId: @js((string) old('insurer_id', '')),
        coverage: @js((float) old('coverage_rate', 0)),
        patientId: @js((string) old('patient_id', '')),
        rates: rates || {},
        lines: [{ product_id: '', quantity: 1, unit_price: 0 }],
        addLine() { this.lines.push({ product_id: '', quantity: 1, unit_price: 0 }); },
        onProduct(i) {
            const sel = document.querySelector(`select[name="items[${i}][product_id]"]`);
            const opt = sel?.selectedOptions?.[0];
            if (opt?.dataset?.price) this.lines[i].unit_price = parseFloat(opt.dataset.price) || 0;
        },
        onPatient() {
            const sel = document.querySelector('select[name="patient_id"]');
            const opt = sel?.selectedOptions?.[0];
            if (opt?.dataset?.insurer) {
                this.insurerId = opt.dataset.insurer;
                this.coverage = parseFloat(opt.dataset.rate) || 0;
                if (this.insurerId) this.method = 'insurance';
            }
        },
        syncRate() {
            if (this.rates[this.insurerId] !== undefined) {
                this.coverage = Number(this.rates[this.insurerId]) || 0;
            }
        },
        subtotal() {
            return this.lines.reduce((s, l) => s + (Number(l.quantity) || 0) * (Number(l.unit_price) || 0), 0);
        },
        insShare() { return this.method === 'insurance' ? this.subtotal() * (Number(this.coverage) || 0) : 0; },
        patShare() { return this.subtotal() - this.insShare(); },
    }
}
</script>
@endpush
@endsection

@extends('admin.layouts.app')

@section('title', 'Pharmacie — Registres')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Registres',
    'subtitle' => 'Fournisseurs & patients',
])

@include('admin.pharmacy.partials.nav')

<div class="flex gap-2 mb-4">
    <a href="{{ route('admin.pharmacy.registers.index', ['tab' => 'suppliers']) }}"
       class="px-3 py-1.5 rounded-lg text-sm border {{ $tab === 'suppliers' ? 'bg-[var(--primary)] text-white border-[var(--primary)]' : 'border-[var(--border-color)]' }}">
        Fournisseurs (dettes {{ number_format($supplierDebts, 2) }} $)
    </a>
    <a href="{{ route('admin.pharmacy.registers.index', ['tab' => 'patients']) }}"
       class="px-3 py-1.5 rounded-lg text-sm border {{ $tab === 'patients' ? 'bg-[var(--primary)] text-white border-[var(--primary)]' : 'border-[var(--border-color)]' }}">
        Patients (CA {{ number_format($patientSales, 2) }} $)
    </a>
</div>

@if($tab === 'suppliers')
    <div class="grid lg:grid-cols-3 gap-4">
        <form method="POST" action="{{ route('admin.pharmacy.registers.suppliers.store') }}" class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3 h-fit">
            @csrf
            <h2 class="font-semibold">Nouveau fournisseur</h2>
            <input type="text" name="name" placeholder="Nom *" class="form-input" required>
            <input type="text" name="contact" placeholder="Contact" class="form-input">
            <input type="text" name="phone" placeholder="Téléphone" class="form-input">
            <input type="text" name="address" placeholder="Adresse" class="form-input">
            <button class="btn btn-primary btn-sm" type="submit">Ajouter</button>
        </form>
        <div class="lg:col-span-2 rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                        <th class="px-4 py-2">Nom</th>
                        <th class="px-4 py-2">Contact</th>
                        <th class="px-4 py-2 text-right">Dette</th>
                        <th class="px-4 py-2">Actif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr class="border-t border-[var(--border-light)]">
                            <td class="px-4 py-2 font-medium">{{ $supplier->name }}</td>
                            <td class="px-4 py-2">{{ $supplier->phone ?: $supplier->contact ?: '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($supplier->debt, 2) }} $</td>
                            <td class="px-4 py-2">{{ $supplier->is_active ? 'Oui' : 'Non' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-[var(--text-secondary)]">Aucun fournisseur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="grid lg:grid-cols-3 gap-4">
        <form method="POST" action="{{ route('admin.pharmacy.registers.patients.store') }}" class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3 h-fit">
            @csrf
            <h2 class="font-semibold">Nouveau patient</h2>
            <input type="text" name="name" placeholder="Nom *" class="form-input" required>
            <input type="text" name="phone" placeholder="Téléphone" class="form-input">
            <select name="insurer_id" class="form-input">
                <option value="">Sans assurance</option>
                @foreach($insurers as $i)
                    <option value="{{ $i->id }}">{{ $i->name }} ({{ number_format($i->coverage_rate * 100, 0) }}%)</option>
                @endforeach
            </select>
            <input type="text" name="member_number" placeholder="N° affilié" class="form-input">
            <button class="btn btn-primary btn-sm" type="submit">Ajouter</button>
        </form>
        <div class="lg:col-span-2 rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                        <th class="px-4 py-2">Nom</th>
                        <th class="px-4 py-2">Assurance</th>
                        <th class="px-4 py-2 text-right">Achats</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr class="border-t border-[var(--border-light)]">
                            <td class="px-4 py-2 font-medium">{{ $patient->name }}</td>
                            <td class="px-4 py-2">{{ $patient->insurer?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($patient->spent, 2) }} $</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-[var(--text-secondary)]">Aucun patient.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

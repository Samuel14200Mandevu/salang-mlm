@extends('admin.layouts.app')

@section('title', 'Pharmacie — Assurances')

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Assurances / mutuelles',
    'subtitle' => 'Créances facturées : '.number_format($totalBilled, 2).' $',
])

@include('admin.pharmacy.partials.nav')

<div class="grid lg:grid-cols-3 gap-4">
    <form method="POST" action="{{ route('admin.pharmacy.insurers.store') }}" class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] p-4 space-y-3 h-fit">
        @csrf
        <h2 class="font-semibold">Ajouter</h2>
        <input type="text" name="name" placeholder="Nom *" class="form-input" required>
        <input type="number" step="0.01" min="0" max="1" name="coverage_rate" placeholder="Taux (ex 0.80)" class="form-input" required>
        <input type="text" name="contact" placeholder="Contact" class="form-input">
        <input type="text" name="notes" placeholder="Notes" class="form-input">
        <button class="btn btn-primary btn-sm" type="submit">Enregistrer</button>
    </form>

    <div class="lg:col-span-2 rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-[var(--text-tertiary)] bg-[var(--bg-secondary)]">
                    <th class="px-4 py-2">Nom</th>
                    <th class="px-4 py-2">Taux</th>
                    <th class="px-4 py-2">Patients</th>
                    <th class="px-4 py-2 text-right">Facturé</th>
                    <th class="px-4 py-2">Actif</th>
                </tr>
            </thead>
            <tbody>
                @forelse($insurers as $insurer)
                    <tr class="border-t border-[var(--border-light)]" x-data="{ edit: false }">
                        <td class="px-4 py-2" colspan="5">
                            <div class="flex flex-wrap items-center justify-between gap-2" x-show="!edit">
                                <div>
                                    <p class="font-medium">{{ $insurer->name }}</p>
                                    <p class="text-xs text-[var(--text-secondary)]">{{ number_format($insurer->coverage_rate * 100, 0) }}% · {{ $insurer->patients_count }} patients · {{ number_format($insurer->billed, 2) }} $</p>
                                </div>
                                <button type="button" class="btn btn-outline btn-sm" @click="edit = true">Modifier</button>
                            </div>
                            <form method="POST" action="{{ route('admin.pharmacy.insurers.update', $insurer) }}" class="grid md:grid-cols-5 gap-2" x-show="edit" x-cloak>
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $insurer->name }}" class="form-input" required>
                                <input type="number" step="0.01" min="0" max="1" name="coverage_rate" value="{{ $insurer->coverage_rate }}" class="form-input" required>
                                <input type="text" name="contact" value="{{ $insurer->contact }}" class="form-input" placeholder="Contact">
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($insurer->is_active)> Actif</label>
                                <div class="flex gap-2">
                                    <button class="btn btn-primary btn-sm" type="submit">OK</button>
                                    <button type="button" class="btn btn-outline btn-sm" @click="edit = false">Annuler</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-[var(--text-secondary)]">Aucune assurance.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📦 Ajouter un package</h1>
        <p class="text-[var(--text-secondary)] mt-1">Créez un nouveau package d'adhésion</p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl">
        <form action="{{ route('admin.packages.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nom *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input @error('name') input-error @enderror" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="input @error('slug') input-error @enderror" required>
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-[var(--text-secondary)] mt-1">URL unique (ex: package-bronze)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Prix ($) *</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price') }}" class="input @error('price') input-error @enderror" required>
                    @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">PV *</label>
                    <input type="number" name="pv_value" value="{{ old('pv_value') }}" class="input @error('pv_value') input-error @enderror" required>
                    @error('pv_value') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">BV</label>
                    <input type="number" name="bv_value" value="{{ old('bv_value', 0) }}" class="input">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Commission (%) *</label>
                    <input type="number" name="commission_rate" step="0.01" value="{{ old('commission_rate', 30) }}" class="input @error('commission_rate') input-error @enderror" required>
                    @error('commission_rate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Description</label>
                    <textarea name="description" rows="3" class="input">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer le package
                </button>
                <a href="{{ route('admin.packages') }}" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">➕ Ajouter un rang</h1>
        <p class="text-[var(--text-secondary)] mt-1">Créez un nouveau grade</p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl">
        <form action="{{ route('admin.ranks.store') }}" method="POST">
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
                    <p class="text-xs text-[var(--text-secondary)] mt-1">URL unique (ex: manager)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">PV minimum *</label>
                    <input type="number" name="min_pv" value="{{ old('min_pv', 0) }}" class="input @error('min_pv') input-error @enderror" required>
                    @error('min_pv') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">BV minimum</label>
                    <input type="number" name="min_bv" value="{{ old('min_bv', 0) }}" class="input">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Bonus (%) *</label>
                    <input type="number" name="bonus_percentage" step="0.01" value="{{ old('bonus_percentage', 0) }}" class="input @error('bonus_percentage') input-error @enderror" required>
                    @error('bonus_percentage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Statut</label>
                    <select name="is_active" class="input">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Description</label>
                    <textarea name="description" rows="2" class="input">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer le rang
                </button>
                <a href="{{ route('admin.ranks') }}" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
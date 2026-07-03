@extends('admin.layouts.app')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Modifier {{ $rank->name }}</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">ID: #{{ $rank->id }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.ranks.update', $rank) }}" method="POST">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $rank->name) }}" class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug', $rank->slug) }}" class="input text-sm sm:text-base @error('slug') input-error @enderror" required>
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">PV minimum *</label>
                    <input type="number" name="min_pv" value="{{ old('min_pv', $rank->min_pv) }}" class="input text-sm sm:text-base @error('min_pv') input-error @enderror" required>
                    @error('min_pv') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">BV minimum</label>
                    <input type="number" name="min_bv" value="{{ old('min_bv', $rank->min_bv ?? 0) }}" class="input text-sm sm:text-base">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Bonus (%) *</label>
                    <input type="number" name="bonus_percentage" step="0.01" value="{{ old('bonus_percentage', $rank->bonus_percentage) }}" class="input text-sm sm:text-base @error('bonus_percentage') input-error @enderror" required>
                    @error('bonus_percentage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Statut</label>
                    <select name="is_active" class="input text-sm sm:text-base">
                        <option value="1" {{ $rank->is_active ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ !$rank->is_active ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Description</label>
                    <textarea name="description" rows="2" class="input text-sm sm:text-base">{{ old('description', $rank->description) }}</textarea>
                </div>
            </div>

            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre a jour
                </button>
                <a href="{{ route('admin.ranks') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
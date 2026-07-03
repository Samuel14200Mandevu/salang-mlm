@extends('admin.layouts.app')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Modifier {{ $package->name }}</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">ID: #{{ $package->id }}</p>
    </div>

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.packages.update', $package) }}" method="POST">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $package->name) }}" class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug', $package->slug) }}" class="input text-sm sm:text-base @error('slug') input-error @enderror" required>
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Prix ($) *</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $package->price) }}" class="input text-sm sm:text-base @error('price') input-error @enderror" required>
                    @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">PV *</label>
                    <input type="number" name="pv_value" value="{{ old('pv_value', $package->pv_value) }}" class="input text-sm sm:text-base @error('pv_value') input-error @enderror" required>
                    @error('pv_value') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">BV</label>
                    <input type="number" name="bv_value" value="{{ old('bv_value', $package->bv_value) }}" class="input text-sm sm:text-base">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Commission (%) *</label>
                    <input type="number" name="commission_rate" step="0.01" value="{{ old('commission_rate', $package->commission_rate) }}" class="input text-sm sm:text-base @error('commission_rate') input-error @enderror" required>
                    @error('commission_rate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Description</label>
                    <textarea name="description" rows="3" class="input text-sm sm:text-base">{{ old('description', $package->description) }}</textarea>
                </div>
            </div>

            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre a jour
                </button>
                <a href="{{ route('admin.packages') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
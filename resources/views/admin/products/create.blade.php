@extends('admin.layouts.app')

@push('styles')
<style>
    .image-preview {
        width: 150px;
        height: 150px;
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .image-preview:hover { border-color: var(--primary-500); }
    .image-preview img { width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">➕ Ajouter un produit</h1>
        <p class="text-[var(--text-secondary)] mt-1">Créez un nouveau produit</p>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 animate-fadeIn">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card animate-fadeInUp delay-1 max-w-2xl">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Image du produit</label>
                    <div class="image-preview" id="imagePreview" onclick="document.getElementById('imageInput').click()">
                        <span class="text-[var(--text-tertiary)] text-4xl">📸</span>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" class="hidden" 
                           onchange="previewImage(this)">
                    <p class="text-xs text-[var(--text-secondary)] mt-1">Formats: JPG, PNG, GIF, WEBP (max 2MB)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Nom *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input @error('name') input-error @enderror" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="input @error('slug') input-error @enderror" required>
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-[var(--text-secondary)] mt-1">URL unique (ex: macbook-pro-2024)</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Description</label>
                    <textarea name="description" rows="3" class="input">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Prix ($) *</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price') }}" class="input @error('price') input-error @enderror" required>
                    @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Coût d'achat ($)</label>
                    <input type="number" name="cost" step="0.01" value="{{ old('cost') }}" class="input">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Stock *</label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}" class="input @error('stock') input-error @enderror" required>
                    @error('stock') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" class="input">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Catégorie</label>
                    <select name="category" class="input">
                        <option value="">Sélectionner</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        Produit actif
                    </label>
                    <label class="flex items-center gap-2 text-sm text-[var(--text-secondary)]">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        ⭐ En vedette
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer le produit
                </button>
                <a href="{{ route('admin.products') }}" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
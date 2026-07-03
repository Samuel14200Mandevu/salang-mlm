@extends('admin.layouts.app')

@push('styles')
<style>
    .image-preview {
        width: 120px;
        height: 120px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: var(--bg-secondary);
    }
    .image-preview img { width: 100%; height: 100%; object-fit: cover; }
    .image-preview-empty {
        width: 120px;
        height: 120px;
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--bg-secondary);
    }
    .image-preview-empty:hover { border-color: var(--primary-500); }
    
    @media (max-width: 640px) {
        .image-preview, .image-preview-empty { width: 100px; height: 100px; }
        .form-group { margin-bottom: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Modifier {{ $product->name }}</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">ID: #{{ $product->id }}</p>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm sm:text-base animate-fadeIn">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm sm:text-base animate-fadeIn">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4 md:p-6">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <!-- Image actuelle -->
            <div class="mb-3 sm:mb-4 p-3 sm:p-4 bg-[var(--bg-secondary)] rounded-lg">
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mb-2">Image actuelle</p>
                <div class="image-preview">
                    @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                        <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}">
                    @else
                        <svg class="w-8 h-8 sm:w-12 sm:h-12 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    @endif
                </div>
                @if($product->image)
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-2 truncate">{{ $product->image }}</p>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nouvelle image</label>
                    <div class="image-preview-empty" id="imagePreview" onclick="document.getElementById('imageInput').click()">
                        <span class="text-[var(--text-tertiary)] text-xs sm:text-sm">Changer</span>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" class="hidden"
                           onchange="previewImage(this)">
                    <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">Formats: JPG, PNG, GIF, WEBP (max 2MB)</p>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="input text-sm sm:text-base @error('slug') input-error @enderror" required>
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Description</label>
                    <textarea name="description" rows="3" class="input text-sm sm:text-base">{{ old('description', $product->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Prix ($) *</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" class="input text-sm sm:text-base @error('price') input-error @enderror" required>
                    @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Cout d'achat ($)</label>
                    <input type="number" name="cost" step="0.01" value="{{ old('cost', $product->cost) }}" class="input text-sm sm:text-base">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Stock *</label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="input text-sm sm:text-base @error('stock') input-error @enderror" required>
                    @error('stock') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="input text-sm sm:text-base">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">Categorie</label>
                    <select name="category" class="input text-sm sm:text-base">
                        <option value="">Selectionner</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $product->category == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}>
                        Produit actif
                    </label>
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }}>
                        En vedette
                    </label>
                </div>
            </div>

            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre a jour
                </button>
                <a href="{{ route('admin.products') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
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
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Apercu" class="w-full h-full object-cover rounded-lg">';
            preview.className = 'image-preview';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
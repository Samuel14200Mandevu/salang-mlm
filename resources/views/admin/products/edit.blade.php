{{-- resources/views/admin/products/edit.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary-blue: #0A2A6C;
    --primary-blue-dark: #061B4A;
    --primary-blue-bg: rgba(10, 42, 108, 0.08);
    --primary-blue-border: rgba(10, 42, 108, 0.15);
}

.image-preview {
    width: 120px;
    height: 120px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--bg-secondary);
}
.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview-empty {
    width: 120px;
    height: 120px;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: border-color 0.15s ease;
    background: var(--bg-secondary);
}
.image-preview-empty:hover {
    border-color: var(--primary-blue);
}

.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    font-size: 0.813rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}
.form-group .required {
    color: #B91C1C;
}
.form-group .help-text {
    font-size: 0.75rem;
    color: var(--text-tertiary);
    margin-top: 0.125rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    outline: none;
}
.form-control:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px var(--primary-blue-border);
}
.form-control-error {
    border-color: #B91C1C;
}
.form-control-error:focus {
    border-color: #B91C1C;
    box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.12);
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.813rem;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
}
.btn:active {
    transform: scale(0.97);
}

.btn-primary {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}
.btn-primary:hover {
    background: var(--primary-blue-dark);
    border-color: var(--primary-blue-dark);
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border-color: var(--border-color);
}
.btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--border-color);
}

.product-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.product-status.active {
    background: rgba(28, 126, 74, 0.12);
    color: #1C7E4A;
    border-color: rgba(28, 126, 74, 0.15);
}
.product-status.inactive {
    background: rgba(185, 28, 28, 0.12);
    color: #B91C1C;
    border-color: rgba(185, 28, 28, 0.15);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.status-dot.active { background: #1C7E4A; }
.status-dot.inactive { background: #B91C1C; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.3s ease forwards; }
.delay-1 { animation-delay: 0.05s; }

@media (max-width: 640px) {
    .image-preview, .image-preview-empty {
        width: 100px;
        height: 100px;
    }
    .form-group {
        margin-bottom: 0.75rem;
    }
    .form-group label {
        font-size: 0.75rem;
    }
    .form-grid {
        grid-template-columns: 1fr !important;
    }
    .edit-header {
        flex-direction: column;
        align-items: flex-start !important;
    }
    .edit-header .status-wrapper {
        margin-left: 0 !important;
        margin-top: 0.5rem;
    }
    .card {
        padding: 0.875rem;
    }
    .btn {
        width: 100%;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header -->
    <div class="animate-fadeInUp">
        <div class="edit-header flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                    Modifier {{ $product->name }}
                </h1>
                <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                    ID: #{{ $product->id }}
                </p>
            </div>
            <div class="status-wrapper ml-auto flex-shrink-0">
                <span class="product-status {{ $product->is_active ? 'active' : 'inactive' }}">
                    <span class="status-dot {{ $product->is_active ? 'active' : 'inactive' }}"></span>
                    {{ $product->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 text-sm animate-fadeInUp flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 sm:p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-500 text-sm animate-fadeInUp">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                <!-- Current Image -->
                <div class="form-group md:col-span-2">
                    <label>Image actuelle</label>
                    <div class="image-preview">
                        @if($product->image && file_exists(storage_path('app/public/products/' . $product->image)))
                            <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}">
                        @else
                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @endif
                    </div>
                    @if($product->image)
                        <span class="help-text mt-1 truncate">{{ $product->image }}</span>
                    @endif
                </div>

                <!-- New Image -->
                <div class="form-group md:col-span-2">
                    <label>Nouvelle image (optionnel)</label>
                    <div class="image-preview-empty" id="imagePreview" onclick="document.getElementById('imageInput').click()">
                        <span class="text-[var(--text-tertiary)] text-xs sm:text-sm">Changer l'image</span>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" class="hidden"
                           onchange="previewImage(this)">
                    <span class="help-text">Formats: JPG, PNG, GIF, WEBP (max 2MB)</span>
                </div>

                <!-- Name -->
                <div class="form-group">
                    <label>Nom <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}"
                           class="form-control @error('name') form-control-error @enderror" required>
                    @error('name')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                           class="form-control @error('slug') form-control-error @enderror" required>
                    <span class="help-text">Identifiant unique pour l'URL</span>
                    @error('slug')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label>Prix (USD) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}"
                           class="form-control @error('price') form-control-error @enderror" required>
                    @error('price')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cost -->
                <div class="form-group">
                    <label>Prix de revient (USD)</label>
                    <input type="number" name="cost" step="0.01" value="{{ old('cost', $product->cost) }}"
                           class="form-control">
                </div>

                <!-- PV Value -->
                <div class="form-group">
                    <label>Valeur PV <span class="required">*</span></label>
                    <select name="pv_value" class="form-control @error('pv_value') form-control-error @enderror" required>
                        <option value="">Sélectionner PV</option>
                        <option value="15" {{ old('pv_value', $product->pv_value) == 15 ? 'selected' : '' }}>15 PV</option>
                        <option value="20" {{ old('pv_value', $product->pv_value) == 20 ? 'selected' : '' }}>20 PV</option>
                        <option value="25" {{ old('pv_value', $product->pv_value) == 25 ? 'selected' : '' }}>25 PV</option>
                        <option value="30" {{ old('pv_value', $product->pv_value) == 30 ? 'selected' : '' }}>30 PV</option>
                        <option value="35" {{ old('pv_value', $product->pv_value) == 35 ? 'selected' : '' }}>35 PV</option>
                        <option value="40" {{ old('pv_value', $product->pv_value) == 40 ? 'selected' : '' }}>40 PV</option>
                        <option value="45" {{ old('pv_value', $product->pv_value) == 45 ? 'selected' : '' }}>45 PV</option>
                        <option value="50" {{ old('pv_value', $product->pv_value) == 50 ? 'selected' : '' }}>50 PV</option>
                        <option value="55" {{ old('pv_value', $product->pv_value) == 55 ? 'selected' : '' }}>55 PV</option>
                        <option value="75" {{ old('pv_value', $product->pv_value) == 75 ? 'selected' : '' }}>75 PV</option>
                        <option value="100" {{ old('pv_value', $product->pv_value) == 100 ? 'selected' : '' }}>100 PV</option>
                    </select>
                    <span class="help-text">Valeur PV du produit (15, 20, 25, 30, 35, 40, 45, 50, 55, 75 ou 100)</span>
                    @error('pv_value')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- BV Value -->
                <div class="form-group">
                    <label>Valeur BV</label>
                    <input type="number" name="bv_value" step="0.01" value="{{ old('bv_value', $product->bv_value ?? 0) }}"
                           class="form-control" placeholder="0">
                    <span class="help-text">Bonus Value (généralement identique au PV ou 0)</span>
                </div>

                <!-- Stock -->
                <div class="form-group">
                    <label>Stock <span class="required">*</span></label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock) }}"
                           class="form-control @error('stock') form-control-error @enderror" required>
                    @error('stock')
                        <p class="text-xs text-[#B91C1C] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SKU -->
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                           class="form-control">
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category" class="form-control">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category }}" {{ $product->category == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status & Featured -->
                <div class="form-group flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}>
                        Actif
                    </label>
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }}>
                        En vedette
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
                <a href="{{ route('admin.products') }}" class="btn btn-outline flex-1 sm:flex-none">
                    Annuler
                </a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <div class="footer-links mt-4 pt-4 border-t border-[var(--border-color)] flex flex-wrap gap-4 text-xs text-[var(--text-muted)]">
        <a href="{{ route('legal.terms') }}" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:underline">Conditions générales d'utilisation</a>
        <a href="{{ route('legal.privacy') }}" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:underline">Politique de confidentialité</a>
        <span>© {{ date('Y') }} — Tous droits réservés</span>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu" class="w-full h-full object-cover rounded-lg">';
            preview.className = 'image-preview';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
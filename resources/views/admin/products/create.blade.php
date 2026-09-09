{{-- resources/views/admin/products/create.blade.php --}}
@extends('admin.layouts.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --bg-base: #F5F6F8;
    --bg-card: #F8F9FA;
    --bg-input: #FFFFFF;
    --bg-secondary: #EEF0F3;
    --bg-hover: #E8EAEE;
    --text-primary: #1A1A1E;
    --text-secondary: #4A4A52;
    --text-tertiary: #7A7A82;
    --border-color: #DCDEE3;
    --border-light: #E8EAEE;
    --primary: #0A2A6C;
    --primary-dark: #061B4A;
    --primary-bg: rgba(10, 42, 108, 0.06);
    --success: #1F7B4D;
    --danger: #B32A2A;
}

* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
}

.image-preview {
    width: 120px;
    height: 120px;
    border: 2px dashed var(--border-color);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background: var(--bg-secondary);
    transition: border-color 0.15s ease;
}
.image-preview:hover {
    border-color: var(--primary);
}
.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
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
    color: var(--danger);
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
    border-radius: 6px;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: border-color 0.15s ease;
    outline: none;
}
.form-control:focus {
    border-color: var(--primary);
}
.form-control-error {
    border-color: var(--danger);
}
.form-control-error:focus {
    border-color: var(--danger);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.813rem;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    transition: background 0.15s ease;
}
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

.btn-primary {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
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

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp { animation: fadeInUp 0.25s ease forwards; }
.delay-1 { animation-delay: 0.05s; }

@media (max-width: 640px) {
    .image-preview {
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

    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">Ajouter un produit</h1>
        <p class="text-sm text-[var(--text-secondary)] mt-0.5">Créer un nouveau produit</p>
    </div>

    @if($errors->any())
        <div class="p-4 border border-red-300 rounded-lg bg-red-50 text-red-700 text-sm animate-fadeInUp">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card animate-fadeInUp delay-1 max-w-2xl p-3 sm:p-4">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                <!-- Image -->
                <div class="form-group md:col-span-2">
                    <label>Image du produit</label>
                    <div class="image-preview" id="imagePreview" onclick="document.getElementById('imageInput').click()">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" class="hidden" onchange="previewImage(this)">
                    <span class="help-text">Formats: JPG, PNG, GIF, WEBP (max 2MB)</span>
                </div>

                <!-- Nom -->
                <div class="form-group">
                    <label>Nom <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') form-control-error @enderror"
                           placeholder="Nom du produit" required>
                    @error('name')
                        <p class="text-xs text-[var(--danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           class="form-control @error('slug') form-control-error @enderror"
                           placeholder="nom-du-produit" required>
                    <span class="help-text">Identifiant unique pour l'URL</span>
                    @error('slug')
                        <p class="text-xs text-[var(--danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="form-control"
                              placeholder="Description du produit...">{{ old('description') }}</textarea>
                </div>

                <!-- Prix -->
                <div class="form-group">
                    <label>Prix (USD) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" value="{{ old('price') }}"
                           class="form-control @error('price') form-control-error @enderror"
                           placeholder="99.99" required>
                    @error('price')
                        <p class="text-xs text-[var(--danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prix de revient -->
                <div class="form-group">
                    <label>Prix de revient (USD)</label>
                    <input type="number" name="cost" step="0.01" value="{{ old('cost') }}"
                           class="form-control" placeholder="49.99">
                </div>

                <!-- PV Value -->
                <div class="form-group">
                    <label>Valeur PV <span class="required">*</span></label>
                    <select name="pv_value" class="form-control @error('pv_value') form-control-error @enderror" required>
                        <option value="">Sélectionner PV</option>
                        <option value="15" {{ old('pv_value') == 15 ? 'selected' : '' }}>15 PV</option>
                        <option value="20" {{ old('pv_value') == 20 ? 'selected' : '' }}>20 PV</option>
                        <option value="25" {{ old('pv_value') == 25 ? 'selected' : '' }}>25 PV</option>
                        <option value="30" {{ old('pv_value') == 30 ? 'selected' : '' }}>30 PV</option>
                        <option value="35" {{ old('pv_value') == 35 ? 'selected' : '' }}>35 PV</option>
                        <option value="40" {{ old('pv_value') == 40 ? 'selected' : '' }}>40 PV</option>
                        <option value="45" {{ old('pv_value') == 45 ? 'selected' : '' }}>45 PV</option>
                        <option value="50" {{ old('pv_value') == 50 ? 'selected' : '' }}>50 PV</option>
                        <option value="55" {{ old('pv_value') == 55 ? 'selected' : '' }}>55 PV</option>
                        <option value="75" {{ old('pv_value') == 75 ? 'selected' : '' }}>75 PV</option>
                        <option value="100" {{ old('pv_value') == 100 ? 'selected' : '' }}>100 PV</option>
                    </select>
                    <span class="help-text">Valeur PV du produit</span>
                    @error('pv_value')
                        <p class="text-xs text-[var(--danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stock -->
                <div class="form-group">
                    <label>Stock <span class="required">*</span></label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}"
                           class="form-control @error('stock') form-control-error @enderror"
                           placeholder="0" required>
                    @error('stock')
                        <p class="text-xs text-[var(--danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SKU -->
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}"
                           class="form-control" placeholder="PROD-001">
                </div>

                <!-- Catégorie -->
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category" class="form-control">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Unités (nouveaux champs) -->
                <div class="form-group">
                    <label>Unité</label>
                    <input type="text" name="unit" value="{{ old('unit') }}"
                           class="form-control" placeholder="Gélules, Comprimés, Sachets...">
                </div>

                <div class="form-group">
                    <label>Conditionnement</label>
                    <input type="text" name="packaging" value="{{ old('packaging') }}"
                           class="form-control" placeholder="Boîte de 30, Flacon...">
                </div>

                <div class="form-group md:col-span-2">
                    <label>Dosage</label>
                    <input type="text" name="dosage" value="{{ old('dosage') }}"
                           class="form-control" placeholder="500mg, 1000mg...">
                </div>

                <!-- Options prix -->
                <div class="form-group">
                    <label>Prix 1/2</label>
                    <input type="number" name="half_price" step="0.01" value="{{ old('half_price') }}"
                           class="form-control" placeholder="Option 1/2">
                </div>

                <div class="form-group">
                    <label>PV 1/2</label>
                    <input type="number" name="half_pv" value="{{ old('half_pv') }}"
                           class="form-control" placeholder="PV option 1/2">
                </div>

                <div class="form-group">
                    <label>Prix Complet</label>
                    <input type="number" name="full_price" step="0.01" value="{{ old('full_price') }}"
                           class="form-control" placeholder="Option complète">
                </div>

                <div class="form-group">
                    <label>PV Complet</label>
                    <input type="number" name="full_pv" value="{{ old('full_pv') }}"
                           class="form-control" placeholder="PV option complète">
                </div>

                <!-- Statut -->
                <div class="form-group md:col-span-2 flex flex-wrap items-center gap-4 pt-2">
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        Actif
                    </label>
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        En vedette
                    </label>
                </div>
            </div>

            <!-- Boutons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    Créer le produit
                </button>
                <a href="{{ route('admin.products') }}" class="btn btn-outline flex-1 sm:flex-none">
                    Annuler
                </a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <div class="mt-4 pt-4 border-t border-[var(--border-color)] flex flex-wrap gap-4 text-xs text-[var(--text-tertiary)]">
        <a href="#" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]">Conditions générales</a>
        <a href="#" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]">Politique de confidentialité</a>
        <span>© {{ date('Y') }}</span>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu">';
            preview.className = 'image-preview';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
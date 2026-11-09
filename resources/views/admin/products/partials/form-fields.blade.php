@php
    /** @var \App\Models\Product|null $product */
    $isEdit = isset($product) && $product;
    $val = function (string $field, $default = null) use ($product, $isEdit) {
        if ($isEdit) {
            return old($field, $product->{$field} ?? $default);
        }

        return old($field, $default);
    };
    $pvOptions = [15, 20, 25, 30, 35, 40, 45, 50, 55, 75, 100];
    $currentImage = $isEdit && $product->image && file_exists(storage_path('app/public/products/'.$product->image))
        ? asset('storage/products/'.$product->image)
        : null;
@endphp

<div class="product-form-layout">
    {{-- Colonne principale --}}
    <div class="product-form-main space-y-4">
        <section class="product-form-section">
            <div class="product-form-section__head">
                <h3>Identité</h3>
                <p>Nom, URL, catégorie et description catalogue.</p>
            </div>
            <div class="product-form-grid product-form-grid--2">
                <div class="form-group">
                    <label for="name">Nom <span class="required">*</span></label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ $val('name') }}"
                           class="form-control @error('name') form-control-error @enderror"
                           placeholder="Nom du produit"
                           required>
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug">Slug <span class="required">*</span></label>
                    <input type="text"
                           id="slug"
                           name="slug"
                           value="{{ $val('slug') }}"
                           class="form-control font-mono @error('slug') form-control-error @enderror"
                           placeholder="nom-du-produit"
                           required>
                    <span class="help-text">Identifiant unique pour l’URL</span>
                    @error('slug')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category }}" @selected((string) $val('category') === (string) $category)>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="sku">Code-barres (SKU)</label>
                    <input type="text"
                           id="sku"
                           name="sku"
                           value="{{ $val('sku') }}"
                           class="form-control font-mono"
                           placeholder="SAL-000001 ou EAN">
                    <span class="help-text">Vide = généré automatiquement</span>
                </div>

                <div class="form-group product-form-grid__full">
                    <label for="description">Description</label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              class="form-control"
                              placeholder="Description du produit…">{{ $val('description') }}</textarea>
                </div>
            </div>
        </section>

        <section class="product-form-section">
            <div class="product-form-section__head">
                <h3>Prix &amp; PV</h3>
                <p>Tarifs flacon / ½ et points de volume associés.</p>
            </div>
            <div class="product-form-grid product-form-grid--4">
                <div class="form-group">
                    <label for="full_price">Prix flacon (USD)</label>
                    <input type="number"
                           id="full_price"
                           name="full_price"
                           step="0.01"
                           value="{{ $val('full_price') }}"
                           class="form-control"
                           placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="full_pv">PV flacon</label>
                    <input type="number"
                           id="full_pv"
                           name="full_pv"
                           value="{{ $val('full_pv') }}"
                           class="form-control"
                           placeholder="0">
                </div>
                <div class="form-group">
                    <label for="half_price">Prix ½ (USD)</label>
                    <input type="number"
                           id="half_price"
                           name="half_price"
                           step="0.01"
                           value="{{ $val('half_price') }}"
                           class="form-control"
                           placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="half_pv">PV ½</label>
                    <input type="number"
                           id="half_pv"
                           name="half_pv"
                           value="{{ $val('half_pv') }}"
                           class="form-control"
                           placeholder="0">
                </div>
                <div class="form-group">
                    <label for="price">Prix de base (USD) <span class="required">*</span></label>
                    <input type="number"
                           id="price"
                           name="price"
                           step="0.01"
                           value="{{ $val('price') }}"
                           class="form-control @error('price') form-control-error @enderror"
                           placeholder="0.00"
                           required>
                    @error('price')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="cost">Prix de revient (USD)</label>
                    <input type="number"
                           id="cost"
                           name="cost"
                           step="0.01"
                           value="{{ $val('cost') }}"
                           class="form-control"
                           placeholder="0.00">
                </div>
                <div class="form-group product-form-grid__span-2">
                    <label for="pv_value">Valeur PV <span class="required">*</span></label>
                    <select id="pv_value"
                            name="pv_value"
                            class="form-control @error('pv_value') form-control-error @enderror"
                            required>
                        <option value="">Sélectionner PV</option>
                        @foreach($pvOptions as $pv)
                            <option value="{{ $pv }}" @selected((string) $val('pv_value') === (string) $pv)>{{ $pv }} PV</option>
                        @endforeach
                    </select>
                    @error('pv_value')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="product-form-section">
            <div class="product-form-section__head">
                <h3>Conditionnement</h3>
                <p>Unité, packaging et dosage affichés sur la fiche.</p>
            </div>
            <div class="product-form-grid product-form-grid--3">
                <div class="form-group">
                    <label for="unit">Unité</label>
                    <input type="text"
                           id="unit"
                           name="unit"
                           value="{{ $val('unit') }}"
                           class="form-control"
                           placeholder="Gélules, Comprimés…">
                </div>
                <div class="form-group">
                    <label for="packaging">Conditionnement</label>
                    <input type="text"
                           id="packaging"
                           name="packaging"
                           value="{{ $val('packaging') }}"
                           class="form-control"
                           placeholder="Boîte de 30, Flacon…">
                </div>
                <div class="form-group">
                    <label for="dosage">Dosage</label>
                    <input type="text"
                           id="dosage"
                           name="dosage"
                           value="{{ $val('dosage') }}"
                           class="form-control"
                           placeholder="500mg, 1000mg…">
                </div>
            </div>
        </section>
    </div>

    {{-- Colonne latérale --}}
    <aside class="product-form-side space-y-4">
        <section class="product-form-section">
            <div class="product-form-section__head">
                <h3>Image</h3>
                <p>JPG, PNG, GIF, WEBP · max 2 Mo</p>
            </div>
            <div class="image-dropzone" id="imagePreview" onclick="document.getElementById('imageInput').click()" role="button" tabindex="0">
                @if($currentImage)
                    <img src="{{ $currentImage }}" alt="{{ $isEdit ? $product->name : 'Aperçu' }}" id="imagePreviewImg">
                @else
                    <div class="image-dropzone__empty" id="imagePreviewEmpty">
                        <svg class="w-8 h-8 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $isEdit ? 'Changer l’image' : 'Ajouter une image' }}</span>
                    </div>
                @endif
            </div>
            <input type="file" id="imageInput" name="image" accept="image/*" class="hidden" onchange="previewProductImage(this)">
            @if($isEdit && $product->image)
                <p class="help-text truncate mt-2">{{ $product->image }}</p>
            @endif
        </section>

        <section class="product-form-section">
            <div class="product-form-section__head">
                <h3>Stock</h3>
                <p>Quantité disponible à la création / maj.</p>
            </div>
            <div class="form-group mb-0">
                <label for="stock">Stock <span class="required">*</span></label>
                <input type="number"
                       id="stock"
                       name="stock"
                       min="0"
                       value="{{ $val('stock', 0) }}"
                       class="form-control @error('stock') form-control-error @enderror"
                       required>
                @if($isEdit)
                    <span class="help-text">
                        Mouvements détaillés :
                        <a href="{{ route('admin.products.stock', $product->id) }}" class="text-[var(--primary)] hover:underline">Gestion du stock</a>
                    </span>
                @endif
                @error('stock')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <section class="product-form-section">
            <div class="product-form-section__head">
                <h3>Visibilité</h3>
                <p>Publication et mise en avant.</p>
            </div>
            <div class="product-form-toggles">
                <label class="toggle-row">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           @checked($isEdit ? (bool) old('is_active', $product->is_active) : (bool) old('is_active', true))>
                    <span>
                        <strong>Actif</strong>
                        <small>Visible à la vente</small>
                    </span>
                </label>
                <label class="toggle-row">
                    <input type="checkbox"
                           name="is_featured"
                           value="1"
                           @checked($isEdit ? (bool) old('is_featured', $product->is_featured) : (bool) old('is_featured'))>
                    <span>
                        <strong>En vedette</strong>
                        <small>Mis en avant dans le catalogue</small>
                    </span>
                </label>
            </div>
        </section>

        <div class="product-form-actions">
            <button type="submit" class="btn btn-primary w-full">
                {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le produit' }}
            </button>
            <a href="{{ $isEdit ? route('admin.products.show', $product->id) : route('admin.products') }}"
               class="btn btn-outline w-full">
                Annuler
            </a>
        </div>
    </aside>
</div>

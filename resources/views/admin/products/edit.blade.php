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
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
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
    .image-preview-empty:hover {
        border-color: var(--primary-500);
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }
    .form-group .required {
        color: #ef4444;
    }
    .form-group .help-text {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        margin-top: 0.125rem;
    }
    .product-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }
    .product-status.active {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }
    .product-status.inactive {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    
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
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <div class="edit-header flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">
                    Edit {{ $product->name }}
                </h1>
                <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">
                    ID: #{{ $product->id }}
                </p>
            </div>
            <div class="status-wrapper ml-auto flex-shrink-0">
                <span class="product-status {{ $product->is_active ? 'active' : 'inactive' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
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

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Current Image -->
                <div class="form-group md:col-span-2">
                    <label>Current Image</label>
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
                        <p class="help-text mt-1 truncate">{{ $product->image }}</p>
                    @endif
                </div>

                <!-- New Image -->
                <div class="form-group md:col-span-2">
                    <label>New Image (optional)</label>
                    <div class="image-preview-empty" id="imagePreview" onclick="document.getElementById('imageInput').click()">
                        <span class="text-[var(--text-tertiary)] text-xs sm:text-sm">Change image</span>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" class="hidden"
                           onchange="previewImage(this)">
                    <p class="help-text">Formats: JPG, PNG, GIF, WEBP (max 2MB)</p>
                </div>

                <!-- Name -->
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" 
                           class="input text-sm sm:text-base @error('slug') input-error @enderror" required>
                    <p class="help-text">Unique URL identifier</p>
                    @error('slug') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="input text-sm sm:text-base">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label>Price (USD) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" 
                           class="input text-sm sm:text-base @error('price') input-error @enderror" required>
                    @error('price') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Cost -->
                <div class="form-group">
                    <label>Cost Price (USD)</label>
                    <input type="number" name="cost" step="0.01" value="{{ old('cost', $product->cost) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- ✅ PV VALUE - NOUVEAU CHAMP -->
                <div class="form-group">
                    <label>PV Value <span class="required">*</span></label>
                    <select name="pv_value" class="input text-sm sm:text-base @error('pv_value') input-error @enderror" required>
                        <option value="">Select PV</option>
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
                    <p class="help-text">PV value for this product (15, 20, 25, 30, 35, 40, 45, 50, 55, 75 or 100)</p>
                    @error('pv_value') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- ✅ BV VALUE - NOUVEAU CHAMP -->
                <div class="form-group">
                    <label>BV Value</label>
                    <input type="number" name="bv_value" step="0.01" value="{{ old('bv_value', $product->bv_value ?? 0) }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="0">
                    <p class="help-text">Bonus Value (usually same as PV or 0)</p>
                </div>

                <!-- Stock -->
                <div class="form-group">
                    <label>Stock <span class="required">*</span></label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" 
                           class="input text-sm sm:text-base @error('stock') input-error @enderror" required>
                    @error('stock') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- SKU -->
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" 
                           class="input text-sm sm:text-base">
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="input text-sm sm:text-base">
                        <option value="">Select category</option>
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
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }}>
                        Featured
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 sm:mt-6 flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Product
                </button>
                <a href="{{ route('admin.products') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
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
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="w-full h-full object-cover rounded-lg">';
            preview.className = 'image-preview';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
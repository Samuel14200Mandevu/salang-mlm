@extends('admin.layouts.app')

@push('styles')
<style>
    .image-preview {
        width: 120px;
        height: 120px;
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: var(--bg-secondary);
    }
    .image-preview:hover {
        border-color: var(--primary-500);
    }
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: var(--radius-md);
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
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Add Product</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Create a new product</p>
    </div>

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
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Image -->
                <div class="form-group md:col-span-2">
                    <label>Product Image</label>
                    <div class="image-preview" id="imagePreview" onclick="document.getElementById('imageInput').click()">
                        <svg class="w-8 h-8 sm:w-12 sm:h-12 text-[var(--text-tertiary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" class="hidden" 
                           onchange="previewImage(this)">
                    <p class="help-text">Formats: JPG, PNG, GIF, WEBP (max 2MB)</p>
                </div>

                <!-- Name -->
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           class="input text-sm sm:text-base @error('name') input-error @enderror" 
                           placeholder="Product name" required>
                    @error('name') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug') }}" 
                           class="input text-sm sm:text-base @error('slug') input-error @enderror" 
                           placeholder="product-slug" required>
                    <p class="help-text">Unique URL identifier</p>
                    @error('slug') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-group md:col-span-2">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="input text-sm sm:text-base" 
                              placeholder="Product description...">{{ old('description') }}</textarea>
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label>Price (USD) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" value="{{ old('price') }}" 
                           class="input text-sm sm:text-base @error('price') input-error @enderror" 
                           placeholder="99.99" required>
                    @error('price') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Cost -->
                <div class="form-group">
                    <label>Cost Price (USD)</label>
                    <input type="number" name="cost" step="0.01" value="{{ old('cost') }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="49.99">
                </div>

                <!-- Stock -->
                <div class="form-group">
                    <label>Stock <span class="required">*</span></label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}" 
                           class="input text-sm sm:text-base @error('stock') input-error @enderror" 
                           placeholder="0" required>
                    @error('stock') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- SKU -->
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" 
                           class="input text-sm sm:text-base" 
                           placeholder="PROD-001">
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="input text-sm sm:text-base">
                        <option value="">Select category</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status & Featured -->
                <div class="form-group flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-[var(--text-secondary)] cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
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
                    Create Product
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
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            preview.className = 'image-preview';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
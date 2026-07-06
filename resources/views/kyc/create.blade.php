@extends('layouts.app')

@push('styles')
<style>
    .document-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        padding: 0.75rem;
        text-align: center;
        border: 2px solid var(--border-color);
    }
    .document-type-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    .document-type-card.selected {
        border-color: var(--primary-500);
        background: rgba(90, 182, 56, 0.04);
    }
    .document-type-card .doc-icon {
        display: block;
        margin-bottom: 0.5rem;
    }
    .document-type-card .doc-icon svg {
        width: 2rem;
        height: 2rem;
        margin: 0 auto;
        color: var(--text-primary);
    }
    .document-type-card p {
        font-size: 0.7rem;
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .drop-zone {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: var(--bg-secondary);
    }
    .drop-zone:hover {
        border-color: var(--primary-500);
        background: rgba(90, 182, 56, 0.02);
    }
    .drop-zone.dragover {
        border-color: var(--primary-500);
        background: rgba(90, 182, 56, 0.05);
    }
    .drop-zone .file-preview {
        max-width: 150px;
        max-height: 120px;
        margin: 0 auto;
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    .drop-zone .file-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 20px rgba(90, 182, 56, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(90, 182, 56, 0.4);
    }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }
    .btn-outline:hover {
        border-color: var(--primary-500);
        color: var(--primary-500);
    }
    
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    
    .input {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-input);
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
    }
    .input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px var(--border-focus);
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp { animation: fadeInUp 0.6s ease forwards; }
    .delay-1 { animation-delay: 0.05s; }
    
    @media (max-width: 640px) {
        .card { padding: 0.875rem; }
        .document-type-card { padding: 0.5rem; }
        .document-type-card .doc-icon svg { width: 1.5rem; height: 1.5rem; }
        .document-type-card p { font-size: 0.6rem; }
        .drop-zone { padding: 1rem; }
        .btn { font-size: 0.75rem; padding: 0.375rem 0.875rem; }
        .input { font-size: 0.813rem; padding: 0.5rem 0.75rem; }
        .doc-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }
    
    @media (max-width: 480px) {
        .card { padding: 0.75rem; }
        .doc-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .drop-zone { padding: 0.75rem; }
        .drop-zone svg { width: 2rem; height: 2rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Header -->
    <div class="animate-fadeInUp">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--text-primary)]">Submit KYC Document</h1>
        <p class="text-sm sm:text-base text-[var(--text-secondary)] mt-0.5 sm:mt-1">Verify your identity by submitting your documents</p>
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

    <div class="card animate-fadeInUp delay-1 max-w-2xl mx-auto">
        <form action="{{ route('kyc.store') }}" method="POST" enctype="multipart/form-data" id="kycForm">
            @csrf

            <!-- Document Type -->
            <div class="mb-4 sm:mb-6">
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-2 sm:mb-3">
                    Document Type *
                </label>
                <div class="doc-grid grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
                    <div class="document-type-card" data-value="id_card" onclick="selectDocumentType(this)">
                        <span class="doc-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                        </span>
                        <p>ID Card</p>
                    </div>
                    <div class="document-type-card" data-value="passport" onclick="selectDocumentType(this)">
                        <span class="doc-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </span>
                        <p>Passport</p>
                    </div>
                    <div class="document-type-card" data-value="proof_of_address" onclick="selectDocumentType(this)">
                        <span class="doc-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <p>Proof of Address</p>
                    </div>
                    <div class="document-type-card" data-value="selfie" onclick="selectDocumentType(this)">
                        <span class="doc-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <p>Selfie</p>
                    </div>
                </div>
                <input type="hidden" name="document_type" id="document_type" required>
                @error('document_type')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Document Number -->
            <div class="mb-3 sm:mb-4">
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                    Document Number (optional)
                </label>
                <input type="text" 
                       name="document_number" 
                       class="input text-sm sm:text-base" 
                       placeholder="e.g. AB123456"
                       value="{{ old('document_number') }}">
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">Optional, but recommended to speed up verification</p>
            </div>

            <!-- File Upload -->
            <div class="mb-3 sm:mb-4">
                <label class="block text-xs sm:text-sm font-medium text-[var(--text-secondary)] mb-1">
                    File * (max 5MB)
                </label>
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <div id="dropZoneContent">
                        <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto text-[var(--text-tertiary)] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-xs sm:text-sm text-[var(--text-secondary)]">Click or drag and drop your file here</p>
                        <p class="text-[10px] sm:text-xs text-[var(--text-tertiary)] mt-1">Accepted formats: JPG, PNG, PDF</p>
                    </div>
                    <div id="dropZonePreview" class="hidden">
                        <div class="file-preview">
                            <img id="filePreviewImage" src="" alt="Preview" class="w-full h-full object-cover">
                        </div>
                        <p id="filePreviewName" class="text-xs sm:text-sm font-medium text-[var(--text-primary)] mt-2"></p>
                        <p id="filePreviewSize" class="text-[10px] sm:text-xs text-[var(--text-secondary)]"></p>
                    </div>
                </div>
                <input type="file" 
                       id="fileInput" 
                       name="file" 
                       accept=".jpg,.jpeg,.png,.pdf"
                       class="hidden"
                       onchange="handleFileSelect(this)"
                       required>
                @error('file')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[10px] sm:text-xs text-[var(--text-secondary)] mt-1">
                    Formats: JPG, PNG, PDF • Max size: 5MB
                </p>
            </div>

            <!-- Important Info -->
            <div class="p-2 sm:p-4 bg-[var(--bg-secondary)] rounded-lg mb-4 sm:mb-6">
                <p class="text-xs sm:text-sm text-[var(--text-secondary)]">
                    <span class="font-semibold text-[var(--text-primary)]">Important:</span>
                    <br>
                    • Documents must be clear and readable.<br>
                    • Only JPG, PNG and PDF formats are accepted.<br>
                    • Maximum file size is 5MB.<br>
                    • Verification may take up to 48 hours.
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap gap-2 sm:gap-3">
                <button type="submit" class="btn btn-primary w-full sm:flex-1 text-sm sm:text-base py-2 sm:py-2.5" id="submitBtn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit Document
                </button>
                <a href="{{ route('kyc.index') }}" class="btn btn-outline w-full sm:w-auto text-sm sm:text-base py-2 sm:py-2.5">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function selectDocumentType(element) {
    document.querySelectorAll('.document-type-card').forEach(function(card) {
        card.classList.remove('selected');
    });
    element.classList.add('selected');
    document.getElementById('document_type').value = element.dataset.value;
}

function handleFileSelect(input) {
    var file = input.files[0];
    if (file) {
        var preview = document.getElementById('dropZonePreview');
        var content = document.getElementById('dropZoneContent');
        var previewImg = document.getElementById('filePreviewImage');
        var previewName = document.getElementById('filePreviewName');
        var previewSize = document.getElementById('filePreviewSize');

        if (file.size > 5 * 1024 * 1024) {
            alert('File is too large. Max size: 5MB');
            input.value = '';
            return;
        }

        var allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('Unsupported format. Use JPG, PNG or PDF.');
            input.value = '';
            return;
        }

        content.classList.add('hidden');
        preview.classList.remove('hidden');

        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
            previewImg.src = '';
        }

        previewName.textContent = file.name;
        previewSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
    }
}

var dropZone = document.getElementById('dropZone');

dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});

dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    
    var files = e.dataTransfer.files;
    if (files.length > 0) {
        var input = document.getElementById('fileInput');
        input.files = files;
        handleFileSelect(input);
    }
});

document.getElementById('kycForm').addEventListener('submit', function(e) {
    var docType = document.getElementById('document_type').value;
    var fileInput = document.getElementById('fileInput');
    
    if (!docType) {
        e.preventDefault();
        alert('Please select a document type.');
        return;
    }
    
    if (!fileInput.files || !fileInput.files[0]) {
        e.preventDefault();
        alert('Please select a file.');
        return;
    }
});
</script>
@endpush
@endsection
@extends('layouts.app')

@push('styles')
<style>
    .document-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .document-type-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    .document-type-card.selected {
        border-color: var(--primary-500);
        background: rgba(99,102,241,0.05);
    }
    .document-type-card .doc-icon {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.5rem;
    }
    .drop-zone {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-lg);
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: var(--bg-secondary);
    }
    .drop-zone:hover {
        border-color: var(--primary-500);
        background: rgba(99,102,241,0.02);
    }
    .drop-zone.dragover {
        border-color: var(--primary-500);
        background: rgba(99,102,241,0.05);
    }
    .drop-zone .file-preview {
        max-width: 200px;
        max-height: 150px;
        margin: 0 auto;
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    .drop-zone .file-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📤 Soumettre un document KYC</h1>
        <p class="text-[var(--text-secondary)] mt-1">Vérifiez votre identité en soumettant vos documents</p>
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

    <div class="card animate-fadeInUp delay-1 max-w-2xl mx-auto">
        <form action="{{ route('kyc.store') }}" method="POST" enctype="multipart/form-data" id="kycForm">
            @csrf

            <!-- Type de document -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-3">
                    📋 Type de document *
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="document-type-card card p-3 text-center" data-value="id_card" onclick="selectDocumentType(this)">
                        <span class="doc-icon">🪪</span>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Carte d'identité</p>
                        <p class="text-xs text-[var(--text-secondary)]">Recto/Verso</p>
                    </div>
                    <div class="document-type-card card p-3 text-center" data-value="passport" onclick="selectDocumentType(this)">
                        <span class="doc-icon">📘</span>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Passeport</p>
                        <p class="text-xs text-[var(--text-secondary)]">Page photo</p>
                    </div>
                    <div class="document-type-card card p-3 text-center" data-value="proof_of_address" onclick="selectDocumentType(this)">
                        <span class="doc-icon">📬</span>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Justificatif de domicile</p>
                        <p class="text-xs text-[var(--text-secondary)]">Facture récente</p>
                    </div>
                    <div class="document-type-card card p-3 text-center" data-value="selfie" onclick="selectDocumentType(this)">
                        <span class="doc-icon">🤳</span>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Selfie</p>
                        <p class="text-xs text-[var(--text-secondary)]">Avec pièce d'identité</p>
                    </div>
                </div>
                <input type="hidden" name="document_type" id="document_type" required>
                @error('document_type')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Numéro de document -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                    🔢 Numéro de document (optionnel)
                </label>
                <input type="text" 
                       name="document_number" 
                       class="input" 
                       placeholder="Ex: AB123456"
                       value="{{ old('document_number') }}">
                <p class="text-xs text-[var(--text-secondary)] mt-1">Facultatif, mais recommandé pour accélérer la vérification</p>
            </div>

            <!-- Upload de fichier -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">
                    📎 Fichier * (max 5MB)
                </label>
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <div id="dropZoneContent">
                        <span class="text-4xl block mb-2">📁</span>
                        <p class="text-[var(--text-secondary)]">Cliquez ou glissez-déposez votre fichier ici</p>
                        <p class="text-xs text-[var(--text-tertiary)] mt-1">Formats acceptés : JPG, PNG, PDF</p>
                    </div>
                    <div id="dropZonePreview" class="hidden">
                        <div class="file-preview">
                            <img id="filePreviewImage" src="" alt="Aperçu" class="w-full h-full object-cover">
                        </div>
                        <p id="filePreviewName" class="text-sm font-medium text-[var(--text-primary)] mt-2"></p>
                        <p id="filePreviewSize" class="text-xs text-[var(--text-secondary)]"></p>
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
                <p class="text-xs text-[var(--text-secondary)] mt-1">
                    Formats: JPG, PNG, PDF • Taille max: 5MB
                </p>
            </div>

            <!-- Informations -->
            <div class="p-4 bg-[var(--bg-secondary)] rounded-lg mb-6">
                <p class="text-sm text-[var(--text-secondary)]">
                    <span class="font-semibold text-[var(--text-primary)]">📌 Important :</span>
                    <br>
                    • Les documents doivent être clairs et lisibles.<br>
                    • Seuls les formats JPG, PNG et PDF sont acceptés.<br>
                    • La taille maximale est de 5MB par fichier.<br>
                    • La vérification peut prendre jusqu'à 48 heures.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary flex-1" id="submitBtn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Soumettre le document
                </button>
                <a href="{{ route('kyc.index') }}" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function selectDocumentType(element) {
    // Retirer la sélection de tous
    document.querySelectorAll('.document-type-card').forEach(card => {
        card.classList.remove('selected');
    });
    // Sélectionner celui-ci
    element.classList.add('selected');
    document.getElementById('document_type').value = element.dataset.value;
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        const preview = document.getElementById('dropZonePreview');
        const content = document.getElementById('dropZoneContent');
        const previewImg = document.getElementById('filePreviewImage');
        const previewName = document.getElementById('filePreviewName');
        const previewSize = document.getElementById('filePreviewSize');

        // Vérifier la taille (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Le fichier est trop volumineux. Taille max: 5MB');
            input.value = '';
            return;
        }

        // Vérifier le type
        const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format non supporté. Utilisez JPG, PNG ou PDF.');
            input.value = '';
            return;
        }

        content.classList.add('hidden');
        preview.classList.remove('hidden');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
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

// Drag and Drop
const dropZone = document.getElementById('dropZone');

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
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('fileInput');
        input.files = files;
        handleFileSelect(input);
    }
});

// Validation du formulaire
document.getElementById('kycForm').addEventListener('submit', function(e) {
    const docType = document.getElementById('document_type').value;
    const fileInput = document.getElementById('fileInput');
    
    if (!docType) {
        e.preventDefault();
        alert('Veuillez sélectionner un type de document.');
        return;
    }
    
    if (!fileInput.files || !fileInput.files[0]) {
        e.preventDefault();
        alert('Veuillez sélectionner un fichier.');
        return;
    }
});
</script>
@endpush
@endsection
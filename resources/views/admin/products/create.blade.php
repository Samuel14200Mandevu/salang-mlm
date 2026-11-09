@extends('admin.layouts.app')

@section('title', 'Ajouter un produit')

@push('styles')
@include('admin.products.partials.form-styles')
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">

    @include('admin.layouts.partials.desktop-page-header', [
        'title' => 'Ajouter un produit',
        'subtitle' => 'Créer une fiche catalogue complète',
        'actions' => '<a href="'.route('admin.products').'" class="btn btn-outline btn-sm">Catalogue</a>',
    ])

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Nouveau produit</h1>
                <p class="page-subtitle">Fiche catalogue</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ route('admin.products') }}" class="btn btn-outline btn-sm btn-round-icon" title="Catalogue" aria-label="Catalogue">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="p-3 salang-flash-error border rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="animate-fadeInUp">
        @csrf
        @include('admin.products.partials.form-fields', [
            'product' => null,
            'categories' => $categories ?? [],
        ])
    </form>
</div>
@endsection

@push('scripts')
<script>
function previewProductImage(input) {
    const zone = document.getElementById('imagePreview');
    if (!input.files || !input.files[0] || !zone) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        zone.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu" id="imagePreviewImg">';
    };
    reader.readAsDataURL(input.files[0]);
}

document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    if (!nameInput || !slugInput) return;

    let slugTouched = slugInput.value.trim().length > 0;
    slugInput.addEventListener('input', function () {
        slugTouched = this.value.trim().length > 0;
    });

    nameInput.addEventListener('input', function () {
        if (slugTouched) return;
        slugInput.value = this.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    });
});
</script>
@endpush

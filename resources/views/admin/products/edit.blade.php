@extends('admin.layouts.app')

@section('title', 'Modifier — '.$product->name)

@push('styles')
@include('admin.products.partials.form-styles')
@endpush

@section('content')
@php
    $statusLabel = $product->is_active ? 'Actif' : 'Inactif';
@endphp
<div class="space-y-4 sm:space-y-6">

    @include('admin.layouts.partials.desktop-page-header', [
        'title' => 'Modifier le produit',
        'subtitle' => e($product->name).' · #'.$product->id.' · '.$statusLabel,
        'actions' => view('admin.products.partials.edit-header-actions', ['product' => $product])->render(),
    ])

    <div class="admin-page-header page-header md:hidden animate-fadeInUp">
        <div class="admin-mobile-page-head is-mobile-banner">
            <div class="admin-title-banner__text">
                <h1 class="page-title">Modifier</h1>
                <p class="page-subtitle">{{ $product->name }}</p>
            </div>
            <div class="admin-title-banner__tools">
                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline btn-sm btn-round-icon" title="Détail" aria-label="Détail">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 salang-flash-success border rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="p-3 salang-flash-error border rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->id) }}"
          method="POST"
          enctype="multipart/form-data"
          class="animate-fadeInUp">
        @csrf
        @method('PUT')
        @include('admin.products.partials.form-fields', [
            'product' => $product,
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
</script>
@endpush

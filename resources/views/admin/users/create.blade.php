@extends('admin.layouts.app')

@section('title', 'Créer un compte')

@push('styles')
@include('admin.users.partials.form-styles')
@endpush

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Créer un compte',
    'subtitle' => 'Membre, caissier, admin ou responsable IT',
    'actions' => '<a href="'.e(route('admin.users')).'" class="btn btn-outline btn-sm">Utilisateurs</a>',
])

<div class="admin-page-header page-header md:hidden animate-fadeInUp">
    <div class="admin-mobile-page-head is-mobile-banner">
        <div class="admin-title-banner__text">
            <h1 class="page-title">Créer un compte</h1>
            <p class="page-subtitle">Membre, caissier, admin ou IT</p>
        </div>
        @include('admin.users.partials.create-mobile-banner-actions')
    </div>
</div>

<div class="admin-mobile-page admin-mobile-page--user-form space-y-4 sm:space-y-6">
    @if($errors->any())
        <div class="p-3 salang-flash-error border rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" id="adminUserCreateForm" class="animate-fadeInUp">
        @csrf
        @include('admin.users.partials.form-fields', [
            'user' => null,
            'packages' => $packages ?? [],
            'ranks' => $ranks ?? [],
        ])
    </form>

    <div class="user-form-actions--mobile-fixed">
        <button type="submit" form="adminUserCreateForm" class="btn btn-primary">Créer le compte</button>
        <a href="{{ route('admin.users') }}" class="btn btn-outline">Annuler</a>
    </div>
</div>
@endsection

@push('scripts')
@include('admin.users.partials.form-scripts', ['user' => null])
@endpush

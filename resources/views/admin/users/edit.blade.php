@extends('admin.layouts.app')

@section('title', 'Modifier — '.$user->name)

@push('styles')
@include('admin.users.partials.form-styles')
@endpush

@section('content')
@include('admin.layouts.partials.desktop-page-header', [
    'title' => 'Modifier le compte',
    'subtitle' => e($user->name).' · #'.$user->id,
    'actions' => view('admin.users.partials.edit-form-header-actions', ['user' => $user])->render(),
])

<div class="admin-page-header page-header md:hidden animate-fadeInUp">
    <div class="admin-mobile-page-head is-mobile-banner">
        <div class="admin-title-banner__text admin-title-banner__text--user-edit">
            <div class="admin-title-banner__avatar">
                @if($user->avatar)
                    <img src="{{ asset('storage/avatars/'.$user->avatar) }}" alt="" class="user-avatar-preview">
                @else
                    <div class="user-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
            </div>
            <div class="admin-title-banner__copy">
                <h1 class="page-title">Modifier</h1>
                <p class="page-subtitle">{{ $user->name }} · #{{ $user->id }}</p>
            </div>
        </div>
        @include('admin.users.partials.edit-mobile-banner-actions', ['user' => $user])
    </div>
</div>

<div class="admin-mobile-page admin-mobile-page--user-form space-y-4 sm:space-y-6">
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

    <form action="{{ route('admin.users.update', $user->id) }}"
          method="POST"
          id="userEditForm"
          class="animate-fadeInUp">
        @csrf
        @method('PUT')
        @include('admin.users.partials.form-fields', [
            'user' => $user,
            'packages' => $packages ?? [],
            'ranks' => $ranks ?? [],
        ])
    </form>

    <div class="user-form-actions--mobile-fixed">
        <button type="submit" form="userEditForm" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline">Annuler</a>
    </div>
</div>
@endsection

@push('scripts')
@include('admin.users.partials.form-scripts', ['user' => $user])
@endpush

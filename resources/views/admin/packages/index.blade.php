@extends('admin.layouts.app')

@push('styles')
<style>
    .package-row:hover { background: var(--bg-hover); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">📦 Packages</h1>
            <p class="text-[var(--text-secondary)] mt-1">Gérez les packages d'adhésion</p>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-500 animate-fadeIn">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="card animate-fadeInUp delay-1">
        <div class="table-wrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Slug</th>
                        <th>Prix</th>
                        <th>PV</th>
                        <th>Commission</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr class="package-row">
                            <td class="font-mono text-sm">#{{ $package->id }}</td>
                            <td class="font-medium">{{ $package->name }}</td>
                            <td class="text-[var(--text-secondary)] text-sm">{{ $package->slug }}</td>
                            <td class="font-bold text-primary-500">${{ number_format($package->price, 2) }}</td>
                            <td>{{ number_format($package->pv_value) }}</td>
                            <td>
                                <span class="badge badge-success">{{ $package->commission_rate }}%</span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.packages.edit', $package) }}" 
                                       class="btn btn-outline btn-sm btn-icon" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement ce package ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm btn-icon text-red-500 hover:text-red-700" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-[var(--text-secondary)]">
                                <svg class="w-16 h-16 mx-auto text-[var(--text-tertiary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7l8 4"/>
                                </svg>
                                Aucun package
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
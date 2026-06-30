@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="animate-fadeInUp">
        <h1 class="text-2xl md:text-3xl font-bold text-[var(--text-primary)]">💬 Centre de messages</h1>
        <p class="text-[var(--text-secondary)] mt-1">Vos messages et notifications</p>
    </div>

    <div class="card animate-fadeInUp delay-1">
        <div class="space-y-3">
            <!-- Message 1 -->
            <div class="flex items-start gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-gradient flex-shrink-0">A</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)]">Administrateur</p>
                    <p class="text-sm text-[var(--text-secondary)]">Votre demande de retrait a été approuvée.</p>
                    <p class="text-xs text-[var(--text-secondary)] mt-1">Il y a 2 heures</p>
                </div>
                <span class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></span>
            </div>

            <!-- Message 2 -->
            <div class="flex items-start gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-info flex-shrink-0">S</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)]">Support</p>
                    <p class="text-sm text-[var(--text-secondary)]">Nous avons répondu à votre ticket #TICK-001.</p>
                    <p class="text-xs text-[var(--text-secondary)] mt-1">Il y a 1 jour</p>
                </div>
                <span class="w-2 h-2 bg-gray-400 rounded-full flex-shrink-0 mt-2"></span>
            </div>

            <!-- Message 3 -->
            <div class="flex items-start gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg hover:bg-[var(--bg-hover)] transition">
                <div class="avatar avatar-md avatar-success flex-shrink-0">J</div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-[var(--text-primary)]">Jean Dupont</p>
                    <p class="text-sm text-[var(--text-secondary)]">Merci pour votre parrainage !</p>
                    <p class="text-xs text-[var(--text-secondary)] mt-1">Il y a 3 jours</p>
                </div>
                <span class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></span>
            </div>
        </div>
    </div>
</div>
@endsection
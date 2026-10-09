@extends('layouts.app')

@php($isSupervision = auth()->user()->hasSupervisionAccess())

@section('title', $isSupervision ? ($conversation->member?->name ?? 'Conversation') : 'Support Salang')

@section('content')
<div class="supervision-page supervision-page--question-show question-thread-page max-w-3xl mx-auto">
    <x-member.catalog-banner
        :eyebrow="$isSupervision ? 'Supervision · Salang' : 'Services · Salang'"
        :title="$isSupervision ? ($conversation->member?->name ?? 'Membre') : 'Support Salang'"
        :sub="($isSupervision ? 'Conversation membre' : 'Votre fil avec l’équipe').' · '.($conversation->is_resolved ? 'Résolue' : 'Ouverte')"
        banner-class="supervision-catalog-banner">
        <x-slot:tools>
            <a href="{{ route('questions.index') }}" class="shop-banner-icon-btn" aria-label="Retour">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            @can('delete', $conversation)
                <form action="{{ route('questions.destroy', $conversation) }}" method="POST" onsubmit="return confirm('Supprimer cette conversation ?');" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="shop-banner-icon-btn" aria-label="Supprimer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            @endcan
        </x-slot:tools>
    </x-member.catalog-banner>

    <div class="supervision-page-header-desktop question-thread-page-head animate-fadeInUp">
        <div class="min-w-0 flex items-start gap-3">
            @include('questions.partials.user-avatar', ['user' => $isSupervision ? $conversation->member : auth()->user(), 'size' => 'lg'])
            <div class="min-w-0">
                <a href="{{ route('questions.index') }}" class="text-sm text-primary-500 hover:underline">← Retour</a>
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] mt-1 truncate">
                    {{ $isSupervision ? ($conversation->member?->name ?? 'Membre') : 'Support Salang' }}
                </h1>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-0.5">
                    {{ $conversation->is_resolved ? 'Résolue' : 'Ouverte' }}
                    · {{ $conversation->messages->count() }} message(s)
                </p>
            </div>
        </div>
        @can('delete', $conversation)
            <form action="{{ route('questions.destroy', $conversation) }}" method="POST" onsubmit="return confirm('Supprimer cette conversation ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline btn-sm text-red-500 border-red-200">Supprimer</button>
            </form>
        @endcan
    </div>

    @if(session('success'))
        <p class="mx-3 md:mx-0 mb-2 text-sm text-center text-[var(--color-primary-700)] font-medium" role="status">{{ session('success') }}</p>
    @endif

    @include('questions.partials.thread-messenger', [
        'conversation' => $conversation,
        'canAnswer' => $canAnswer,
        'canMessage' => $canMessage,
    ])
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('questionThreadMessages');
    if (box) {
        box.scrollTop = box.scrollHeight;
    }

    ['questionAnswerInput', 'questionMemberMessageInput'].forEach(function (id) {
        var input = document.getElementById(id);
        if (!input) return;
        function resize() {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        }
        input.addEventListener('input', resize);
        resize();
    });
});
</script>
@endpush

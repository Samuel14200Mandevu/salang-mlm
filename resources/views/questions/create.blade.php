@extends('layouts.app')

@section('title', 'Poser une question')

@section('content')
<div class="supervision-page supervision-page--question-show supervision-page--question-compose question-thread-page max-w-3xl mx-auto">
    <x-member.catalog-banner
        eyebrow="Services · Salang"
        title="Support Salang"
        sub="Poser une question · Réponse de l’équipe"
        banner-class="supervision-catalog-banner">
        <x-slot:tools>
            <a href="{{ route('questions.index') }}" class="shop-banner-icon-btn" aria-label="Retour">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </x-slot:tools>
    </x-member.catalog-banner>

    <div class="supervision-page-header-desktop question-thread-page-head animate-fadeInUp">
        <div class="min-w-0 flex items-start gap-3">
            <div class="question-chat-avatar question-chat-avatar--lg question-chat-avatar--support" aria-hidden="true">
                <span>S</span>
            </div>
            <div class="min-w-0">
                <a href="{{ route('questions.index') }}" class="text-sm text-primary-500 hover:underline">← Retour</a>
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)] mt-1">Support Salang</h1>
                <p class="text-xs sm:text-sm text-[var(--text-secondary)] mt-0.5">Nouvelle conversation</p>
            </div>
        </div>
    </div>

    @include('questions.partials.compose-form')
</div>
@endsection

@extends('layouts.app')

@section('title', $isSupervision ? 'Questions reçues' : 'Mes questions')

@section('content')
<div class="supervision-page space-y-4 sm:space-y-6">
    @include('questions.partials.catalog-head', compact('questions', 'isSupervision', 'statusFilter'))

    <div class="supervision-page-header-desktop flex flex-wrap items-center justify-between gap-3 animate-fadeInUp">
        <div class="member-page-intro min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--text-primary)]">
                {{ $isSupervision ? 'Questions reçues' : 'Mes questions' }}
            </h1>
            <p class="text-sm text-[var(--text-secondary)] mt-0.5">
                {{ $isSupervision ? 'Répondez aux membres et caissiers' : 'Suivez vos demandes à l’équipe' }}
            </p>
        </div>
        <a href="{{ route('questions.create') }}" class="btn btn-primary btn-sm sm:btn-md shrink-0">
            Poser une question
        </a>
    </div>

    @if($isSupervision)
        <div class="questions-filters-desktop supervision-filters-card card p-3 animate-fadeInUp delay-1 hidden md:block">
            @include('questions.partials.filters-form', ['filterFormId' => 'questions-status-filter-desktop'])
        </div>
    @endif

    <div class="question-convo-list supervision-questions-list card overflow-hidden animate-fadeInUp delay-2">
        @forelse($questions as $question)
            @include('questions.partials.conversation-row', compact('question', 'isSupervision'))
        @empty
            <div class="p-8 text-center text-[var(--text-secondary)]">
                <p class="font-medium">Aucune conversation</p>
                <p class="text-sm mt-1">Utilisez « Poser une question » pour contacter l’équipe.</p>
            </div>
        @endforelse
    </div>

    @if($conversations->hasPages())
        <div class="mt-4">{{ $conversations->links() }}</div>
    @endif
</div>

@if($isSupervision)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('questionsFilterToggle');
    var panel = document.getElementById('questionsFiltersPanel');
    var head = document.getElementById('questionsCatalogHead');
    if (!toggle || !panel) return;

    function setFiltersOpen(open) {
        panel.classList.toggle('is-open', open);
        toggle.classList.toggle('is-active', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (head) {
            head.classList.toggle('is-filters-open', open);
        }
    }

    toggle.addEventListener('click', function () {
        setFiltersOpen(!panel.classList.contains('is-open'));
    });
});
</script>
@endpush
@endif
@endsection

{{-- En-tête classique desktop (≥768px) — titre + actions optionnelles --}}
<div class="page-header admin-desktop-page-head {{ $class ?? '' }}">
    <div class="top-row">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if(!empty($subtitle))
                <p class="page-subtitle">{!! $subtitle !!}</p>
            @endif
        </div>
        @if(!empty($actions))
            <div class="header-actions flex flex-wrap gap-2 items-center">
                {!! $actions !!}
            </div>
        @endif
    </div>
</div>

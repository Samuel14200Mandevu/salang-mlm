{{-- Bandeau titre mobile (style accueil / membres) --}}
<div class="admin-mobile-greeting md:hidden">
    <div class="admin-title-banner__text">
        <h1>{{ $title }}</h1>
        @if(!empty($subtitle))
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    @if(!empty($actions))
        <div class="admin-title-banner__tools">
            {!! $actions !!}
        </div>
    @endif
</div>

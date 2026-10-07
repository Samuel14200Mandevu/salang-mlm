{{-- Bandeau titre mobile (style accueil / membres) --}}
<div class="admin-mobile-greeting md:hidden">
    <h1>{{ $title }}</h1>
    @if(!empty($subtitle))
        <p>{{ $subtitle }}</p>
    @endif
</div>

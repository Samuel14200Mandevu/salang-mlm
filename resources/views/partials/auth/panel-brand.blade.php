@php
    $heroImage = file_exists(public_path('images/site.webp')) ? asset('images/site.webp') : asset('images/site.png');
@endphp
<aside
    class="auth-panel-brand"
    style="--auth-panel-bg: url('{{ $heroImage }}');"
>
    <div class="auth-panel-brand-main">
    <div class="auth-panel-brand-block">
        <a href="{{ url('/') }}" class="auth-panel-brand-logo">
            <x-ui.image
                src="images/salang_logo.png"
                alt="Salang Group"
                class="auth-panel-brand-logo-img brightness-110"
                width="220"
                height="60"
                :priority="true"
                :lazy="false"
            />
        </a>
        <p class="auth-panel-kicker">Salang Group</p>
        <h1>Health Care <span class="auth-panel-title-accent">International</span></h1>
        <p>
            Médecine complémentaire et opportunité de réseau. Connectez-vous pour gérer votre activité, vos commissions et votre équipe.
        </p>
        <ul class="auth-panel-list">
            <li>Produits naturels et parcours membre sécurisé</li>
            <li>Tableau de bord commissions et réseau binaire</li>
            <li>Support et documents officiels Salang Group</li>
        </ul>
    </div>
    </div>
    <p class="auth-panel-foot">&copy; {{ date('Y') }} Salang Group · Hong Kong</p>
</aside>

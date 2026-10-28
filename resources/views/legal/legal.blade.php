@extends('layouts.public')

@section('title', ($pageTitle ?? 'Mentions légales') . ' — Salang Group')

@php
    $site = config('salang-site');
    $backRoute = $back['route'] ?? route('home');
    $backLabel = $back['label'] ?? 'Retour à l\'accueil';
    $editor = [
        ['label' => 'Raison sociale', 'value' => 'Salang Group'],
        ['label' => 'Forme juridique', 'value' => 'SARL'],
        ['label' => 'N° RCCM', 'value' => 'CD/BKV/RCCM/20-B-00116'],
        ['label' => 'N° Import-Export', 'value' => '0024/CBX-21/I000439SK/Z'],
        ['label' => 'N° SARL', 'value' => 'IDN:22-M7 300-N63464 Q'],
        ['label' => 'Directeur Général', 'value' => 'Lou JIAN CHENG'],
    ];
    $coords = [
        ['label' => 'Adresse', 'value' => '382 AV ixoras Limeté Résidentielle, Kinshasa RD Congo'],
        ['label' => 'Téléphone', 'value' => '+243 999 086 990 / 975 220 079'],
        ['label' => 'Email', 'value' => $site['email']],
        ['label' => 'Site web', 'value' => 'www.salanggroup.com'],
    ];
@endphp

@section('content')
<section class="public-page-hero">
    <div class="public-shell">
        <p class="public-kicker">Informations légales</p>
        <h1 class="public-page-hero__title">{{ $pageTitle ?? 'Mentions légales' }}</h1>
        <p class="public-page-hero__lead">
            Identité de l’éditeur et coordonnées officielles de {{ $site['brand'] }}.
        </p>
    </div>
</section>

<section class="public-section">
    <div class="public-shell public-shell--reading">
        @include('partials.public.legal-tabs')

        <article class="public-legal-doc">
            <p class="public-legal-doc__updated">Dernière mise à jour : 27 juillet 2021</p>

            <h2>Éditeur du site</h2>
            <div class="public-legal-meta">
                @foreach ($editor as $item)
                    <div class="public-legal-meta__item">
                        <span class="public-legal-meta__label">{{ $item['label'] }}</span>
                        <span class="public-legal-meta__value">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>

            <h2>Coordonnées</h2>
            <div class="public-legal-meta">
                @foreach ($coords as $item)
                    <div class="public-legal-meta__item">
                        <span class="public-legal-meta__label">{{ $item['label'] }}</span>
                        <span class="public-legal-meta__value">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="public-legal-actions">
                <a href="{{ $backRoute }}" class="public-btn-secondary">
                    <span aria-hidden="true">←</span>
                    {{ $backLabel }}
                </a>
                <a href="{{ route('contact') }}" class="public-btn-primary">Nous contacter</a>
            </div>
        </article>
    </div>
</section>
@endsection

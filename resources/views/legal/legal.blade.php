@extends('layouts.legal', [
    'backRoute' => $back['route'] ?? route('home'),
    'backLabel' => $back['label'] ?? 'Retour à l\'accueil'
])

@section('title', $pageTitle . ' - Salang MLM')

@section('content')
<div class="legal-page">
    <div class="legal-card">
        <h1>{{ $pageTitle }}</h1>
        <p class="last-update"> Dernière mise à jour : 27 Juillet 2021</p>

        <h2>Éditeur du site</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0;">
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Raison sociale</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">Salang Group</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Forme juridique</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">SARL</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">N° RCCM</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">CD/BKV/RCCM/20-B-00116</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">N° Import-Export</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">0024/CBX-21/I000439SK/Z</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">N° SARL</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">IDN:22-M7 300-N63464 Q</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Directeur Général</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">Lou JIAN CHENG</div>
            </div>
        </div>

        <h2>Coordonnées</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0;">
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Adresse</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">382 AV ixoras Limeté Résidentielle, Kinshasa RD Congo</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Téléphone</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">+243 999 086 990 / 975 220 079</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Email</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">support@salanggroup.com</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary-500);">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Site Web</div>
                <div style="font-weight: 600; color: var(--text-primary); margin-top: 0.25rem;">www.salanggroup.com</div>
            </div>
        </div>

        <a href="{{ $back['route'] ?? route('home') }}" class="back-link">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $back['label'] ?? 'Retour à l\'accueil' }}
        </a>
    </div>
</div>
@endsection
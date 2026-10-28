@extends('layouts.public')

@section('title', 'Contact — Salang Group')

@php
    $site = config('salang-site');
    $supportEmail = $supportEmail ?: $site['email'];
    $supportPhone = $supportPhone ?: $site['phone'];
    $subjectOptions = [
        'Adhésion / inscription',
        'Produits & commandes',
        'Plan de rémunération',
        'Compte membre / connexion',
        'Partenariat',
        'Autre',
    ];
@endphp

@section('content')
<section class="public-page-hero">
    <div class="public-shell">
        <p class="public-kicker">Contact</p>
        <h1 class="public-page-hero__title">Nous contacter</h1>
        <p class="public-page-hero__lead">
            Une question sur l’adhésion, les produits ou le plan&nbsp;? Écrivez-nous — l’équipe Salang vous répond.
        </p>
        <div class="public-contact-hero-actions">
            <a href="mailto:{{ $supportEmail }}" class="public-btn-primary">{{ $supportEmail }}</a>
            @if ($supportPhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $supportPhone) }}" class="public-btn-secondary">{{ $supportPhone }}</a>
            @endif
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-shell">
        @if (session('success'))
            <div class="public-alert public-alert--success mb-8" role="status">
                <span class="public-alert__mark" aria-hidden="true">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="public-contact-grid">
            <aside class="public-contact-aside">
                <p class="public-kicker">Coordonnées</p>
                <h2 class="public-heading">{{ $site['brand'] }}</h2>
                <p class="public-contact-aside__lead">{{ $site['subtitle'] }} · Siège {{ $site['hq'] }}</p>

                <ul class="public-contact-channels">
                    <li class="public-contact-channel">
                        <span class="public-contact-channel__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="20" height="20"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
                        </span>
                        <div>
                            <p class="public-contact-channel__label">Email</p>
                            <a href="mailto:{{ $supportEmail }}" class="public-contact-channel__value">{{ $supportEmail }}</a>
                        </div>
                    </li>
                    @if ($supportPhone)
                        <li class="public-contact-channel">
                            <span class="public-contact-channel__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="20" height="20"><path d="M6.5 4.5h3l1.5 4-2 1.5a12 12 0 0 0 5.5 5.5l1.5-2 4 1.5v3A2 2 0 0 1 18 20 14 14 0 0 1 4 6a2 2 0 0 1 2.5-1.5Z"/></svg>
                            </span>
                            <div>
                                <p class="public-contact-channel__label">Téléphone</p>
                                <a href="tel:{{ preg_replace('/\s+/', '', $supportPhone) }}" class="public-contact-channel__value">{{ $supportPhone }}</a>
                            </div>
                        </li>
                    @endif
                    <li class="public-contact-channel">
                        <span class="public-contact-channel__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="20" height="20"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                        </span>
                        <div>
                            <p class="public-contact-channel__label">Site web</p>
                            <a href="{{ $site['website'] }}" class="public-contact-channel__value" target="_blank" rel="noopener">www.salanggroup.com</a>
                        </div>
                    </li>
                </ul>

                <div class="public-contact-aside__links">
                    <a href="{{ asset('docs/livret-salang-international.pdf') }}" class="public-btn-secondary" target="_blank" rel="noopener">Livret PDF</a>
                    <a href="{{ route('site.remuneration') }}" class="public-contact-aside__textlink">Voir la rémunération →</a>
                    <a href="{{ route('register') }}" class="public-contact-aside__textlink">Adhérer — {{ $site['membership_fee_usd'] }}&nbsp;USD →</a>
                </div>
            </aside>

            <form method="POST" action="{{ route('contact.store') }}" class="public-contact-form" novalidate>
                @csrf
                <div class="public-contact-form__head">
                    <h2 class="public-contact-form__title">Envoyer un message</h2>
                    <p class="public-contact-form__hint">Réponse sous 24–48&nbsp;h ouvrées en général.</p>
                </div>

                <div class="public-hp" aria-hidden="true">
                    <label for="website">Site web</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="public-contact-form__row">
                    <div>
                        <label for="name" class="public-label">Nom complet</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" class="public-input @error('name') is-invalid @enderror" placeholder="Votre nom">
                        @error('name')<p class="public-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="public-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="public-input @error('email') is-invalid @enderror" placeholder="vous@exemple.com">
                        @error('email')<p class="public-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="public-contact-form__row">
                    <div>
                        <label for="phone" class="public-label">Téléphone <span class="public-label__opt">(optionnel)</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="public-input @error('phone') is-invalid @enderror" placeholder="+243 …">
                        @error('phone')<p class="public-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="subject" class="public-label">Sujet</label>
                        <select id="subject" name="subject" required class="public-input public-select @error('subject') is-invalid @enderror">
                            <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Choisir un sujet</option>
                            @foreach ($subjectOptions as $opt)
                                <option value="{{ $opt }}" @selected(old('subject') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('subject')<p class="public-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="message" class="public-label">Message</label>
                    <textarea id="message" name="message" rows="6" required class="public-input public-textarea @error('message') is-invalid @enderror" placeholder="Décrivez votre demande…">{{ old('message') }}</textarea>
                    @error('message')<p class="public-error">{{ $message }}</p>@enderror
                </div>

                <div class="public-contact-form__foot">
                    <button type="submit" class="public-btn-primary">Envoyer le message</button>
                    <p class="public-contact-form__privacy">
                        Vos informations sont utilisées uniquement pour vous répondre.
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="public-cta-banner">
    <div class="public-shell public-cta-banner__inner">
        <div>
            <p class="public-kicker">Adhésion</p>
            <h2 class="public-heading">Prêt à rejoindre Salang&nbsp;?</h2>
            <p class="public-cta-banner__lead">{{ $site['promise'] }}</p>
        </div>
        <div class="public-cta-banner__actions">
            <a href="{{ route('register') }}" class="public-btn-primary">Créer un compte — {{ $site['membership_fee_usd'] }}&nbsp;USD</a>
            <a href="{{ route('site.offer') }}" class="public-btn-hero-secondary">Découvrir l’offre</a>
        </div>
    </div>
</section>
@endsection

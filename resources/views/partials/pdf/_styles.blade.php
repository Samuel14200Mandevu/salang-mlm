{{-- Styles partagés en-tête / pied de page PDF Salang --}}
.salang-letterhead {
    width: 100%;
    margin: 0 0 8px 0;
    padding: 0;
    text-align: center;
    line-height: 0;
}
.salang-letterhead__img {
    width: 100%;
    max-width: 100%;
    height: auto;
    display: block;
    border: 0;
    margin: 0 auto;
}
/* Paysage : même image, légèrement resserrée pour densifier le rapport */
.salang-letterhead--compact {
    margin: 0 0 4px 0;
}
.salang-letterhead--compact .salang-letterhead__img {
    width: 100%;
    max-width: 100%;
}

.salang-lh-fallback {
    width: 100%;
    text-align: center;
    line-height: 1.25;
    border-bottom: 2px solid #C5A04D;
    padding-bottom: 6px;
}
.salang-lh-fallback__title {
    font-size: 15px;
    font-weight: bold;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.salang-lh-fallback__ids,
.salang-lh-fallback__contact {
    font-size: 8.5px;
    color: #111;
}
.salang-lh-fallback__cities {
    margin-top: 4px;
    padding: 4px 8px;
    background: #0A254E;
    color: #fff;
    font-size: 8px;
}

.salang-pdf-footer {
    width: 100%;
    margin-top: 14px;
    text-align: center;
    page-break-inside: avoid;
}
.salang-pdf-footer__brand {
    font-size: 9px;
    color: #0A254E;
    font-weight: bold;
    letter-spacing: 0.2px;
    margin-bottom: 4px;
}
.salang-pdf-footer__rule {
    border-bottom: 1.5px solid #C5A04D;
    width: 100%;
    margin: 0 auto 4px auto;
}
.salang-pdf-footer__note {
    font-size: 8px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

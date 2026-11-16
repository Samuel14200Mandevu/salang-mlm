{{-- Pied de page officiel Salang (aligné communiqué bleu / or) --}}
@php
    $pdfFooterLabel = $pdfFooterLabel ?? 'RAPPORT OFFICIEL – SALANG GROUP SARL';
@endphp
<div class="salang-pdf-footer">
    <div class="salang-pdf-footer__brand">SALANG GROUP SARL • www.salanggroup.com</div>
    <div class="salang-pdf-footer__rule"></div>
    <div class="salang-pdf-footer__note">{{ $pdfFooterLabel }}</div>
</div>

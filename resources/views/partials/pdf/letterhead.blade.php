{{-- En-tête officiel = image exacte du communiqué Salang (1536×213) --}}
@php
    if (empty($letterheadBase64 ?? null)) {
        $letterheadPath = public_path('images/salang-letterhead.jpg');
        if (! is_file($letterheadPath) || filesize($letterheadPath) <= 0) {
            $letterheadPath = public_path('images/salang-letterhead.png');
        }
        $letterheadBase64 = '';
        if (is_file($letterheadPath) && filesize($letterheadPath) > 0) {
            $mime = str_ends_with(strtolower($letterheadPath), '.png') ? 'image/png' : 'image/jpeg';
            $letterheadBase64 = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($letterheadPath));
        }
    }
@endphp
<div class="salang-letterhead{{ !empty($letterheadCompact) ? ' salang-letterhead--compact' : '' }}">
    @if($letterheadBase64 !== '')
        <img src="{{ $letterheadBase64 }}" alt="SALANG GROUP SARL" class="salang-letterhead__img" width="1536" height="{{ (int) ($letterheadImgHeight ?? 213) }}">
    @else
        {{-- Fallback texte si l’image manque --}}
        <div class="salang-lh-fallback">
            <div class="salang-lh-fallback__title">
                <span style="color:#0A254E;">SALANG GROUP</span>
                <span style="color:#C5A04D;">SARL</span>
            </div>
            <div class="salang-lh-fallback__ids">
                N° IDN:22-M7300-N63464Q &nbsp; N° RCCM:CD/BKV/RCCM/20-B-00116
            </div>
            <div class="salang-lh-fallback__contact">
                Contact : +243 975 220 079 &nbsp;|&nbsp; Web: www.salanggroup.com &nbsp;|&nbsp; Email: support@salanggroup.com
            </div>
            <div class="salang-lh-fallback__cities">
                Kinshasa : 4 AV, Ixoras 382, 7eme Rue Resid. &nbsp;|&nbsp;
                Bukavu : N°4 Av. FIZI, Q. Nyawera, C/Ibanda &nbsp;|&nbsp;
                Goma : Rondpoint Chikudu, Bat. KBS 3e N.
            </div>
        </div>
    @endif
</div>

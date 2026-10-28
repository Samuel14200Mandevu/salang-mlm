@extends('layouts.app')

@section('title', ($reportType ?? 'period') === 'network' ? 'Rapport réseau' : 'Document — '.$period)

@php
    $isNetwork = ($reportType ?? 'period') === 'network';
    $serverPaged = (bool) ($serverPaged ?? false);
    $shortPeriod = $period;
    if (! $isNetwork && preg_match('/^(\d{4})-(\d{2})$/', (string) $period, $m)) {
        $moisFr = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];
        $shortPeriod = ($moisFr[(int) $m[2]] ?? $m[2]).' '.$m[1];
    }
    if ($isNetwork) {
        $shortPeriod = 'Réseau';
    }
    $reportTitle = $isNetwork ? 'Rapport Détaillé du Réseau' : 'Rapport Détaillé de PV';
    $gradeLabel = \App\Support\MlmRank::label((int) ($user->rank_level ?? 0));
    $logoUrl = asset('images/salang_logo.png');

    if ($serverPaged) {
        $pageNum = (int) ($currentPage ?? 1);
        $totalPages = max(1, (int) ($totalPages ?? 1));
        $rowChunks = [$rows];
        $rowOffsetBase = (int) ($rowOffset ?? 0);
    } else {
        $firstPageSize = 6;
        $nextPageSize = 12;
        $rowChunks = [];
        if ($rowsCount === 0) {
            $rowChunks[] = [];
        } else {
            $rowChunks[] = array_slice($rows, 0, $firstPageSize);
            $rest = array_slice($rows, $firstPageSize);
            if ($rest !== []) {
                $rowChunks = array_merge($rowChunks, array_chunk($rest, $nextPageSize));
            }
        }
        $totalPages = count($rowChunks);
        $rowOffsetBase = null;
    }
@endphp

@section('content')
<div class="mrd-viewer" id="mrdViewer" data-pages="{{ $serverPaged ? 1 : $totalPages }}" data-server-paged="{{ $serverPaged ? '1' : '0' }}">
    @if (session('error'))
        <div class="mrd-viewer__alert">{{ session('error') }}</div>
    @endif
    @if (session('info'))
        <div class="mrd-viewer__alert" style="border-color:#bfdbfe;background:#eff6ff;color:#1d4ed8;">{{ session('info') }}</div>
    @endif

    <div class="mrd-viewer__stage">
        <div class="mrd-viewer__pages">
            @foreach ($rowChunks as $pageIndex => $pageRows)
                @php
                    $pageNum = $serverPaged ? (int) ($currentPage ?? 1) : ($pageIndex + 1);
                    $showIntro = $pageNum === 1;
                @endphp
                <article class="mrd-paper {{ ($serverPaged || $pageNum === 1) ? 'is-active' : '' }}" data-page="{{ $pageNum }}">
                    @if ($showIntro)
                        <header class="mrd-paper__header">
                            <div><img src="{{ $logoUrl }}" alt="" class="mrd-paper__logo"></div>
                            <div class="mrd-paper__brand">
                                <div class="mrd-paper__company">SALANG GROUP SARL</div>
                                <div class="mrd-paper__ids">
                                    N° IDN:22-M7300-N63464Q &nbsp; N° RCCM:CD/BKV/RCCM/20-B-00116<br>
                                    Contact : +243 975 220 079 &nbsp; Web:www.salanggroup.com &nbsp; Email:support@salanggroup.com
                                </div>
                                <div class="mrd-paper__address">
                                    <strong>Kinshasa :</strong> 4 AV, Ixoras 382, 7eme Rue Resid. |
                                    <strong>Bukavu :</strong> N°4 Av. FIZI, Q. Nyawera, C/Ibanda |
                                    <strong>Goma :</strong> Rondpoint Chikudu, Bat. KBS 3e N.
                                </div>
                            </div>
                            <div style="text-align:right"><img src="{{ $logoUrl }}" alt="" class="mrd-paper__logo"></div>
                        </header>

                        <h1 class="mrd-paper__title">{{ $reportTitle }}</h1>

                        <div class="mrd-paper__meta">
                            @if ($isNetwork)
                                <div>Type : <strong>Réseau complet</strong></div>
                                <div class="mrd-paper__period">Réseau</div>
                            @else
                                <div>Période : <strong>{{ $shortPeriod }}</strong></div>
                                <div class="mrd-paper__period">{{ $shortPeriod }}</div>
                            @endif
                            <div>Date : <strong>{{ $date_generated }}</strong></div>
                            <div>Filleuls : <strong>{{ number_format($rowsCount) }}</strong></div>
                        </div>

                        <h2 class="mrd-paper__section">1. Informations du Membre</h2>
                        <table class="mrd-paper__info">
                            <tr>
                                <th>Nom complet</th>
                                <td><strong>{{ $user->name }}</strong></td>
                                <th>Code Membre</th>
                                <td><strong>{{ $user->sponsor_id ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Grade</th>
                                <td><strong>{{ $gradeLabel }}</strong></td>
                                <th>Niveau</th>
                                <td><strong>{{ (int) ($user->rank_level ?? 0) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Téléphone</th>
                                <td><strong>{{ $user->phone ?? 'N/A' }}</strong></td>
                                <th>Email</th>
                                <td><strong>{{ $user->email ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Parrain</th>
                                <td><strong>{{ $user->parrain?->name ?? 'Aucun' }}</strong></td>
                                <th>Code parrain</th>
                                <td><strong>{{ $user->parrain?->sponsor_id ?? '—' }}</strong></td>
                            </tr>
                        </table>

                        <h2 class="mrd-paper__section">2. Résumé de la Période</h2>
                        <table class="mrd-paper__summary">
                            @if ($isNetwork)
                                <tr>
                                    <th>PV personnel cumulé</th>
                                    <th>PV cumulé réseau</th>
                                </tr>
                                <tr>
                                    <td>{{ number_format($memberStats['cumulative_pv'], 1, ',', ' ') }}</td>
                                    <td>{{ number_format($memberStats['cumulative_network_pv'], 1, ',', ' ') }}</td>
                                </tr>
                            @else
                                <tr>
                                    <th>PV mensuel</th>
                                    <th>PV mensuel groupe</th>
                                    <th>PV personnel cumulé</th>
                                    <th>PV cumulé réseau</th>
                                </tr>
                                <tr>
                                    <td>{{ number_format($memberStats['monthly_pv'], 1, ',', ' ') }}</td>
                                    <td>{{ number_format($memberStats['group_pv'], 1, ',', ' ') }}</td>
                                    <td>{{ number_format($memberStats['cumulative_pv'], 1, ',', ' ') }}</td>
                                    <td>{{ number_format($memberStats['cumulative_network_pv'], 1, ',', ' ') }}</td>
                                </tr>
                            @endif
                        </table>

                        <h2 class="mrd-paper__section">3. Détail sur les filleuls</h2>
                    @else
                        <header class="mrd-paper__header mrd-paper__header--compact">
                            <div><img src="{{ $logoUrl }}" alt="" class="mrd-paper__logo"></div>
                            <div class="mrd-paper__brand">
                                <div class="mrd-paper__company">SALANG GROUP SARL</div>
                                <div class="mrd-paper__ids">Suite — {{ $reportTitle }} · {{ $shortPeriod }} · {{ $user->sponsor_id }}</div>
                            </div>
                        </header>
                        <h2 class="mrd-paper__section">3. Détail sur les filleuls (suite)</h2>
                    @endif

                    @if ($rowsCount === 0)
                        <p class="mrd-paper__empty">
                            {{ $isNetwork
                                ? 'Aucun filleul dans votre réseau.'
                                : 'Aucun filleul n’a enregistré de PV sur cette période.' }}
                        </p>
                    @else
                        @php
                            $rowOffset = $serverPaged
                                ? $rowOffsetBase
                                : ($pageIndex === 0 ? 0 : 6 + (($pageIndex - 1) * 12));
                        @endphp
                        <table class="mrd-paper__table">
                            <thead>
                                <tr>
                                    <th style="width:4%">N°</th>
                                    <th style="width:{{ $isNetwork ? '10%' : '7%' }}">Code</th>
                                    <th style="width:{{ $isNetwork ? '18%' : '12%' }}">Nom &amp; Post-Nom</th>
                                    <th style="width:{{ $isNetwork ? '12%' : '10%' }}">Grade</th>
                                    <th style="width:5%">Gén.</th>
                                    @unless ($isNetwork)
                                        <th style="width:8%">PV mensuel</th>
                                        <th style="width:8%">PV mens groupe</th>
                                    @endunless
                                    <th style="width:{{ $isNetwork ? '14%' : '10%' }}">PV pers. cumulé</th>
                                    <th style="width:{{ $isNetwork ? '14%' : '10%' }}">PV cumulé réseau</th>
                                    <th style="width:{{ $isNetwork ? '10%' : '8%' }}">Code parrain</th>
                                    <th style="width:{{ $isNetwork ? '23%' : '18%' }}">Nom &amp; Post-Nom</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pageRows as $i => $row)
                                    <tr>
                                        <td>{{ $rowOffset + $i + 1 }}</td>
                                        <td><strong>{{ $row['code'] }}</strong></td>
                                        <td class="tl">{{ $row['name'] }}</td>
                                        <td>{{ $row['grade'] }}</td>
                                        <td>{{ $row['generation'] }}</td>
                                        @unless ($isNetwork)
                                            <td class="tr">{{ number_format($row['monthly_pv'], 1, ',', ' ') }}</td>
                                            <td class="tr">{{ number_format($row['group_pv'], 1, ',', ' ') }}</td>
                                        @endunless
                                        <td class="tr">{{ number_format($row['cumulative_pv'], 1, ',', ' ') }}</td>
                                        <td class="tr">{{ number_format($row['cumulative_network_pv'] ?? 0, 1, ',', ' ') }}</td>
                                        <td>{{ $row['sponsor_code'] }}</td>
                                        <td class="tl">{{ $row['sponsor_name'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <p class="mrd-paper__footer">
                        Salang Group SARL — Rapport confidentiel · {{ $user->sponsor_id }} · {{ $periodLabel }}
                    </p>
                    <div class="mrd-paper__page-label">Page {{ $pageNum }} / {{ $totalPages }}</div>
                </article>
            @endforeach
        </div>

        @if ($totalPages > 1)
            <div class="mrd-viewer__pager">
                @if ($serverPaged)
                    <a
                        href="{{ $currentPage > 1 ? route('report.downline.network', ['page' => $currentPage - 1]) : '#' }}"
                        class="mrd-viewer__pager-btn {{ $currentPage <= 1 ? 'is-disabled' : '' }}"
                        @if ($currentPage <= 1) aria-disabled="true" tabindex="-1" @endif
                        aria-label="Page précédente"
                    >‹</a>
                    <div class="mrd-viewer__pager-label">{{ $currentPage }} / {{ $totalPages }}</div>
                    <a
                        href="{{ $currentPage < $totalPages ? route('report.downline.network', ['page' => $currentPage + 1]) : '#' }}"
                        class="mrd-viewer__pager-btn {{ $currentPage >= $totalPages ? 'is-disabled' : '' }}"
                        @if ($currentPage >= $totalPages) aria-disabled="true" tabindex="-1" @endif
                        aria-label="Page suivante"
                    >›</a>
                @else
                    <button type="button" class="mrd-viewer__pager-btn" id="mrdPrev" aria-label="Page précédente">‹</button>
                    <div class="mrd-viewer__pager-label" id="mrdPageLabel">1 / {{ $totalPages }}</div>
                    <button type="button" class="mrd-viewer__pager-btn" id="mrdNext" aria-label="Page suivante">›</button>
                @endif
            </div>
        @endif
    </div>

    <div class="mrd-viewer__actions">
        <a href="{{ route('report.downline') }}" class="mrd-viewer__btn mrd-viewer__btn--ghost">← Retour</a>
        <a href="{{ $pdfDownloadUrl }}" class="mrd-viewer__btn mrd-viewer__btn--primary">Télécharger</a>
    </div>
</div>
@endsection

@push('styles')
    @include('report.downline.partials.paper-styles')
<style>
    a.mrd-viewer__pager-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    a.mrd-viewer__pager-btn.is-disabled {
        opacity: 0.4;
        pointer-events: none;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var root = document.getElementById('mrdViewer');
    if (!root) return;
    if (root.getAttribute('data-server-paged') === '1') return;

    var total = parseInt(root.getAttribute('data-pages') || '1', 10);
    if (total <= 1) return;

    var current = 1;
    var label = document.getElementById('mrdPageLabel');
    var prev = document.getElementById('mrdPrev');
    var next = document.getElementById('mrdNext');
    var papers = root.querySelectorAll('.mrd-paper');

    function render() {
        papers.forEach(function (paper) {
            paper.classList.toggle('is-active', parseInt(paper.getAttribute('data-page'), 10) === current);
        });
        if (label) label.textContent = current + ' / ' + total;
        if (prev) prev.disabled = current <= 1;
        if (next) next.disabled = current >= total;
    }

    if (prev) prev.addEventListener('click', function () {
        if (current > 1) { current -= 1; render(); }
    });
    if (next) next.addEventListener('click', function () {
        if (current < total) { current += 1; render(); }
    });

    render();
})();
</script>
@endpush

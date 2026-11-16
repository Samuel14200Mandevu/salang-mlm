{{-- resources/views/cashier/commissions-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Commissions - Salang Group</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Times New Roman', Times, serif, Arial; 
            font-size: 14px; 
            color: #000; 
            padding: 0;
            margin: 0;
            background: #fff;
        }

        @include('partials.pdf._styles')
        @include('partials.pdf._letterhead_full_bleed')

        /* TITRE */
        .main-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 6px;
        }
        .form-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            padding: 0 3px;
        }

        /* SECTIONS DE TITRE */
        .section-header {
            font-size: 14.5px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
        }

        /* TABLEAU DES DONNÉES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table th {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            padding: 5px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 4px 3px;
            border-bottom: 1.5px dotted #000;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
            font-size: 12.5px;
        }
        .data-table td.text-left { text-align: left; }
        
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
            font-size: 13px;
            background-color: #fff;
        }

        .amount-positive {
            color: #000;
            font-weight: bold;
        }
        .amount-zero {
            color: #666;
        }

        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: #666;
            font-size: 13.5px;
        }

        .page-content {
            background: #fff;
        }

        @page {
            size: A4 portrait;
            margin: 0 0 12mm 0;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            @page { 
                size: A4 portrait;
                margin: 0 0 12mm 0;
            }
        }
    </style>
</head>
<body>

@include('partials.pdf.letterhead', ['letterheadCompact' => false, 'letterheadImgHeight' => 330])

<!-- PAGE CONTENT -->
<div class="page-content page-content-padded">

    <!-- TITRE + META -->
    <div class="main-title">Rapport des Commissions</div>
    <div class="form-meta">
        <div>Période : <strong>{{ $period }}</strong></div>
        <div>Généré le : <strong>{{ $date->format('d/m/Y') }}</strong></div>
    </div>

    <!-- SECTION : COMMISSIONS -->
    <div class="section-header">Détails des Commissions par Membre</div>
    
    @if(count($members) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:8%;">Période</th>
                <th style="width:11%;">Code ID</th>
                <th style="width:20%;">Nom Complet</th>
                <th style="width:5%;">Niv.</th>
                <th style="width:6%;">PV</th>
                <th style="width:9%;">Sponsor</th>
                <th style="width:9%;">Direct</th>
                <th style="width:9%;">Indirect</th>
                <th style="width:11%;">Leadership</th>
                <th style="width:12%;">Cash POS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $member)
            @php
                $user = $member['user'];
            @endphp
            <tr>
                <td>{{ $member['period'] ?? $period }}</td>
                <td><strong>{{ $user?->sponsor_id ?? 'N/A' }}</strong></td>
                <td class="text-left"><strong>{{ $user?->name ?? 'N/A' }}</strong></td>
                <td>{{ $user?->rank_level ?? '-' }}</td>
                <td>{{ $member['monthly_pv'] ?? 0 }}</td>
                <td>
                    @if(($member['sponsor'] ?? 0) > 0)
                        <span class="amount-positive">${{ number_format($member['sponsor'], 2) }}</span>
                    @else
                        <span class="amount-zero">-</span>
                    @endif
                </td>
                <td>
                    @if(($member['direct'] ?? 0) > 0)
                        <span class="amount-positive">${{ number_format($member['direct'], 2) }}</span>
                    @else
                        <span class="amount-zero">-</span>
                    @endif
                </td>
                <td>
                    @if(($member['indirect'] ?? 0) > 0)
                        <span class="amount-positive">${{ number_format($member['indirect'], 2) }}</span>
                    @else
                        <span class="amount-zero">-</span>
                    @endif
                </td>
                <td>
                    @if(($member['leadership'] ?? 0) > 0)
                        <span class="amount-positive">${{ number_format($member['leadership'], 2) }}</span>
                    @else
                        <span class="amount-zero">-</span>
                    @endif
                </td>
                <td>
                    @if(($member['cash_pos'] ?? 0) > 0)
                        <span class="amount-positive">${{ number_format($member['cash_pos'], 2) }}</span>
                    @else
                        <span class="amount-zero">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align:right; font-weight:bold;">TOTAUX :</td>
                <td>${{ number_format($totals['sponsor'], 2) }}</td>
                <td>${{ number_format($totals['direct'], 2) }}</td>
                <td>${{ number_format($totals['indirect'], 2) }}</td>
                <td>${{ number_format($totals['leadership'], 2) }}</td>
                <td>${{ number_format($totals['cash_pos'], 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="9" style="text-align:right; font-weight:bold; color:#0E2F76;">TOTAL GÉNÉRAL :</td>
                <td style="color:#0E2F76; font-size:14px;"><strong>${{ number_format($totals['grand_total'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">
        <p>Aucune commission enregistrée pour la période {{ $period }}</p>
    </div>
    @endif

    @include('partials.pdf.footer', ['pdfFooterLabel' => 'RAPPORT OFFICIEL – SALANG GROUP SARL'])

</div>
</body>
</html>
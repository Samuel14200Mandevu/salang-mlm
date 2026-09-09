{{-- resources/views/admin/pv/commission-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Commissions - {{ $period }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Times New Roman', Times, serif, Arial; 
            font-size: 11px; 
            color: #000; 
            padding: 10px 18px 45px 18px;
            background: #fff;
            line-height: 1.3;
        }

        /* PIED DE PAGE PERMANENT */
        .footer-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            background-color: #fff;
            padding: 4px 20px 6px 20px;
        }
        .footer-left {
            font-size: 9px;
            line-height: 1.3;
            text-align: center;
            color: #000;
        }
        .footer-line {
            border-bottom: 1.5px solid #cbd5e0;
            margin-top: 3px;
        }
        .footer-right {
            font-size: 8px;
            color: #666;
            text-align: right;
            margin-top: 1px;
        }

        /* EN-TÊTE */
        .report-header {
            width: 100%;
            border-bottom: 2.5px solid #8b0000;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
            padding: 2px 4px;
        }
        .logo-cell {
            width: 65px;
            text-align: center;
        }
        .logo-cell img {
            max-height: 45px;
            width: auto;
        }
        .header-center {
            text-align: center;
        }
        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #558b2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .header-text {
            font-size: 7.5px;
            line-height: 1.2;
            font-weight: bold;
        }
        .address {
            font-size: 7px;
            color: #000;
            margin-top: 1px;
            line-height: 1.2;
            font-weight: normal;
        }
        .address .city {
            font-weight: bold;
            color: #0E2F76;
        }

        /* TITRE ET META */
        .main-title {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            color: #0E2F76;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .form-meta {
            width: 100%;
            margin-bottom: 4px;
            font-size: 10.5px;
            font-weight: bold;
            border-collapse: collapse;
        }
        .form-meta td {
            padding: 2px 0;
        }

        /* SECTIONS NUMÉROTÉES */
        .section-header {
            font-size: 11.5px;
            font-weight: bold;
            color: #558b2f;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 2px;
            margin: 6px 0 4px 0;
            text-transform: uppercase;
            clear: both;
        }

        /* SECTION MEMBRE */
        .member-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            border: 1px solid #000;
        }
        .member-info td {
            padding: 3px 6px;
            font-size: 10px;
            border: 1px solid #000;
        }
        .member-info .label {
            font-weight: bold;
            background-color: #f2f2f2;
            width: 15%;
        }
        .member-info .value {
            width: 35%;
        }

        /* TABLEAU DES TOTAUX */
        .totals-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 6px 0;
        }
        .totals-grid td {
            padding: 4px 8px;
            border: 1px solid #000;
            text-align: center;
            font-size: 10.5px;
            background-color: #f9f9f9;
        }
        .totals-grid .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            background-color: #f2f2f2;
        }
        .totals-grid .amount {
            font-weight: bold;
            font-size: 12px;
        }
        .direct-color { color: #4F46E5; }
        .indirect-color { color: #2563EB; }
        .leadership-color { color: #A65A0E; }
        .cash-color { color: #16a34a; }

        /* TABLEAU DES COMMISSIONS */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 4px 0;
            font-size: 9px;
        }
        
        .data-table thead {
            display: table-header-group;
        }
        
        .data-table tr {
            page-break-inside: avoid;
        }
        
        .data-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 3px 4px;
            border: 1px solid #000;
            text-align: center;
        }
        .data-table td {
            padding: 2px 4px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5px;
        }
        .data-table td.text-left { text-align: left; }
        .data-table td.text-right { text-align: right; }

        .data-table .total-row td {
            font-weight: bold;
            font-size: 9.5px;
            background-color: #f9f9f9;
        }

        /* BADGES AVEC COULEURS */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-direct { background: rgba(79, 70, 229, 0.15); color: #4F46E5; }
        .badge-indirect { background: rgba(37, 99, 235, 0.15); color: #2563EB; }
        .badge-leadership { background: rgba(166, 90, 14, 0.15); color: #A65A0E; }
        .badge-cash { background: rgba(22, 163, 74, 0.15); color: #16a34a; }

        /* EN-TÊTE DE GROUPE DE BONUS */
        .bonus-group-header {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .bonus-group-header td {
            padding: 4px 6px;
            border: 1px solid #000;
            text-align: left;
        }
        .bonus-group-direct .bonus-group-header td {
            background-color: rgba(79, 70, 229, 0.10);
            color: #4F46E5;
        }
        .bonus-group-indirect .bonus-group-header td {
            background-color: rgba(37, 99, 235, 0.10);
            color: #2563EB;
        }
        .bonus-group-leadership .bonus-group-header td {
            background-color: rgba(166, 90, 14, 0.10);
            color: #A65A0E;
        }
        .bonus-group-cash .bonus-group-header td {
            background-color: rgba(22, 163, 74, 0.10);
            color: #16a34a;
        }

        /* SOUS-TOTAL DE GROUPE */
        .bonus-group-total td {
            font-weight: bold;
            font-size: 8.5px;
            background-color: #f9f9f9;
            border-top: 2px solid #000;
        }
        .bonus-group-direct .bonus-group-total td {
            color: #4F46E5;
        }
        .bonus-group-indirect .bonus-group-total td {
            color: #2563EB;
        }
        .bonus-group-leadership .bonus-group-total td {
            color: #A65A0E;
        }
        .bonus-group-cash .bonus-group-total td {
            color: #16a34a;
        }

        .beneficiaire-title {
            font-size: 10.5px;
            font-weight: bold;
            padding: 3px 8px;
            background: #f2f2f2;
            border-left: 3px solid #8b0000;
            margin: 6px 0 3px 0;
            page-break-after: avoid;
        }
        .beneficiaire-title .total-badge {
            float: right;
            font-weight: bold;
            color: #0E2F76;
        }

        .beneficiaire-section {
            margin-bottom: 6px;
        }

        @media print {
            body { padding: 8px 15px 40px 15px; }
            @page { 
                size: A4 portrait;
                margin: 0.4cm 0.5cm; 
            }
        }
    </style>
</head>
<body>

    <!-- PIED DE PAGE FIXE -->
    <div class="footer-container">
        <div class="footer-left">
            <strong>Salang Group International SARL</strong> — Service Administratif &amp; Réseau<br>
            Site web : www.salanggroup.com &nbsp;|&nbsp; E-mail : support@salanggroup.com
        </div>
        <div class="footer-line"></div>
        <div class="footer-right">Généré le {{ $date_generated }}</div>
    </div>

    <!-- CONTENU PRINCIPAL -->
    <div class="page-content">

        <!-- EN-TÊTE -->
        <div class="report-header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @php
                            $logoPath = public_path('images/salang_logo.png');
                            $logoBase64 = '';
                            if(file_exists($logoPath)) {
                                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                            }
                        @endphp
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" alt="Salang Logo">
                        @endif
                    </td>
                    <td class="header-center">
                        <div class="company-title">SALANG GROUP SARL</div>
                        <div class="header-text">
                            N° IDN:22-M7300-N63464Q &nbsp; N° RCCM:CD/BKV/RCCM/20-B-00116<br>
                            Contact : +243 975 220 079 &nbsp; Web:www.salanggroup.com &nbsp; Email:support@salanggroup.com
                        </div>
                        <div class="address">
                            <span class="city">Kinshasa :</span> 4 AV, Ixoras 382, 7eme Rue Resid. &nbsp;|&nbsp;
                            <span class="city">Bukavu :</span> N°4 Av. FIZI, Q. Nyawera, C/Ibanda &nbsp;|&nbsp;
                            <span class="city">Goma :</span> Rondpoint Chikudu, Bat. KBS 3e N.
                        </div>
                    </td>
                    <td class="logo-cell">
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" alt="Salang Logo">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- TITRE -->
        <div class="main-title">Rapport des Commissions</div>
        
        <table class="form-meta">
            <tr>
                <td style="text-align: left; width:50%;">Période : <strong>{{ $period }}</strong></td>
                <td style="text-align: right; width:50%;">Date : <strong>{{ $date_generated }}</strong></td>
            </tr>
        </table>

        <!-- 1. INFORMATIONS DU MEMBRE -->
        <div class="section-header">1. Informations du Membre</div>
        
        @if(isset($user) && $user)
        <table class="member-info">
            <tr>
                <td class="label">Nom complet</td>
                <td class="value"><strong>{{ $user->name }}</strong></td>
                <td class="label">Code Membre</td>
                <td class="value"><strong>{{ $user->sponsor_id ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Grade</td>
                <td class="value"><strong>{{ $user->rank ?? 'Distributeur' }}</strong></td>
                <td class="label">Niveau</td>
                <td class="value"><strong>{{ $user->rank_level ?? 1 }}</strong></td>
            </tr>
            <tr>
                <td class="label">Package</td>
                <td class="value"><strong>{{ $user->package?->name ?? 'Aucun' }}</strong></td>
                <td class="label">Téléphone</td>
                <td class="value"><strong>{{ $user->phone ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="value"><strong>{{ $user->email ?? 'N/A' }}</strong></td>
                <td class="label">Statut</td>
                <td class="value"><strong>{{ $user->is_active ? 'Actif' : 'Inactif' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Parrain</td>
                <td class="value"><strong>{{ $user->parrain?->name ?? 'Aucun' }}</strong></td>
                <td class="label">Date d'inscription</td>
                <td class="value"><strong>{{ $user->created_at?->format('d/m/Y') ?? 'N/A' }}</strong></td>
            </tr>
        </table>
        @endif

        <!-- PV de la période sélectionnée -->
        @if(isset($periodPV) && $periodPV > 0)
        <div style="margin: 4px 0; padding: 4px 8px; background: #f9f9f9; border: 1px solid #ddd; text-align: center; font-size: 11px;">
            <strong>PV pour la période {{ $period }} :</strong> {{ number_format($periodPV, 1) }} PV
        </div>
        @endif

        <!-- 2. RÉSUMÉ DES GAINS -->
        <div class="section-header">2. Résumé des Gains</div>
        <table class="totals-grid">
            <tr>
                <td class="label" style="width:16%;">Type</td>
                <td class="label" style="width:17%;">Direct</td>
                <td class="label" style="width:17%;">Indirect</td>
                <td class="label" style="width:17%;">Leadership</td>
                <td class="label" style="width:17%;">Cash POS</td>
                <td class="label" style="width:16%;">Total</td>
            </tr>
            <tr>
                <td class="label">Montant</td>
                <td class="amount direct-color">${{ number_format($totals['direct'] ?? 0, 2) }}</td>
                <td class="amount indirect-color">${{ number_format($totals['indirect'] ?? 0, 2) }}</td>
                <td class="amount leadership-color">${{ number_format($totals['leadership'] ?? 0, 2) }}</td>
                <td class="amount cash-color">${{ number_format($totals['cash_pos'] ?? 0, 2) }}</td>
                <td class="amount" style="color:#0E2F76;">${{ number_format($totals['total'] ?? 0, 2) }}</td>
            </tr>
        </table>

        <!-- 3. DÉTAIL PAR BÉNÉFICIAIRE -->
        <div class="section-header">3. Détail des Commissions</div>
        
        @foreach($beneficiaires as $beneficiaire)
            <div class="beneficiaire-section">
                <div class="beneficiaire-title">
                    {{ $beneficiaire['name'] }}
                    <span class="total-badge">${{ number_format($beneficiaire['total'], 2) }}</span>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:10%;">Type</th>
                            <th style="width:15%;">De</th>
                            <th style="width:12%;">Montant</th>
                            <th style="width:8%;">Taux</th>
                            <th style="width:8%;">Gén.</th>
                            <th style="width:47%;">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Définir l'ordre des bonus pour le PDF
                            $bonusOrder = ['direct', 'indirect', 'leadership', 'cash_pos'];
                            $bonusLabels = [
                                'direct' => 'Direct Bonus',
                                'indirect' => 'Indirect Bonus',
                                'leadership' => 'Leadership Bonus',
                                'cash_pos' => 'Cash POS Bonus'
                            ];
                            
                            // Regrouper les détails par type
                            $groupedDetails = [];
                            foreach ($bonusOrder as $type) {
                                $groupedDetails[$type] = [];
                            }
                            
                            foreach ($beneficiaire['details'] as $detail) {
                                if (isset($groupedDetails[$detail['type']])) {
                                    $groupedDetails[$detail['type']][] = $detail;
                                }
                            }
                            
                            $totalParType = [];
                            foreach ($bonusOrder as $type) {
                                $totalParType[$type] = collect($groupedDetails[$type])->sum('amount');
                            }
                        @endphp
                        
                        @foreach($bonusOrder as $type)
                            @if(count($groupedDetails[$type]) > 0)
                                <!-- En-tête du groupe de bonus -->
                                <tr class="bonus-group-{{ $type }}">
                                    <td colspan="6" class="bonus-group-header">
                                        {{ $bonusLabels[$type] ?? ucfirst($type) }}
                                        <span style="float: right; font-weight: bold; color: 
                                            @if($type == 'direct') #4F46E5
                                            @elseif($type == 'indirect') #2563EB
                                            @elseif($type == 'leadership') #A65A0E
                                            @elseif($type == 'cash_pos') #16a34a
                                            @endif
                                        ">
                                            Total: ${{ number_format($totalParType[$type], 2) }}
                                        </span>
                                    </td>
                                </tr>
                                
                                <!-- Lignes du groupe -->
                                @foreach($groupedDetails[$type] as $detail)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $detail['type'] }}">
                                            {{ ucfirst($detail['type']) }}
                                        </span>
                                    </td>
                                    <td class="text-left">{{ $detail['from_user'] }}</td>
                                    <td class="text-right">${{ number_format($detail['amount'], 2) }}</td>
                                    <td>{{ $detail['percentage'] }}%</td>
                                    <td>{{ $detail['generation'] }}</td>
                                    <td class="text-left" style="font-size:8px;">{{ Str::limit($detail['description'], 45) }}</td>
                                </tr>
                                @endforeach
                                
                                <!-- Sous-total du groupe -->
                                <tr class="bonus-group-{{ $type }}">
                                    <td colspan="5" class="bonus-group-total" style="text-align: right;">
                                        Sous-total {{ $bonusLabels[$type] ?? ucfirst($type) }}
                                    </td>
                                    <td class="bonus-group-total" style="text-align: right; font-weight: bold;">
                                        ${{ number_format($totalParType[$type], 2) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="5" style="text-align:right;">TOTAL {{ strtoupper($beneficiaire['name']) }} :</td>
                            <td style="text-align:right; color:#0E2F76; font-size:10px;">
                                <strong>${{ number_format($beneficiaire['total'], 2) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

    </div>

</body>
</html>
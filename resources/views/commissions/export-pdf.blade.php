<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Commissions - SALANG GROUP</title>
    <style>
        /* ===== STYLES GÉNÉRAUX ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 9px;
            color: #1a202c;
            padding: 20px 25px;
            background: #ffffff;
            line-height: 1.3;
        }
        
        /* ===== EN-TÊTE OFFICIEL ===== */
        .header {
            border-bottom: 2px solid #0E2F76;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-logo {
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .header-company {
            line-height: 1.3;
        }
        .header-company .name {
            font-size: 18px;
            font-weight: 700;
            color: #0E2F76;
            letter-spacing: 1px;
        }
        .header-company .infos {
            font-size: 7px;
            color: #4a5568;
        }
        .header-company .infos span {
            color: #0E2F76;
            font-weight: 600;
        }
        
        .header-right {
            text-align: right;
            font-size: 7px;
            color: #4a5568;
            line-height: 1.5;
            flex-shrink: 0;
        }
        .header-right .contact {
            font-weight: 700;
            color: #0E2F76;
            font-size: 8px;
        }
        .header-right .web {
            color: #0E2F76;
            text-decoration: none;
            font-weight: 600;
        }
        .header-right .email {
            color: #0E2F76;
            text-decoration: none;
            font-weight: 600;
        }
        .header-right .address {
            font-size: 6px;
            color: #718096;
            font-style: italic;
        }
        
        .header-divider {
            border-top: 1px dashed #dce3ed;
            margin: 5px 0 8px 0;
        }
        
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 16px;
            font-weight: 700;
            color: #0E2F76;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .header-title .subtitle {
            font-size: 8px;
            color: #718096;
        }
        .header-title .user-info {
            font-size: 7.5px;
            color: #4a5568;
            margin-top: 2px;
            font-weight: 600;
        }
        
        /* ===== STATISTIQUES EN COLONNES ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
            margin-bottom: 12px;
        }
        .stats-grid .stat-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .stats-grid .stat-box .label {
            font-size: 6px;
            text-transform: uppercase;
            color: #718096;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .stats-grid .stat-box .value {
            font-size: 13px;
            font-weight: 700;
            color: #0E2F76;
            margin-top: 1px;
        }
        .stats-grid .stat-box .value.green { color: #22c55e; }
        .stats-grid .stat-box .value.yellow { color: #f59e0b; }
        .stats-grid .stat-box .value.purple { color: #8b5cf6; }
        .stats-grid .stat-box .value.blue { color: #3b82f6; }
        
        /* ===== DISTRIBUTION PAR TYPE EN COLONNES ===== */
        .type-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 14px;
        }
        .type-grid .type-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .type-grid .type-box .label {
            font-size: 6.5px;
            text-transform: uppercase;
            color: #718096;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .type-grid .type-box .value {
            font-size: 12px;
            font-weight: 700;
            color: #0E2F76;
            margin-top: 1px;
        }
        .type-grid .type-box .count {
            font-size: 6.5px;
            color: #a0aec0;
        }
        
        .type-grid .type-box.type-sponsor { border-left: 3px solid #6366f1; }
        .type-grid .type-box.type-direct { border-left: 3px solid #3b82f6; }
        .type-grid .type-box.type-indirect { border-left: 3px solid #f59e0b; }
        .type-grid .type-box.type-leadership { border-left: 3px solid #22c55e; }
        
        /* ===== TABLEAU ===== */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            min-width: 450px;
        }
        .table thead th {
            background: #0E2F76;
            color: white;
            padding: 5px 7px;
            text-align: left;
            font-size: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            white-space: nowrap;
        }
        .table thead th:last-child {
            text-align: center;
        }
        .table tbody td {
            padding: 4px 7px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 7.5px;
            vertical-align: middle;
        }
        .table tbody td:last-child {
            text-align: center;
        }
        .table tbody tr:nth-child(even) {
            background: #f7fafc;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tfoot {
            background: #edf2f7;
            font-weight: 700;
        }
        .table tfoot td {
            padding: 5px 7px;
            border-top: 2px solid #0E2F76;
            font-size: 7.5px;
        }
        .table tfoot td:last-child {
            text-align: center;
        }
        
        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 6px;
            font-size: 6px;
            font-weight: 700;
            white-space: nowrap;
            text-transform: uppercase;
        }
        .badge-paid {
            background: rgba(34, 197, 94, 0.12);
            color: #22c55e;
        }
        .badge-pending {
            background: rgba(245, 158, 11, 0.12);
            color: #f59e0b;
        }
        .badge-cancelled {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
        }
        .badge-type {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 6px;
            font-size: 6px;
            font-weight: 700;
            white-space: nowrap;
            text-transform: uppercase;
        }
        .badge-type-sponsor { background: rgba(99,102,241,0.15); color: #6366f1; }
        .badge-type-direct { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .badge-type-indirect { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-type-leadership { background: rgba(34,197,94,0.15); color: #22c55e; }
        .badge-type-retail { background: rgba(236,72,153,0.15); color: #ec4899; }
        
        /* ===== TEXTE ===== */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 700; }
        .text-primary { color: #0E2F76; }
        .text-success { color: #22c55e; }
        .text-warning { color: #f59e0b; }
        .text-muted { color: #a0aec0; }
        
        /* ===== PIED DE PAGE ===== */
        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #a0aec0;
            font-size: 6.5px;
        }
        .footer .company-name {
            font-weight: 700;
            color: #0E2F76;
            font-size: 8px;
        }
        .footer .info {
            display: flex;
            justify-content: space-between;
            font-size: 6px;
            color: #718096;
            margin-top: 3px;
            flex-wrap: wrap;
            gap: 3px;
        }
        .footer .watermark {
            font-size: 5px;
            color: #cbd5e0;
            margin-top: 2px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            body { padding: 12px 15px; }
            .header-top { flex-direction: column; align-items: stretch; }
            .header-left { justify-content: center; }
            .header-right { text-align: center; }
            .header-company .name { font-size: 15px; }
            .header-logo { width: 45px; height: 45px; }
            .header-title h1 { font-size: 14px; }
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
            .type-grid { grid-template-columns: repeat(2, 1fr); }
            .table { font-size: 6.5px; min-width: 350px; }
        }
        
        @media (max-width: 480px) {
            body { padding: 8px 10px; }
            .header-company .name { font-size: 12px; }
            .header-company .infos { font-size: 5.5px; }
            .header-right { font-size: 5.5px; }
            .header-title h1 { font-size: 11px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .type-grid { grid-template-columns: 1fr 1fr; }
            .table { font-size: 5.5px; min-width: 280px; }
            .table thead th { font-size: 4.5px; padding: 2px 3px; }
            .table tbody td { font-size: 5px; padding: 2px 3px; }
            .badge { font-size: 4.5px; padding: 1px 3px; }
            .badge-type { font-size: 4.5px; padding: 1px 3px; }
        }
        
        @media print {
            body { background: white; padding: 15px 20px; }
            .header { border-bottom-color: #0E2F76; }
            .table thead th { background: #0E2F76 !important; color: white !important; }
            .table tbody tr:nth-child(even) { background: #f7fafc !important; }
            .stats-grid .stat-box { background: #f7fafc !important; }
            .type-grid .type-box { background: #f7fafc !important; }
            .footer { border-top-color: #e2e8f0 !important; }
        }
    </style>
</head>
<body>
    <!-- ============================================================ -->
    <!-- EN-TÊTE SALANG GROUP - OFFICIEL                              -->
    <!-- ============================================================ -->
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <div class="header-logo">
                    @php
                        $logoPath = public_path('images/salang_logo.png');
                        if(file_exists($logoPath)):
                    @endphp
                        <img src="{{ $logoPath }}" alt="SALANG GROUP">
                    @else
                        <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" stroke="#0E2F76" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                            <text x="5" y="22" font-size="6" fill="#0E2F76" font-weight="700">SG</text>
                        </svg>
                    @endif
                </div>
                <div class="header-company">
                    <div class="name">SALANG GROUP</div>
                    <div class="infos">
                        N° IDN: <span>22-M7</span><br>
                        <span>300-N63464 Q</span> N° RCCM: <span>CD/BKV/RCCM/20-B-00116</span> NUMERO IMPORT-EXPORT: <span>0024/CBX-21/I000439SK/Z</span>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="contact">📞 +243 999 086 990 - 975 220 079</div>
                <div>🌐 <span class="web">www.salang-group.com</span></div>
                <div>✉️ <span class="email">support@salanggroup.com</span></div>
                <div class="address">« 382 AV ixoras Limeté Résidentielle, Kinshasa RD Congo</div>
            </div>
        </div>
        
        <div class="header-divider"></div>
        
        <div class="header-title">
            <h1>RAPPORT DES COMMISSIONS</h1>
            <p class="subtitle">
                Généré le {{ $generated_at->format('d/m/Y à H:i') }}
                @if(isset($filters))
                    | Statut: {{ $filters['status'] ?? 'Tous' }} | 
                    Type: {{ $filters['type'] ?? 'Tous' }} | 
                    Période: {{ $filters['period'] ?? 'Tous' }}
                @endif
            </p>
            <p class="user-info">{{ $user->name }} • {{ $user->email }}</p>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- STATISTIQUES EN COLONNES                                      -->
    <!-- ============================================================ -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Total des commissions</div>
            <div class="value">${{ number_format($stats['total'] ?? 0, 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Nombre total</div>
            <div class="value purple">{{ $stats['count'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Commissions payées</div>
            <div class="value green">${{ number_format($stats['paid'] ?? 0, 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">En attente</div>
            <div class="value yellow">${{ number_format($stats['pending'] ?? 0, 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Taux de paiement</div>
            <div class="value blue">
                @php
                    $total = $stats['total'] ?? 0;
                    $paid = $stats['paid'] ?? 0;
                    $rate = $total > 0 ? ($paid / $total) * 100 : 0;
                @endphp
                {{ number_format($rate, 1) }}%
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- DISTRIBUTION PAR TYPE EN COLONNES                            -->
    <!-- ============================================================ -->
    @if(isset($byType) && $byType->count() > 0)
    <div class="type-grid">
        @foreach($byType as $type => $data)
            <div class="type-box type-{{ $type }}">
                <div class="label">{{ $data['label'] ?? ucfirst($type) }}</div>
                <div class="value">${{ number_format($data['total'] ?? 0, 2) }}</div>
                <div class="count">{{ $data['count'] ?? 0 }} commission(s)</div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- ============================================================ -->
    <!-- TABLEAU DES COMMISSIONS                                       -->
    <!-- ============================================================ -->
    @if(isset($commissions) && $commissions->count() > 0)
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:12%;">Date</th>
                        <th style="width:10%;">Type</th>
                        <th style="width:16%;">De</th>
                        <th style="width:24%;">Item</th>
                        <th style="width:18%;text-align:right;">Montant</th>
                        <th style="width:20%;text-align:center;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $commission)
                        <tr>
                            <td>{{ $commission->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge-type badge-type-{{ $commission->type }}">
                                    {{ ucfirst($commission->type) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($commission->fromUser?->name ?? 'Système', 15) }}</td>
                            <td>
                                @if($commission->package_id)
                                    {{ Str::limit($commission->package?->name ?? 'N/A', 20) }}
                                @elseif($commission->product_id)
                                    {{ Str::limit($commission->product?->name ?? 'N/A', 20) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-right font-bold text-success">
                                +${{ number_format($commission->amount, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $commission->status }}">
                                    {{ $commission->status == 'paid' ? 'Payé' : ($commission->status == 'pending' ? 'En attente' : 'Annulé') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right font-bold">TOTAL GÉNÉRAL</td>
                        <td class="text-right text-primary font-bold" style="font-size:9px;">
                            ${{ number_format($stats['total'] ?? 0, 2) }}
                        </td>
                        <td class="text-center font-bold">
                            {{ $stats['count'] ?? 0 }} commissions
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div style="text-align:center;padding:25px 15px;color:#a0aec0;border:1px dashed #e2e8f0;border-radius:4px;margin-top:10px;">
            <svg style="width:35px;height:35px;margin:0 auto 6px;display:block;color:#cbd5e0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="font-size:11px;font-weight:600;color:#4a5568;">Aucune commission trouvée</p>
            <p style="font-size:8px;margin-top:2px;">Modifiez les filtres pour voir plus de résultats</p>
        </div>
    @endif

    <!-- ============================================================ -->
    <!-- PIED DE PAGE                                                  -->
    <!-- ============================================================ -->
    <div class="footer">
        <div>
            <span class="company-name">SALANG GROUP</span>
            <span style="color:#718096;"> - Rapport généré automatiquement</span>
        </div>
        <div class="info">
            <span class="left">© {{ date('Y') }} SALANG GROUP. Tous droits réservés.</span>
            <span class="center">Document confidentiel</span>
            <span class="right">Page 1/1</span>
        </div>
        <div class="watermark">
            Ce document est la propriété de SALANG GROUP - Toute reproduction interdite
        </div>
    </div>
</body>
</html>
{{-- resources/views/admin/reports/pdf/layout.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Salang Group - Rapport')</title>
    <style>
        /* ===== STYLES GÉNÉRAUX ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
            padding: 20px;
        }
        
        /* ===== EN-TÊTE ===== */
        .header {
            border-bottom: 3px solid #1a56db;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .header-left .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .header-left .company-info {
            display: flex;
            flex-direction: column;
        }
        .header-left .company-info .company-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a56db;
            letter-spacing: 1px;
        }
        .header-left .company-info .company-slogan {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
        .header-right {
            text-align: right;
            font-size: 9px;
            color: #666;
            line-height: 1.6;
        }
        .header-right .title {
            font-size: 14px;
            font-weight: 700;
            color: #1a56db;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-right .ref {
            font-size: 8px;
            color: #999;
        }
        
        /* ===== COORDONNÉES ===== */
        .contact-bar {
            background: #f3f4f6;
            padding: 6px 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 8px;
            color: #555;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .contact-bar span {
            margin-right: 15px;
        }
        
        /* ===== TABLEAUX ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table thead th {
            background: #1a56db;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        table tbody tr:hover {
            background: #eff6ff;
        }
        
        /* ===== STATISTIQUES ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 10px 0 15px 0;
        }
        .stats-grid .stat-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 10px 12px;
            text-align: center;
        }
        .stats-grid .stat-box .stat-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stats-grid .stat-box .stat-value {
            font-size: 16px;
            font-weight: 700;
            color: #1a56db;
            margin-top: 2px;
        }
        .stats-grid .stat-box .stat-value.green { color: #16a34a; }
        .stats-grid .stat-box .stat-value.yellow { color: #ca8a04; }
        .stats-grid .stat-box .stat-value.red { color: #dc2626; }
        .stats-grid .stat-box .stat-value.purple { color: #7c3aed; }
        
        /* ===== STATUT BADGE ===== */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-warning { background: #fef3c7; color: #ca8a04; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        .badge-info { background: #dbeafe; color: #2563eb; }
        
        /* ===== PIED DE PAGE ===== */
        .footer {
            border-top: 2px solid #e5e7eb;
            padding-top: 12px;
            margin-top: 20px;
            font-size: 8px;
            color: #999;
            display: flex;
            justify-content: space-between;
        }
        .footer .page-number:after {
            content: "Page {PAGE_NUM} / {PAGE_COUNT}";
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 600px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .header { flex-direction: column; text-align: center; }
            .header-right { text-align: center; margin-top: 10px; }
            .contact-bar { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
    
    <!-- ===== EN-TÊTE ===== -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/salang_logo.png') }}" alt="Salang Group" class="logo">
            <div class="company-info">
                <span class="company-name">SALANG GROUP</span>
                <span class="company-slogan">Santé & Bien-être - Marketing de réseau relationnel</span>
            </div>
        </div>
        <div class="header-right">
            <div class="title">@yield('report_title', 'RAPPORT')</div>
            <div>N° RCCM: CD/BKV/RCCM/20-B-00116</div>
            <div>N° IMPORT-EXPORT: 0024/CBX-21/1000439SK/Z</div>
            <div class="ref">IDN: 22-M7</div>
        </div>
    </div>
    
    <!-- ===== COORDONNÉES ===== -->
    <div class="contact-bar">
        <span> +243 999 086 990 - 975 220 079</span>
        <span> support@salanggroup.com</span>
        <span> www.salang-group.com</span>
        <span> 382 AV Ixoras Limeté Résidentielle, Kinshasa RD Congo</span>
    </div>
    
    <!-- ===== DATE ET RÉFÉRENCE ===== -->
    <div style="display:flex; justify-content:space-between; font-size:8px; color:#888; margin-bottom:10px;">
        <span>Date: {{ now()->format('d/m/Y H:i') }}</span>
        <span>Réf: RPT-{{ now()->format('Ymd') }}-{{ strtoupper(uniqid()) }}</span>
    </div>
    
    <!-- ===== CONTENU ===== -->
    @yield('content')
    
    <!-- ===== PIED DE PAGE ===== -->
    <div class="footer">
        <span>Salang Group Health Care - Règlement d'ordre intérieur</span>
        <span class="page-number"></span>
    </div>
    
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Consultation #{{ $consultation->id ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #e8edf3;
        }
        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: #0A254E;
            color: #fff;
        }
        .toolbar a,
        .toolbar .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-back {
            background: #fff;
            color: #0A254E;
        }
        .btn-print {
            background: #C5A04D;
            color: #0A254E;
        }
        .viewer {
            width: 100%;
            height: calc(100vh - 52px);
            border: 0;
            background: #525659;
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('cashier.consultations.show', $consultation) }}" class="btn-back">← Retour</a>
    </div>
    <iframe class="viewer" src="{{ route('cashier.consultations.print.pdf', $consultation) }}" title="Fiche de consultation"></iframe>
</body>
</html>

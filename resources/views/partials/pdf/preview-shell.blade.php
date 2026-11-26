<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title>{{ $title ?? 'Aperçu PDF' }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            height: 100%;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #e8edf3;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
        }
        .toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            padding: 10px 12px;
            padding-top: max(10px, env(safe-area-inset-top));
            padding-left: max(12px, env(safe-area-inset-left));
            padding-right: max(12px, env(safe-area-inset-right));
            background: #0A254E;
            color: #fff;
            flex-shrink: 0;
        }
        .toolbar__title {
            flex: 1 1 auto;
            min-width: 0;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }
        .toolbar a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-back {
            background: #fff;
            color: #0A254E;
        }
        .btn-download {
            background: #C5A04D;
            color: #0A254E;
        }
        .viewer-wrap {
            flex: 1 1 auto;
            min-height: 0;
            position: relative;
            background: #525659;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .viewer {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            background: #525659;
        }
        .viewer-fallback {
            display: none;
            position: absolute;
            inset: 0;
            z-index: 2;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            text-align: center;
            color: #fff;
            background: #525659;
        }
        .viewer-fallback.is-visible { display: flex; }
        .viewer-fallback p {
            margin: 0 0 14px;
            font-size: 15px;
            line-height: 1.4;
            max-width: 22rem;
        }
        @media (max-width: 640px) {
            .toolbar__title { display: none; }
            .toolbar a { padding: 10px 14px; min-height: 44px; }
        }
    </style>
</head>
<body>
@php
    $bust = (string) time();
    $withQuery = static function (string $url, array $extra) use ($bust): string {
        $parts = parse_url($url);
        $query = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        unset($query['t']);
        $query = array_merge($query, $extra, ['t' => $bust]);
        $base = ($parts['scheme'] ?? null) && ($parts['host'] ?? null)
            ? ($parts['scheme'].'://'.$parts['host'].($parts['port'] ?? null ? ':'.$parts['port'] : '').($parts['path'] ?? ''))
            : ($parts['path'] ?? $url);
        if (! str_starts_with((string) $base, 'http') && ! str_starts_with((string) $base, '/')) {
            $base = $url;
            $qPos = strpos($base, '?');
            if ($qPos !== false) {
                $base = substr($base, 0, $qPos);
            }
        }

        return $base.'?'.http_build_query($query);
    };

    $resolvedPdfUrl = $withQuery((string) ($pdfUrl ?? ''), ['pdf' => 1]);
    $resolvedDownloadUrl = $withQuery(
        (string) ($downloadUrl ?? $pdfUrl ?? ''),
        ['pdf' => 1, 'download' => 1]
    );
@endphp
    <div class="toolbar">
        <a href="{{ $backUrl }}" class="btn-back">← Retour</a>
        <div class="toolbar__title">{{ $title ?? 'Aperçu PDF' }}</div>
        <a href="{{ $resolvedDownloadUrl }}" class="btn-download" download>Télécharger</a>
    </div>
    <div class="viewer-wrap">
        <iframe
            class="viewer"
            id="pdfViewer"
            src="{{ $resolvedPdfUrl }}"
            title="{{ $title ?? 'Aperçu PDF' }}"
        ></iframe>
        <div class="viewer-fallback" id="pdfFallback">
            <div>
                <p>Aperçu indisponible sur cet appareil. Téléchargez le PDF à jour.</p>
                <a href="{{ $resolvedDownloadUrl }}" class="btn-download" download>Télécharger</a>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var ua = navigator.userAgent || '';
            var isIOS = /iPad|iPhone|iPod/.test(ua)
                || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            if (!isIOS) return;
            var fallback = document.getElementById('pdfFallback');
            if (fallback) {
                setTimeout(function () {
                    fallback.classList.add('is-visible');
                }, 900);
            }
        })();
    </script>
</body>
</html>

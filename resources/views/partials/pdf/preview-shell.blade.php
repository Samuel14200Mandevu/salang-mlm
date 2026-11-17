<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
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
            flex-wrap: wrap;
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
        }
        .toolbar__actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .toolbar a, .toolbar button {
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
            border: 0;
            cursor: pointer;
            font-family: inherit;
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
        .viewer-status {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            text-align: center;
            color: #fff;
            background: #525659;
            z-index: 2;
        }
        .viewer-status[hidden] { display: none !important; }
        .viewer-status p {
            margin: 0 0 14px;
            font-size: 15px;
            line-height: 1.4;
            max-width: 22rem;
        }
        .viewer-status a, .viewer-status .btn-download {
            display: inline-flex;
            margin: 0 4px;
        }
        .spinner {
            width: 28px;
            height: 28px;
            border: 3px solid rgba(255,255,255,.25);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 640px) {
            .toolbar__title { display: none; }
            .toolbar a, .toolbar button { padding: 10px 14px; min-height: 44px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ $backUrl }}" class="btn-back">← Retour</a>
        <div class="toolbar__title">{{ $title ?? 'Aperçu PDF' }}</div>
        <div class="toolbar__actions">
            @if(!empty($downloadUrl))
                <a href="{{ $downloadUrl }}" class="btn-download" id="btnDownload">Télécharger</a>
            @endif
        </div>
    </div>
    <div class="viewer-wrap">
        <div class="viewer-status" id="pdfLoading">
            <div>
                <div class="spinner" aria-hidden="true"></div>
                <p>Chargement du rapport…</p>
            </div>
        </div>
        <div class="viewer-status" id="pdfFallback" hidden>
            <div>
                <p>Impossible d’afficher l’aperçu ici. Téléchargez ou ouvrez le PDF.</p>
                <a href="{{ $downloadUrl ?? $pdfUrl }}" class="btn-download">Ouvrir le PDF</a>
            </div>
        </div>
        <iframe class="viewer" id="pdfViewer" title="{{ $title ?? 'Aperçu PDF' }}" hidden></iframe>
        <embed class="viewer" id="pdfEmbed" type="application/pdf" hidden>
    </div>
    <script>
        (function () {
            var pdfUrl = @json($pdfUrl);
            var downloadUrl = @json($downloadUrl ?? $pdfUrl);
            var loading = document.getElementById('pdfLoading');
            var fallback = document.getElementById('pdfFallback');
            var viewer = document.getElementById('pdfViewer');
            var embed = document.getElementById('pdfEmbed');
            var blobUrl = null;

            function showFallback() {
                if (loading) loading.hidden = true;
                if (fallback) fallback.hidden = false;
                if (viewer) viewer.hidden = true;
                if (embed) embed.hidden = true;
            }

            function showViewer(el, url) {
                if (loading) loading.hidden = true;
                if (fallback) fallback.hidden = true;
                el.hidden = false;
                if (el === viewer) {
                    el.src = url;
                } else {
                    el.setAttribute('src', url);
                }
            }

            // Charge en blob pour contourner X-Frame-Options: DENY (Cloudflare / proxy)
            // qui bloque l’affichage du PDF dans une iframe same-origin.
            fetch(pdfUrl, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/pdf' }
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    var type = (res.headers.get('content-type') || '').toLowerCase();
                    if (type && type.indexOf('pdf') === -1 && type.indexOf('octet-stream') === -1) {
                        throw new Error('Not a PDF');
                    }
                    return res.blob();
                })
                .then(function (blob) {
                    if (!blob || blob.size < 100) throw new Error('Empty PDF');
                    var pdfBlob = blob.type && blob.type.indexOf('pdf') !== -1
                        ? blob
                        : new Blob([blob], { type: 'application/pdf' });
                    blobUrl = URL.createObjectURL(pdfBlob);

                    var ua = navigator.userAgent || '';
                    var isIOS = /iPad|iPhone|iPod/.test(ua)
                        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

                    if (isIOS) {
                        // iOS n’affiche souvent pas un PDF blob dans une iframe :
                        // garder la barre Retour et proposer l’ouverture native.
                        if (loading) loading.hidden = true;
                        if (fallback) {
                            var link = fallback.querySelector('a');
                            if (link) link.href = blobUrl;
                            fallback.hidden = false;
                        }
                        return;
                    }

                    showViewer(viewer, blobUrl);
                    // Si l’iframe reste vide, bascule sur <embed>
                    setTimeout(function () {
                        try {
                            if (viewer && viewer.offsetParent !== null && !viewer.contentDocument) {
                                // ok — PDF viewer natif sans contentDocument
                            }
                        } catch (e) { /* cross-opaque ok */ }
                    }, 800);
                })
                .catch(function () {
                    // Dernier recours : tenter l’URL directe (certains navigateurs l’acceptent)
                    try {
                        showViewer(embed, pdfUrl);
                        setTimeout(function () {
                            // Si toujours bloqué, fallback boutons
                            showFallback();
                        }, 1500);
                    } catch (e) {
                        showFallback();
                    }
                });

            window.addEventListener('pagehide', function () {
                if (blobUrl) URL.revokeObjectURL(blobUrl);
            });
        })();
    </script>
</body>
</html>

<?php

namespace App\Http\Controllers\Concerns;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait GeneratesPdfSafely
{
    /**
     * Relève les limites PHP avant une génération DomPDF (prod / proxy Cloud).
     */
    protected function preparePdfExport(int $seconds = 300, string $memory = '512M'): void
    {
        set_time_limit($seconds);
        @ini_set('memory_limit', $memory);
    }

    /**
     * Options DomPDF stables (pas de remote, subset fonts).
     */
    protected function safePdfOptions(): array
    {
        return [
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ];
    }

    /**
     * Logo Salang en base64 (taille plafonnée pour DomPDF).
     */
    protected function loadPdfLogoBase64(int $maxBytes = 100000): string
    {
        $logoPath = public_path('images/salang_logo.png');
        if (! file_exists($logoPath) || filesize($logoPath) >= $maxBytes) {
            return '';
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath));
    }

    /**
     * Génère un PDF depuis une vue et le renvoie en téléchargement fichier temporaire
     * (évite de garder tout le binaire en mémoire dans la réponse HTTP).
     */
    protected function downloadPdfView(
        string $view,
        array $data,
        string $filename,
        string $paper = 'a4',
        string $orientation = 'portrait',
        int $seconds = 300,
        string $memory = '512M',
    ): BinaryFileResponse|Response {
        $this->preparePdfExport($seconds, $memory);

        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions($this->safePdfOptions());

        return $this->downloadPdfBinary($pdf->output(), $filename);
    }

    /**
     * Génère un PDF depuis du HTML brut.
     */
    protected function downloadPdfHtml(
        string $html,
        string $filename,
        string $paper = 'a4',
        string $orientation = 'portrait',
        int $seconds = 120,
        string $memory = '256M',
    ): BinaryFileResponse|Response {
        $this->preparePdfExport($seconds, $memory);

        $pdf = Pdf::loadHTML($html)
            ->setPaper($paper, $orientation)
            ->setOptions($this->safePdfOptions());

        return $this->downloadPdfBinary($pdf->output(), $filename);
    }

    /**
     * Écrit le binaire PDF sur disque temporaire puis download.
     */
    protected function downloadPdfBinary(string $binary, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'salang_pdf_');
        if ($tmp === false) {
            // Fallback mémoire si temp indisponible
            return response($binary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        file_put_contents($tmp, $binary);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Journalise une erreur d’export PDF.
     */
    protected function logPdfFailure(string $context, \Throwable $e, array $extra = []): void
    {
        Log::error("Erreur export PDF [{$context}]", array_merge($extra, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]));
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\BinancePayService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérification signature Binance Pay (openssl_verify + certificat public).
 * Ne pas confondre avec le middleware HMAC générique webhook.verify.
 */
class VerifyBinancePaySignature
{
    public function __construct(
        protected BinancePayService $binancePayService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->binancePayService->verifySignature($request)) {
            Log::warning('Binance Pay webhook: signature invalide', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'returnCode' => 'FAIL',
                'returnMessage' => 'Invalid signature',
            ], 401);
        }

        $request->attributes->set('binance_signature_verified', true);

        return $next($request);
    }
}

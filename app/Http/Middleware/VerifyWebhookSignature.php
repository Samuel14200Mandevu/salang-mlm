<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, $provider = null)
    {
        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();
        
        // Vérifier selon le provider
        switch ($provider) {
            case 'flexpay':
                $secret = config('services.flexpay.webhook_secret');
                break;
            case 'coinbase':
                $secret = config('services.coinbase.webhook_secret');
                break;
            default:
                $secret = config('app.key');
        }
        
        if (!$signature || !$secret) {
            Log::warning('Webhook signature missing', [
                'provider' => $provider,
                'headers' => $request->headers->all()
            ]);
            return response()->json(['error' => 'Signature manquante'], 401);
        }
        
        $computed = hash_hmac('sha256', $payload, $secret);
        
        if (!hash_equals($computed, $signature)) {
            Log::warning('Webhook signature invalid', [
                'provider' => $provider,
                'received' => $signature,
                'computed' => $computed
            ]);
            return response()->json(['error' => 'Signature invalide'], 401);
        }
        
        return $next($request);
    }
}
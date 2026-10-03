<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, string $provider = 'flexpay')
    {
        $secret = config("services.webhooks.{$provider}.secret");

        if (empty($secret)) {
            if (app()->environment('production')) {
                Log::error('Webhook secret missing in production', ['provider' => $provider]);

                return response()->json(['success' => false, 'message' => 'Webhook not configured'], 503);
            }

            return $next($request);
        }

        $signature = $request->header('X-Webhook-Signature')
            ?? $request->header('X-FlexPay-Signature')
            ?? $request->header('Stripe-Signature');

        if (!$signature) {
            return response()->json(['success' => false, 'message' => 'Missing signature'], 401);
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($expected, $signature) && !hash_equals($expected, trim($signature))) {
            Log::warning('Invalid webhook signature', [
                'provider' => $provider,
                'ip' => $request->ip(),
            ]);

            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}

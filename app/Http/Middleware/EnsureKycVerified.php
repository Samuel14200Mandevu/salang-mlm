<?php
// app/Http/Middleware/EnsureKycVerified.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureKycVerified
{
    protected array $kycRequiredRoutes = [
        'withdrawal',
        'withdrawal.*',
        'wallet.withdraw',
        'commission.*',
        'team.*',
        'payment.*',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'code' => 'UNAUTHENTICATED'
                ], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if (!$this->isKycRequired($request)) {
            return $next($request);
        }

        if ($user->kyc_status !== 'verified') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC verification required to access this feature.',
                    'code' => 'KYC_REQUIRED',
                    'status' => $user->kyc_status,
                ], 403);
            }

            return redirect()->route('kyc.index')
                ->with('warning', 'Please complete your KYC verification to access this feature.');
        }

        return $next($request);
    }

    private function isKycRequired(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return false;
        }

        foreach ($this->kycRequiredRoutes as $pattern) {
            if (str_contains($routeName, rtrim($pattern, '.*')) || $routeName === $pattern) {
                return true;
            }
        }

        return false;
    }
}
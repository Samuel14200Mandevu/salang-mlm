<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        $request->validate([
            'method' => 'required|in:crypto,mobile_money',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();

        if ($request->method === 'crypto') {
            Log::info('Crypto payment initiated', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'order_id' => $request->order_id,
            ]);

            return redirect()->route('payment.success')
                ->with('success', 'Crypto payment initiated. Please complete the payment.');
        }

        if ($request->method === 'mobile_money') {
            $request->validate([
                'phone' => 'required|string',
                'provider' => 'required|in:Airtel Money,Orange Money,M-Pesa',
            ]);

            Log::info('Mobile money payment initiated', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'phone' => $request->phone,
                'provider' => $request->provider,
            ]);

            return redirect()->route('payment.success')
                ->with('success', 'Mobile money payment initiated. You will receive a confirmation SMS.');
        }

        return back()->with('error', 'Payment method not supported.');
    }

    public function success()
    {
        return view('payment.success');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}
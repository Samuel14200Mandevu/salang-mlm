<?php

namespace App\Http\Controllers;

use App\Services\CryptoPaymentService;
use App\Services\MobileMoneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $cryptoService;
    protected $mobileMoneyService;

    public function __construct(
        CryptoPaymentService $cryptoService,
        MobileMoneyService $mobileMoneyService
    ) {
        $this->cryptoService = $cryptoService;
        $this->mobileMoneyService = $mobileMoneyService;
    }

    public function process(Request $request)
    {
        $request->validate([
            'method' => 'required|in:crypto,mobile_money',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();

        if ($request->method === 'crypto') {
            $result = $this->cryptoService->createPayment(
                $request->amount,
                'USD',
                'USDC',
                $user->id,
                $request->order_id
            );

            if ($result['success']) {
                return redirect($result['payment_url']);
            }

            return back()->with('error', $result['error']);
        }

        if ($request->method === 'mobile_money') {
            $request->validate([
                'phone' => 'required|string',
                'provider' => 'required|in:Airtel Money,Orange Money,M-Pesa',
            ]);

            $result = $this->mobileMoneyService->initiatePayment(
                $request->amount,
                $request->phone,
                $request->provider,
                $user->id,
                $request->order_id
            );

            if ($result['success']) {
                return redirect()->route('payment.success')
                    ->with('success', 'Paiement initié avec succès. Vous recevrez une confirmation par SMS.');
            }

            return back()->with('error', $result['error']);
        }

        return back()->with('error', 'Méthode de paiement non supportée');
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
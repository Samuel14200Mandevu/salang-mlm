<?php

namespace App\Http\Controllers;

use App\Services\FlexPayService;
use App\Services\PaymentService;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected FlexPayService $flexPayService;

    public function __construct(
        PaymentService $paymentService,
        FlexPayService $flexPayService
    ) {
        $this->paymentService = $paymentService;
        $this->flexPayService = $flexPayService;
    }

    /**
     * Afficher la page de paiement
     */
    public function index($orderId)
    {
        $order = Order::with('user')->findOrFail($orderId);
        return view('payment.index', compact('order'));
    }

    /**
     * Initier un paiement mobile money
     */
    public function initierPaiement(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'phone' => 'required|string|regex:/^[0-9]{9}$/',
            'provider' => 'required|in:orange,airtel,mpesa'
        ]);

        try {
            $order = Order::with('user')->findOrFail($request->order_id);
            $user = Auth::user();

            // Vérifier que le montant est valide
            if ($order->total <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant de la commande est invalide'
                ], 422);
            }

            // Appeler le service de paiement
            $result = $this->paymentService->processMobileMoneyPayment(
                $user->id,
                $order->total,
                $request->provider,
                $request->phone
            );

            if ($result['success']) {
                // Mettre à jour la commande avec la référence
                $order->payment_reference = $result['reference'] ?? null;
                $order->payment_method = 'mobile_money';
                $order->payment_provider = $request->provider;
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Demande de paiement envoyée. Veuillez confirmer sur votre téléphone.',
                    'data' => [
                        'reference' => $result['reference'] ?? null,
                        'transaction_id' => $result['transaction_id'] ?? null,
                        'status' => 'pending'
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Erreur lors du paiement'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment initiation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur technique: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function verifierStatut($reference)
    {
        try {
            $result = $this->flexPayService->verifierStatut($reference);

            return response()->json([
                'success' => true,
                'status' => $result->isSuccessful() ? 'completed' : 'pending',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Status verification error', [
                'reference' => $reference,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ], 500);
        }
    }

    /**
     * Traiter un paiement (méthode legacy)
     */
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

            // Utiliser le service FlexPay
            $result = $this->paymentService->processMobileMoneyPayment(
                $user->id,
                $request->amount,
                strtolower(str_replace(' Money', '', $request->provider)),
                $request->phone
            );

            if ($result['success']) {
                return redirect()->route('payment.success')
                    ->with('success', 'Mobile money payment initiated. You will receive a confirmation SMS.');
            }

            return back()->with('error', $result['error'] ?? 'Erreur lors du paiement');
        }

        return back()->with('error', 'Payment method not supported.');
    }

    /**
     * Page de succès
     */
    public function success(Request $request)
    {
        // Récupérer le message de succès
        $message = $request->session()->get('success', 'Votre paiement a été effectué avec succès.');
        
        return view('payment.success', compact('message'));
    }

    /**
     * Page d'annulation
     */
    public function cancel(Request $request)
    {
        $message = $request->session()->get('error', 'Le paiement a été annulé.');
        
        return view('payment.cancel', compact('message'));
    }

    /**
     * Confirmer un paiement
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
            'provider' => 'required|string'
        ]);

        $result = $this->paymentService->confirmMobilePayment(
            $request->reference,
            $request->provider
        );

        if ($result['success']) {
            return redirect()->route('payment.success')
                ->with('success', 'Paiement confirmé avec succès');
        }

        return redirect()->route('payment.cancel')
            ->with('error', 'Erreur lors de la confirmation du paiement');
    }
}
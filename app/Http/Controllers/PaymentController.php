<?php

namespace App\Http\Controllers;

use App\Services\FlexPayService;
use App\Services\PaymentService;
use App\Models\Order;
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
     * Initier un paiement mobile money (commande)
     */
    public function initierPaiement(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'phone' => 'required|string|min:9|max:15',
            'provider' => 'required|in:orange,airtel,mpesa',
        ]);

        try {
            $order = Order::with('user')->findOrFail($request->order_id);
            $user = Auth::user();

            if ((int) $order->user_id !== (int) $user->id && ! $user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non autorisée',
                ], 403);
            }

            if ($order->total <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant de la commande est invalide',
                ], 422);
            }

            $result = $this->paymentService->processMobileMoneyPayment(
                $user->id,
                $order->total,
                $request->provider,
                $request->phone,
                $order->id
            );

            if ($result['success']) {
                $meta = is_array($order->metadata) ? $order->metadata : [];
                $meta['flexpay_reference'] = $result['reference'] ?? null;
                $meta['flexpay_order_number'] = $result['order_number'] ?? null;
                $meta['payment_provider'] = $request->provider;
                $order->payment_method = 'mobile_money';
                $order->payment_status = 'pending';
                $order->metadata = $meta;
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Demande de paiement envoyée. Veuillez confirmer sur votre téléphone.',
                    'data' => [
                        'reference' => $result['reference'] ?? null,
                        'order_number' => $result['order_number'] ?? null,
                        'transaction_id' => $result['order_number'] ?? null,
                        'status' => 'pending',
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? $result['message'] ?? 'Erreur lors du paiement',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Payment initiation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur technique: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'un paiement FlexPay (orderNumber)
     */
    public function verifierStatut($reference)
    {
        try {
            $result = $this->flexPayService->verifierStatut($reference);

            return response()->json([
                'success' => (bool) ($result['success'] ?? false),
                'status' => $result['status'] ?? 'unknown',
                'paid' => (bool) ($result['paid'] ?? false),
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Status verification error', [
                'reference' => $reference,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification',
            ], 500);
        }
    }

    /**
     * Traiter un paiement (méthode legacy form)
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
                'phone' => 'required|string|min:9|max:15',
                'provider' => 'required|in:Airtel Money,Orange Money,M-Pesa,orange,airtel,mpesa',
            ]);

            $provider = strtolower(str_replace(' Money', '', $request->provider));

            $result = $this->paymentService->processMobileMoneyPayment(
                $user->id,
                $request->amount,
                $provider,
                $request->phone,
                $request->order_id
            );

            if ($result['success']) {
                return redirect()->route('payment.success')
                    ->with('success', 'Mobile money payment initiated. Confirm on your phone. Réf: '.($result['reference'] ?? ''));
            }

            return back()->with('error', $result['error'] ?? $result['message'] ?? 'Erreur lors du paiement');
        }

        return back()->with('error', 'Payment method not supported.');
    }

    public function success(Request $request)
    {
        $message = $request->session()->get('success', 'Votre paiement a été effectué avec succès.');

        return view('payment.success', compact('message'));
    }

    public function cancel(Request $request)
    {
        $message = $request->session()->get('error', 'Le paiement a été annulé.');

        return view('payment.cancel', compact('message'));
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
            'provider' => 'required|string',
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

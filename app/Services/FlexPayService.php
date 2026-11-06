<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Devscast\Flexpay\Client as FlexpayClient;
use Devscast\Flexpay\Credential;
use Devscast\Flexpay\Data\Currency;
use Devscast\Flexpay\Environment;
use Devscast\Flexpay\Exception\NetworkException;
use Devscast\Flexpay\Request\MobileRequest;
use Devscast\Flexpay\Response\CheckResponse;
use Devscast\Flexpay\Response\PaymentResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FlexPayService
{
    protected ?FlexpayClient $client = null;

    /**
     * Initier un paiement Mobile Money via FlexPay.
     *
     * @return array{success: bool, reference?: string, order_number?: string|null, message?: string, error?: string, raw?: array}
     */
    public function initierPaiement(
        float $amount,
        string $phone,
        string $reference,
        ?string $description = null,
        ?string $callbackUrl = null,
        string $currency = 'USD',
    ): array {
        $phone = $this->normalizePhone($phone);
        $callbackUrl = $callbackUrl ?: route('webhook.flexpay');
        $description = $description ?: 'Paiement Salang '.$reference;

        try {
            $currencyEnum = Currency::from(strtoupper($currency));

            $request = new MobileRequest(
                amount: $amount,
                reference: $reference,
                currency: $currencyEnum,
                callbackUrl: $callbackUrl,
                phone: $phone,
                description: $description,
            );

            $response = $this->client()->pay($request);

            if (! $response->isSuccessful()) {
                Log::warning('FlexPayService: initiation refusée', [
                    'reference' => $reference,
                    'message' => $response->message,
                ]);

                return [
                    'success' => false,
                    'error' => $response->message ?: 'FlexPay a refusé la demande de paiement',
                    'reference' => $reference,
                    'raw' => $this->paymentResponseToArray($response),
                ];
            }

            Log::info('FlexPayService: paiement initié', [
                'reference' => $reference,
                'order_number' => $response->orderNumber,
            ]);

            return [
                'success' => true,
                'reference' => $response->reference ?? $reference,
                'order_number' => $response->orderNumber,
                'message' => $response->message ?: 'Demande de paiement envoyée. Confirmez sur votre téléphone.',
                'raw' => $this->paymentResponseToArray($response),
            ];
        } catch (NetworkException $e) {
            Log::error('FlexPayService: erreur réseau initiation', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de joindre FlexPay: '.$e->getMessage(),
                'reference' => $reference,
            ];
        } catch (\Throwable $e) {
            Log::error('FlexPayService: erreur initiation', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'reference' => $reference,
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement FlexPay via orderNumber.
     *
     * @return array{success: bool, status: string, order_number?: string, reference?: string|null, message?: string, paid?: bool, raw?: array}
     */
    public function verifierStatut(string $orderNumber): array
    {
        try {
            /** @var CheckResponse $response */
            $response = $this->client()->check($orderNumber);
            $paid = $response->isSuccessful()
                && $response->transaction !== null
                && $response->transaction->isSuccessful();

            return [
                'success' => true,
                'status' => $paid ? 'completed' : ($response->isSuccessful() ? 'pending' : 'failed'),
                'paid' => $paid,
                'order_number' => $response->transaction?->orderNumber ?? $orderNumber,
                'reference' => $response->transaction?->reference,
                'message' => $response->message,
                'raw' => [
                    'code' => $response->code->value,
                    'message' => $response->message,
                    'transaction' => $response->transaction ? [
                        'reference' => $response->transaction->reference,
                        'orderNumber' => $response->transaction->orderNumber,
                        'amount' => $response->transaction->amount,
                        'status' => $response->transaction->status->value,
                        'channel' => $response->transaction->channel,
                    ] : null,
                ],
            ];
        } catch (NetworkException $e) {
            Log::error('FlexPayService: erreur réseau check', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('FlexPayService: erreur check', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Traiter le callback FlexPay : match pending + crédit wallet une seule fois.
     *
     * @return array{success: bool, already_processed?: bool, status?: string, message: string, transaction_id?: int, http_status: int}
     */
    public function processCallback(array $payload): array
    {
        try {
            $payment = $this->client()->handleCallback($payload);
        } catch (\Throwable $e) {
            Log::error('FlexPayService: callback invalide', [
                'error' => $e->getMessage(),
                'payload_keys' => array_keys($payload),
            ]);
            app(PaymentNotificationService::class)->recordWebhookFailure(
                'flexpay',
                'Payload invalide: '.$e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Payload FlexPay invalide',
                'http_status' => 400,
            ];
        }

        $reference = $payment->reference
            ?? ($payload['reference'] ?? null);
        $orderNumber = $payment->orderNumber
            ?? ($payload['orderNumber'] ?? $payload['order_number'] ?? null);

        if (! $reference && ! $orderNumber) {
            app(PaymentNotificationService::class)->recordWebhookFailure(
                'flexpay',
                'Référence ou orderNumber manquant'
            );

            return [
                'success' => false,
                'message' => 'Référence ou orderNumber manquant',
                'http_status' => 400,
            ];
        }

        // handleCallback parse la réponse ; en cas de succès déclaré, on confirme
        // éventuellement auprès de l'API FlexPay (check) avant de créditer.
        $isPaid = $payment->isSuccessful();
        if ($isPaid && $orderNumber) {
            $check = $this->verifierStatut((string) $orderNumber);
            if (($check['success'] ?? false) === true) {
                $isPaid = (bool) ($check['paid'] ?? false);
            }
            // Si le check API échoue (réseau), on s'appuie sur handleCallback ($isPaid reste true).
        }

        $result = DB::transaction(function () use ($payment, $payload, $reference, $orderNumber, $isPaid) {
            $query = Transaction::query()->where(function ($q) use ($reference, $orderNumber) {
                if ($reference) {
                    $q->where('reference', $reference);
                }
                if ($orderNumber) {
                    $method = $reference ? 'orWhere' : 'where';
                    $q->{$method}('transaction_id', $orderNumber);
                }
            });

            /** @var Transaction|null $transaction */
            $transaction = $query->lockForUpdate()->first();

            if (! $transaction) {
                Log::warning('FlexPayService: callback sans transaction pending', [
                    'reference' => $reference,
                    'order_number' => $orderNumber,
                ]);

                return [
                    'success' => false,
                    'message' => 'Transaction introuvable',
                    'http_status' => 404,
                    'notify' => null,
                ];
            }

            if ($transaction->status === 'completed') {
                return [
                    'success' => true,
                    'already_processed' => true,
                    'status' => 'completed',
                    'message' => 'Transaction déjà traitée',
                    'transaction_id' => $transaction->id,
                    'http_status' => 200,
                    'notify' => null,
                ];
            }

            if ($transaction->status === 'failed' || $transaction->status === 'cancelled') {
                return [
                    'success' => true,
                    'already_processed' => true,
                    'status' => $transaction->status,
                    'message' => 'Transaction déjà clôturée',
                    'transaction_id' => $transaction->id,
                    'http_status' => 200,
                    'notify' => null,
                ];
            }

            $meta = is_array($transaction->metadata) ? $transaction->metadata : [];
            $meta['flexpay_callback'] = $this->paymentResponseToArray($payment);
            $meta['flexpay_raw'] = $payload;

            if ($orderNumber && ! $transaction->transaction_id) {
                $transaction->transaction_id = $orderNumber;
            }

            if (! $isPaid) {
                $transaction->status = 'failed';
                $transaction->metadata = $meta;
                $transaction->save();

                Log::info('FlexPayService: paiement échoué', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'message' => $payment->message,
                ]);

                return [
                    'success' => true,
                    'already_processed' => false,
                    'status' => 'failed',
                    'message' => $payment->message ?: 'Paiement échoué',
                    'transaction_id' => $transaction->id,
                    'http_status' => 200,
                    'notify' => 'failed',
                ];
            }

            /** @var Wallet $wallet */
            $wallet = Wallet::query()
                ->whereKey($transaction->wallet_id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                return [
                    'success' => false,
                    'message' => 'Portefeuille introuvable',
                    'http_status' => 404,
                    'notify' => null,
                ];
            }

            $amount = (float) $transaction->amount;
            $balanceBefore = (float) $wallet->balance;

            $wallet->balance = $balanceBefore + $amount;
            $wallet->total_deposited = (float) $wallet->total_deposited + $amount;
            $wallet->save();

            $transaction->status = 'completed';
            $transaction->balance_before = $balanceBefore;
            $transaction->balance_after = $wallet->balance;
            $transaction->completed_at = now();
            $transaction->metadata = $meta;
            $transaction->provider = $transaction->provider ?: 'flexpay';
            $transaction->save();

            $orderId = $meta['order_id'] ?? $transaction->order_id;
            if ($orderId) {
                $this->markOrderPaid((int) $orderId, $transaction);
            }

            Log::info('FlexPayService: wallet crédité', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $amount,
                'reference' => $transaction->reference,
                'order_number' => $transaction->transaction_id,
            ]);

            return [
                'success' => true,
                'already_processed' => false,
                'status' => 'completed',
                'message' => 'Paiement confirmé et wallet crédité',
                'transaction_id' => $transaction->id,
                'http_status' => 200,
                'notify' => 'completed',
            ];
        });

        $notifier = app(PaymentNotificationService::class);
        $notifier->dispatchFromCallbackResult($result, 'FlexPay');

        if (($result['success'] ?? false) === false) {
            $notifier->recordWebhookFailure(
                'flexpay',
                (string) ($result['message'] ?? 'Échec callback FlexPay')
            );
        }

        return $result;
    }

    /**
     * Créer une transaction pending puis appeler FlexPay.
     *
     * @param  array{order_id?: int|null, provider?: string|null, phone?: string, description?: string|null}  $context
     * @return array{success: bool, reference?: string, order_number?: string|null, transaction?: Transaction, message?: string, error?: string}
     */
    public function initiateDeposit(
        User $user,
        float $amount,
        string $phone,
        array $context = [],
    ): array {
        $phone = $this->normalizePhone($phone);
        $reference = 'FP-'.strtoupper(Str::random(12));
        $provider = $context['provider'] ?? 'flexpay';
        $orderId = $context['order_id'] ?? null;
        $description = $context['description'] ?? 'Dépôt Mobile Money Salang';

        $transaction = DB::transaction(function () use ($user, $amount, $phone, $reference, $provider, $orderId, $description) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => 0,
                    'pending_balance' => 0,
                    'total_withdrawn' => 0,
                    'total_deposited' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]
            );

            $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'order_id' => $orderId,
                'type' => 'deposit',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance,
                'status' => 'pending',
                'reference' => $reference,
                'transaction_id' => null,
                'provider' => $provider,
                'description' => $description,
                'metadata' => [
                    'provider' => $provider,
                    'phone' => $phone,
                    'order_id' => $orderId,
                    'user_id' => $user->id,
                ],
            ]);
        });

        // Appel réseau hors lock DB
        $result = $this->initierPaiement(
            amount: $amount,
            phone: $phone,
            reference: $reference,
            description: $description,
        );

        if (! ($result['success'] ?? false)) {
            $transaction->status = 'failed';
            $transaction->metadata = array_merge($transaction->metadata ?? [], [
                'flexpay_init_error' => $result['error'] ?? 'unknown',
                'flexpay_raw' => $result['raw'] ?? null,
            ]);
            $transaction->save();

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Échec initiation FlexPay',
                'reference' => $reference,
                'transaction' => $transaction,
            ];
        }

        $transaction->transaction_id = $result['order_number'] ?? null;
        $transaction->metadata = array_merge($transaction->metadata ?? [], [
            'flexpay_init' => $result['raw'] ?? null,
        ]);
        $transaction->save();

        return [
            'success' => true,
            'reference' => $reference,
            'order_number' => $result['order_number'] ?? null,
            'message' => $result['message'] ?? 'Paiement initié',
            'transaction' => $transaction,
        ];
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            $phone = '243'.substr($phone, 1);
        }

        if (! str_starts_with($phone, '243') && strlen($phone) === 9) {
            $phone = '243'.$phone;
        }

        if (strlen($phone) !== 12) {
            throw new \InvalidArgumentException(
                'Le numéro doit être au format 243XXXXXXXXX (12 chiffres). Reçu: '.$phone
            );
        }

        return $phone;
    }

    protected function client(): FlexpayClient
    {
        if ($this->client instanceof FlexpayClient) {
            return $this->client;
        }

        $token = (string) config('services.flexpay.token');
        $merchant = (string) config('services.flexpay.merchant_code');

        if ($token === '' || $merchant === '') {
            throw new \RuntimeException(
                'FlexPay non configuré: définissez FLEXPAY_TOKEN et FLEXPAY_MERCHANT_CODE.'
            );
        }

        $this->client = new FlexpayClient(
            new Credential($token, $merchant),
            $this->resolveEnvironment()
        );

        return $this->client;
    }

    protected function resolveEnvironment(): Environment
    {
        $env = strtolower((string) config('services.flexpay.environment', 'sandbox'));

        return match ($env) {
            'live', 'prod', 'production' => Environment::LIVE,
            default => Environment::SANDBOX,
        };
    }

    protected function paymentResponseToArray(PaymentResponse $response): array
    {
        return [
            'code' => $response->code->value,
            'message' => $response->message,
            'reference' => $response->reference,
            'provider_reference' => $response->providerReference,
            'orderNumber' => $response->orderNumber,
            'url' => $response->url,
            'successful' => $response->isSuccessful(),
        ];
    }

    protected function markOrderPaid(int $orderId, Transaction $transaction): void
    {
        $order = Order::query()->whereKey($orderId)->lockForUpdate()->first();
        if (! $order || in_array($order->payment_status, ['completed', 'paid'], true)) {
            return;
        }

        $order->status = 'completed';
        $order->payment_status = 'completed';
        $order->payment_method = 'mobile_money';
        $order->paid_at = now();
        $orderMeta = is_array($order->metadata) ? $order->metadata : [];
        $orderMeta['flexpay_reference'] = $transaction->reference;
        $orderMeta['flexpay_order_number'] = $transaction->transaction_id;
        $order->metadata = $orderMeta;
        $order->save();
    }
}

<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use CryptoPay\Binancepay\BinancePay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BinancePayService
{
    public const PROVIDER = 'binance';

    /**
     * Créer une commande Binance Pay + transaction pending.
     *
     * @param  array{goods_name?: string, goods_detail?: string|null, order_id?: int|null, currency_label?: string, note?: string|null}  $context
     * @return array{success: bool, checkout_url?: string, reference?: string, prepay_id?: string|null, transaction?: Transaction, message?: string, error?: string}
     */
    public function createOrder(User $user, float $amount, array $context = []): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Montant invalide', 'message' => 'Montant invalide'];
        }

        $this->assertConfigured();

        $merchantTradeNo = $this->generateMerchantTradeNo();
        $goodsName = $context['goods_name'] ?? 'Depot portefeuille Salang';
        $goodsDetail = $context['goods_detail'] ?? 'Depot USDT via Binance Pay';
        $orderId = $context['order_id'] ?? null;

        $transaction = DB::transaction(function () use ($user, $amount, $merchantTradeNo, $orderId, $goodsName, $context) {
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
                'reference' => $merchantTradeNo,
                'transaction_id' => null,
                'provider' => self::PROVIDER,
                'description' => 'Dépôt Binance Pay (USDT)',
                'metadata' => [
                    'provider' => self::PROVIDER,
                    'merchant_trade_no' => $merchantTradeNo,
                    'order_id' => $orderId,
                    'user_id' => $user->id,
                    'currency' => config('binancepay.binance_currency', 'USDT'),
                    'currency_label' => $context['currency_label'] ?? 'USDT',
                    'note' => $context['note'] ?? null,
                    'goods_name' => $goodsName,
                ],
            ]);
        });

        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
        $firstName = $nameParts[0] ?? 'Member';
        $lastName = $nameParts[1] ?? 'Salang';

        $payload = [
            'order_amount' => round($amount, 2),
            'package_id' => (string) ($orderId ?: 'wallet-deposit'),
            'goods_name' => mb_substr($goodsName, 0, 120),
            'goods_detail' => $goodsDetail,
            'buyer' => [
                'referenceBuyerId' => (string) $user->id,
                'buyerEmail' => (string) ($user->email ?: 'noreply@salang.local'),
                'buyerName' => [
                    'firstName' => mb_substr($firstName, 0, 50),
                    'lastName' => mb_substr($lastName, 0, 50),
                ],
            ],
            'trx_id' => (string) $transaction->id,
            'merchant_trade_no' => $merchantTradeNo,
        ];

        try {
            $binancePay = new BinancePay('binancepay/openapi/v2/order');
            $response = $binancePay->createOrder($payload);
        } catch (\Throwable $e) {
            $transaction->status = 'failed';
            $transaction->metadata = array_merge($transaction->metadata ?? [], [
                'binance_init_error' => $e->getMessage(),
            ]);
            $transaction->save();

            Log::error('BinancePayService: exception createOrder', [
                'reference' => $merchantTradeNo,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de joindre Binance Pay: '.$e->getMessage(),
                'reference' => $merchantTradeNo,
                'transaction' => $transaction,
            ];
        }

        if (($response['status'] ?? null) !== 'SUCCESS') {
            $error = $response['errorMessage'] ?? json_encode($response);

            $transaction->status = 'failed';
            $transaction->metadata = array_merge($transaction->metadata ?? [], [
                'binance_init_error' => $error,
                'binance_raw' => $response,
            ]);
            $transaction->save();

            Log::warning('BinancePayService: createOrder refusé', [
                'reference' => $merchantTradeNo,
                'response' => $response,
            ]);

            return [
                'success' => false,
                'error' => is_string($error) ? $error : 'Binance Pay a refusé la commande',
                'reference' => $merchantTradeNo,
                'transaction' => $transaction,
            ];
        }

        $data = $response['data'] ?? [];
        $checkoutUrl = $data['checkoutUrl'] ?? $data['universalUrl'] ?? null;
        $prepayId = isset($data['prepayId']) ? (string) $data['prepayId'] : null;

        $transaction->transaction_id = $prepayId;
        $transaction->metadata = array_merge($transaction->metadata ?? [], [
            'binance_prepay_id' => $prepayId,
            'checkout_url' => $checkoutUrl,
            'qrcode_link' => $data['qrcodeLink'] ?? null,
            'deeplink' => $data['deeplink'] ?? null,
            'binance_init' => $data,
        ]);
        $transaction->save();

        if (! $checkoutUrl) {
            return [
                'success' => false,
                'error' => 'Binance Pay n\'a pas renvoyé d\'URL de paiement',
                'reference' => $merchantTradeNo,
                'transaction' => $transaction,
            ];
        }

        Log::info('BinancePayService: order créée', [
            'reference' => $merchantTradeNo,
            'prepay_id' => $prepayId,
            'user_id' => $user->id,
            'amount' => $amount,
        ]);

        return [
            'success' => true,
            'checkout_url' => $checkoutUrl,
            'reference' => $merchantTradeNo,
            'prepay_id' => $prepayId,
            'message' => 'Redirection vers Binance Pay',
            'transaction' => $transaction,
        ];
    }

    /**
     * Vérifier la signature Binance (openssl_verify + certificat public).
     */
    public function verifySignature(Request $request): bool
    {
        $timestamp = (string) $request->header('Binancepay-Timestamp', '');
        $nonce = (string) $request->header('Binancepay-Nonce', '');
        $signature = (string) $request->header('Binancepay-Signature', '');

        if ($timestamp === '' || $nonce === '' || $signature === '') {
            Log::warning('BinancePayService: headers signature manquants');

            return false;
        }

        try {
            $binancePay = new BinancePay('binancepay/openapi/certificates');
            $certResponse = $binancePay->query($request->all());
        } catch (\Throwable $e) {
            Log::error('BinancePayService: échec fetch certificat', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (($certResponse['status'] ?? null) !== 'SUCCESS') {
            Log::warning('BinancePayService: certificat non SUCCESS', [
                'response' => $certResponse,
            ]);

            return false;
        }

        $publicKey = $certResponse['data'][0]['certPublic'] ?? null;
        if (! is_string($publicKey) || $publicKey === '') {
            return false;
        }

        // Corps brut préféré (recommandé Binance) ; fallback JSON stable
        $entityBody = $request->getContent();
        if ($entityBody === '') {
            $entityBody = json_encode($request->all(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        $payload = $timestamp."\n".$nonce."\n".$entityBody."\n";
        $decodedSignature = base64_decode($signature, true);
        if ($decodedSignature === false) {
            return false;
        }

        $pem = $this->normalizePublicKey($publicKey);
        $ok = openssl_verify($payload, $decodedSignature, $pem, OPENSSL_ALGO_SHA256);

        if ($ok !== 1) {
            Log::warning('BinancePayService: openssl_verify échoué', [
                'openssl_error' => openssl_error_string(),
            ]);
        }

        return $ok === 1;
    }

    /**
     * Traiter le callback webhook Binance (idempotent).
     * La signature peut déjà avoir été validée par VerifyBinancePaySignature.
     *
     * @return array{returnCode: string, returnMessage: string|null, http_status: int, already_processed?: bool, status?: string}
     */
    public function processCallback(Request $request): array
    {
        $notifier = app(PaymentNotificationService::class);

        $alreadyVerified = (bool) $request->attributes->get('binance_signature_verified', false);
        if (! $alreadyVerified && ! $this->verifySignature($request)) {
            $notifier->recordWebhookFailure('binance', 'Invalid signature');

            return [
                'returnCode' => 'FAIL',
                'returnMessage' => 'Invalid signature',
                'http_status' => 401,
            ];
        }

        $body = $request->all();
        $bizStatus = (string) ($body['bizStatus'] ?? '');

        $dataRaw = $body['data'] ?? null;
        $data = is_string($dataRaw)
            ? (json_decode($dataRaw, true) ?: [])
            : (is_array($dataRaw) ? $dataRaw : []);

        $merchantTradeNo = $data['merchantTradeNo'] ?? null;
        if (! $merchantTradeNo) {
            $notifier->recordWebhookFailure('binance', 'merchantTradeNo missing');

            return [
                'returnCode' => 'FAIL',
                'returnMessage' => 'merchantTradeNo missing',
                'http_status' => 400,
            ];
        }

        try {
            $result = DB::transaction(function () use ($merchantTradeNo, $bizStatus, $data, $body) {
                /** @var Transaction|null $transaction */
                $transaction = Transaction::query()
                    ->where('reference', $merchantTradeNo)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction) {
                    Log::warning('BinancePayService: transaction introuvable', [
                        'merchant_trade_no' => $merchantTradeNo,
                    ]);

                    return [
                        'returnCode' => 'FAIL',
                        'returnMessage' => 'Order not found',
                        'http_status' => 404,
                        'notify' => null,
                    ];
                }

                if ($transaction->status === 'completed') {
                    return [
                        'returnCode' => 'SUCCESS',
                        'returnMessage' => null,
                        'http_status' => 200,
                        'already_processed' => true,
                        'status' => 'completed',
                        'transaction_id' => $transaction->id,
                        'notify' => null,
                    ];
                }

                if ($transaction->status === 'failed' || $transaction->status === 'cancelled') {
                    return [
                        'returnCode' => 'SUCCESS',
                        'returnMessage' => null,
                        'http_status' => 200,
                        'already_processed' => true,
                        'status' => $transaction->status,
                        'transaction_id' => $transaction->id,
                        'notify' => null,
                    ];
                }

                $meta = is_array($transaction->metadata) ? $transaction->metadata : [];
                $meta['binance_callback'] = [
                    'bizStatus' => $bizStatus,
                    'data' => $data,
                    'bizId' => $body['bizId'] ?? null,
                    'bizType' => $body['bizType'] ?? null,
                ];

                if (! empty($data['prepayId'])) {
                    $transaction->transaction_id = (string) $data['prepayId'];
                } elseif (! empty($data['transactionId'])) {
                    $transaction->transaction_id = (string) $data['transactionId'];
                }

                if ($bizStatus === 'PAY_CLOSED') {
                    $transaction->status = 'failed';
                    $transaction->metadata = $meta;
                    $transaction->save();

                    return [
                        'returnCode' => 'SUCCESS',
                        'returnMessage' => null,
                        'http_status' => 200,
                        'already_processed' => false,
                        'status' => 'failed',
                        'transaction_id' => $transaction->id,
                        'message' => 'Paiement Binance annulé (PAY_CLOSED)',
                        'notify' => 'failed',
                    ];
                }

                if ($bizStatus !== 'PAY_SUCCESS') {
                    $transaction->metadata = $meta;
                    $transaction->save();

                    return [
                        'returnCode' => 'SUCCESS',
                        'returnMessage' => null,
                        'http_status' => 200,
                        'status' => $bizStatus,
                        'transaction_id' => $transaction->id,
                        'notify' => null,
                    ];
                }

                /** @var Wallet $wallet */
                $wallet = Wallet::query()
                    ->whereKey($transaction->wallet_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $amount = (float) $transaction->amount;
                $balanceBefore = (float) $wallet->balance;

                $wallet->balance = $balanceBefore + $amount;
                $wallet->total_deposited = (float) $wallet->total_deposited + $amount;
                $wallet->save();

                $transaction->status = 'completed';
                $transaction->balance_before = $balanceBefore;
                $transaction->balance_after = $wallet->balance;
                $transaction->completed_at = now();
                $transaction->provider = self::PROVIDER;
                $transaction->metadata = $meta;
                $transaction->save();

                Log::info('BinancePayService: wallet crédité', [
                    'transaction_id' => $transaction->id,
                    'merchant_trade_no' => $merchantTradeNo,
                    'amount' => $amount,
                    'user_id' => $transaction->user_id,
                ]);

                return [
                    'returnCode' => 'SUCCESS',
                    'returnMessage' => null,
                    'http_status' => 200,
                    'already_processed' => false,
                    'status' => 'completed',
                    'transaction_id' => $transaction->id,
                    'notify' => 'completed',
                ];
            });

            $notifier->dispatchFromCallbackResult($result, 'Binance Pay');

            if (($result['returnCode'] ?? 'FAIL') !== 'SUCCESS') {
                $notifier->recordWebhookFailure(
                    'binance',
                    (string) ($result['returnMessage'] ?? 'Échec callback Binance')
                );
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('BinancePayService: processCallback error', [
                'error' => $e->getMessage(),
                'merchant_trade_no' => $merchantTradeNo,
            ]);
            $notifier->recordWebhookFailure('binance', $e->getMessage());

            return [
                'returnCode' => 'FAIL',
                'returnMessage' => $e->getMessage(),
                'http_status' => 500,
            ];
        }
    }

    public function generateMerchantTradeNo(): string
    {
        // Binance: unique merchant trade no (alphanumeric, longueur raisonnable)
        return 'BP'.strtoupper(Str::lower(dechex(time()))).strtoupper(Str::random(10));
    }

    protected function assertConfigured(): void
    {
        $key = (string) config('binancepay.binance_merchant_api_key');
        $secret = (string) config('binancepay.binance_merchant_secret_key');

        if ($key === '' || $secret === '') {
            throw new \RuntimeException(
                'Binance Pay non configuré: définissez BINANCE_PAY_API_KEY et BINANCE_PAY_SECRET.'
            );
        }
    }

    protected function normalizePublicKey(string $key): string
    {
        $trimmed = trim($key);
        if (str_contains($trimmed, 'BEGIN')) {
            return $trimmed;
        }

        $wrapped = trim(chunk_split($trimmed, 64, "\n"));

        return "-----BEGIN PUBLIC KEY-----\n{$wrapped}\n-----END PUBLIC KEY-----";
    }
}

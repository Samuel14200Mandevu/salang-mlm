<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Product;
use Illuminate\Support\Str;

class ConsultationSaleService
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PURCHASED = 'purchased';
    public const STATUS_DECLINED = 'declined';

    public const PORTION_FULL = 'full';
    public const PORTION_HALF = 'half';

    public const SERVICE_CERAGEM = 'ceragem';
    public const SERVICE_DETOX = 'detox';

    /** @var list<string> */
    public const SERVICE_KEYS = [self::SERVICE_CERAGEM, self::SERVICE_DETOX];

    public static function resolvePortion(?string $portion): string
    {
        return $portion === self::PORTION_HALF ? self::PORTION_HALF : self::PORTION_FULL;
    }

    /**
     * Force "complet" si le produit n'a pas d'option 1/2 en base.
     */
    public static function resolvePortionForProduct(Product $product, ?string $portion): string
    {
        $portion = self::resolvePortion($portion);

        if ($portion === self::PORTION_HALF && ! $product->hasHalfOption()) {
            return self::PORTION_FULL;
        }

        return $portion;
    }

    public static function portionLabel(?string $portion): string
    {
        return self::resolvePortion($portion) === self::PORTION_HALF ? '1/2' : 'Flacon';
    }

    public static function priceForProduct(Product $product, ?string $portion): float
    {
        return $product->priceForPortion(self::resolvePortionForProduct($product, $portion));
    }

    public static function pvForProduct(Product $product, ?string $portion): int
    {
        return $product->pvForPortion(self::resolvePortionForProduct($product, $portion));
    }

    public static function productDisplayName(array $line): string
    {
        $name = (string) ($line['produit'] ?? '');
        if (self::resolvePortion($line['portion'] ?? null) === self::PORTION_HALF) {
            return trim($name) === '' ? '1/2' : $name.' (1/2)';
        }

        return $name;
    }

    /**
     * Libellé prix catalogue pour la fiche (fourchette si 1/2 disponible).
     */
    public static function priceLabel(array $line): string
    {
        $line = self::enrichLinePricing($line);
        $min = self::lineMinPrice($line);
        $max = self::lineMaxPrice($line);

        if ($min <= 0 && $max <= 0) {
            return '';
        }

        if (abs($min - $max) < 0.001) {
            return '$'.number_format($max, 2);
        }

        return '$'.number_format($min, 2).' - $'.number_format($max, 2);
    }

    public static function lineHasHalf(array $line): bool
    {
        return ! empty($line['has_half'])
            || (isset($line['half_price']) && $line['half_price'] !== null && $line['half_price'] !== '');
    }

    public static function lineMinPrice(array $line): float
    {
        if (self::lineHasHalf($line) && isset($line['half_price'])) {
            return round((float) $line['half_price'], 2);
        }

        return self::lineMaxPrice($line);
    }

    public static function lineMaxPrice(array $line): float
    {
        if (isset($line['full_price'])) {
            return round((float) $line['full_price'], 2);
        }

        return round((float) ($line['prix'] ?? 0), 2);
    }

    /**
     * Complète half/full depuis le catalogue si absents de la ligne.
     *
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    public static function enrichLinePricing(array $line): array
    {
        if (empty($line['product_id'])) {
            return $line;
        }

        if (isset($line['full_price']) && array_key_exists('half_price', $line) && array_key_exists('has_half', $line)) {
            return $line;
        }

        $product = Product::find($line['product_id']);
        if (! $product) {
            return $line;
        }

        $fullPrice = (float) ($product->full_price ?? $product->price);
        $hasHalf = $product->hasHalfOption();

        $line['full_price'] = $line['full_price'] ?? $fullPrice;
        $line['half_price'] = array_key_exists('half_price', $line)
            ? $line['half_price']
            : ($hasHalf ? (float) $product->half_price : null);
        $line['has_half'] = $line['has_half'] ?? $hasHalf;
        $line['prix'] = $line['prix'] ?? $fullPrice;

        return $line;
    }

    /**
     * Total produits (min = somme des 1/2, max = somme des complets).
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{min: float, max: float, label: string}
     */
    public static function productsTotalRange(array $lines): array
    {
        $min = 0.0;
        $max = 0.0;

        foreach ($lines as $line) {
            if (empty($line['product_id']) && empty($line['produit'])) {
                continue;
            }
            $line = self::enrichLinePricing($line);
            $min += self::lineMinPrice($line);
            $max += self::lineMaxPrice($line);
        }

        $min = round($min, 2);
        $max = round($max, 2);

        $label = abs($min - $max) < 0.001
            ? '$'.number_format($max, 2)
            : '$'.number_format($min, 2).' - $'.number_format($max, 2);

        return [
            'min' => $min,
            'max' => $max,
            'label' => $label,
        ];
    }

    /**
     * Ligne produit recommandée côté admin (sans choix 1/2|complet).
     *
     * @return array<string, mixed>
     */
    public static function buildAdminProductRow(Product $product, array $item): array
    {
        $fullPrice = (float) ($product->full_price ?? $product->price);
        $hasHalf = $product->hasHalfOption();

        return [
            'product_id' => (int) $product->id,
            'produit' => (string) $product->name,
            'posologie' => (string) ($item['posologie'] ?? ''),
            'observation' => (string) ($item['observation'] ?? ''),
            'prix' => $fullPrice,
            'full_price' => $fullPrice,
            'half_price' => $hasHalf ? (float) $product->half_price : null,
            'has_half' => $hasHalf,
            'pv' => (int) ($product->full_pv ?? $product->pv_value ?? 0),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function normalizedLines(Consultation $consultation): array
    {
        $rows = $consultation->recommended_products ?? [];
        if (! is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $index => $row) {
            if (empty($row['product_id'])) {
                continue;
            }
            $row['line_key'] = $row['line_key']
                ?? ('legacy_' . $row['product_id'] . '_' . $index);
            $row['status'] = $row['status'] ?? self::STATUS_PENDING;
            $normalized[] = $row;
        }

        return $normalized;
    }

    /**
     * @return array{total: int, pending: int, purchased: int, declined: int, paid_amount: float, recommended_amount: float}
     */
    public static function productStats(Consultation $consultation): array
    {
        $lines = self::normalizedLines($consultation);
        $pending = 0;
        $purchased = 0;
        $declined = 0;
        $paidAmount = 0.0;
        $recommendedAmount = 0.0;

        foreach ($lines as $line) {
            $price = (float) ($line['prix'] ?? 0);
            $recommendedAmount += $price;
            $status = $line['status'] ?? self::STATUS_PENDING;

            if ($status === self::STATUS_PURCHASED) {
                $purchased++;
                $paidAmount += $price;
            } elseif ($status === self::STATUS_DECLINED) {
                $declined++;
            } else {
                $pending++;
            }
        }

        return [
            'total' => count($lines),
            'pending' => $pending,
            'purchased' => $purchased,
            'declined' => $declined,
            'paid_amount' => $paidAmount,
            'recommended_amount' => $recommendedAmount,
        ];
    }

    public static function canCashierSell(Consultation $consultation): bool
    {
        return in_array($consultation->status, ['processing', 'completed'], true);
    }

    /**
     * @param  array<int, string>  $lineKeys
     * @param  array<string, string>  $portions  line_key => half|full
     * @return array<int, array<string, mixed>>
     */
    public static function resolvePendingLines(Consultation $consultation, array $lineKeys, array $portions = []): array
    {
        $lineKeys = array_values(array_unique(array_filter($lineKeys)));
        $byKey = collect(self::normalizedLines($consultation))->keyBy('line_key');
        $selected = [];

        foreach ($lineKeys as $key) {
            $line = $byKey->get($key);
            if (! $line) {
                throw new \InvalidArgumentException('Ligne de consultation invalide.');
            }
            if (($line['status'] ?? self::STATUS_PENDING) !== self::STATUS_PENDING) {
                throw new \InvalidArgumentException('Un ou plusieurs produits ne sont plus disponibles à l\'encaissement.');
            }

            $line = self::enrichLinePricing($line);
            $product = Product::where('is_active', true)->find($line['product_id']);
            if (! $product) {
                throw new \InvalidArgumentException('Produit introuvable ou inactif : ' . ($line['produit'] ?? ''));
            }

            $portion = self::resolvePortionForProduct($product, $portions[$key] ?? ($line['portion'] ?? null));
            $line['portion'] = $portion;
            $line['prix'] = self::priceForProduct($product, $portion);
            $line['pv'] = self::pvForProduct($product, $portion);
            $selected[] = $line;
        }

        return $selected;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    public static function buildCartItems(Consultation $consultation, array $lines): array
    {
        $cart = [];

        foreach ($lines as $line) {
            $product = Product::where('is_active', true)->find($line['product_id']);
            if (! $product) {
                throw new \InvalidArgumentException('Produit introuvable ou inactif : ' . ($line['produit'] ?? ''));
            }

            $portion = self::resolvePortionForProduct($product, $line['portion'] ?? null);
            $linePrice = self::priceForProduct($product, $portion);

            $cart[] = [
                'id' => $product->id,
                'type' => 'product',
                'name' => self::productDisplayName(array_merge($line, [
                    'produit' => $product->name,
                    'portion' => $portion,
                ])),
                'price' => $linePrice,
                'image' => $product->image ? asset('storage/products/' . $product->image) : null,
                'source' => 'pos',
                'sourceLabel' => 'Consultation',
                'pv_value' => self::pvForProduct($product, $portion),
                'bv_value' => 0,
                'quantity' => 1,
                'consultation_id' => $consultation->id,
                'consultation_line_key' => $line['line_key'],
                'portion' => $portion,
            ];
        }

        return $cart;
    }

    /**
     * @param  array<int, string>  $lineKeys
     * @param  array<string, array{portion?: string, prix?: float|int|string}>  $purchaseDetails
     */
    public static function markLinesPurchased(
        Consultation $consultation,
        array $lineKeys,
        int $orderId,
        array $purchaseDetails = []
    ): void {
        $lineKeys = array_flip($lineKeys);
        $rows = $consultation->recommended_products ?? [];
        if (! is_array($rows)) {
            return;
        }

        $updated = [];
        foreach ($rows as $index => $row) {
            if (empty($row['product_id'])) {
                $updated[] = $row;
                continue;
            }
            $row['line_key'] = $row['line_key']
                ?? ('legacy_' . $row['product_id'] . '_' . $index);
            $row['status'] = $row['status'] ?? self::STATUS_PENDING;

            if (isset($lineKeys[$row['line_key']]) && $row['status'] === self::STATUS_PENDING) {
                $details = $purchaseDetails[$row['line_key']] ?? [];
                $row['status'] = self::STATUS_PURCHASED;
                $row['order_id'] = $orderId;
                $row['purchased_at'] = now()->toIso8601String();
                if (! empty($details['portion'])) {
                    $row['portion'] = self::resolvePortion($details['portion']);
                }
                if (isset($details['prix'])) {
                    $row['prix'] = round((float) $details['prix'], 2);
                }
            }
            $updated[] = $row;
        }

        $consultation->update(['recommended_products' => $updated]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $existingRows
     * @return array<int, array<string, mixed>>
     */
    public static function mergeAdminProductRow(array $existingRows, int $productId, array $newData): array
    {
        $existing = collect($existingRows)->firstWhere('product_id', $productId);
        $status = $existing['status'] ?? self::STATUS_PENDING;

        $merged = array_merge($newData, [
            'line_key' => $existing['line_key'] ?? (string) Str::uuid(),
            'status' => $status,
            'order_id' => $existing['order_id'] ?? null,
            'purchased_at' => $existing['purchased_at'] ?? null,
        ]);

        if ($status === self::STATUS_PURCHASED) {
            if (isset($existing['portion'])) {
                $merged['portion'] = $existing['portion'];
            }
            if (isset($existing['prix'])) {
                $merged['prix'] = $existing['prix'];
            }
        }

        return $merged;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PURCHASED => 'Payé',
            self::STATUS_PARTIAL => 'Partiel',
            self::STATUS_DECLINED => 'Refusé',
            default => 'À payer',
        };
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_PURCHASED => 'badge-success',
            self::STATUS_PARTIAL => 'badge-info',
            self::STATUS_DECLINED => 'badge-secondary',
            default => 'badge-warning',
        };
    }

    public static function serviceLabel(string $key): string
    {
        return match ($key) {
            self::SERVICE_CERAGEM => 'Séances Ceragem',
            self::SERVICE_DETOX => 'Séances Detox',
            default => $key,
        };
    }

    /**
     * @return array{ceragem: array<string, mixed>, detox: array<string, mixed>}
     */
    public static function serviceLines(Consultation $consultation): array
    {
        $stored = is_array($consultation->services_payment) ? $consultation->services_payment : [];

        return [
            self::SERVICE_CERAGEM => self::normalizeServiceLine(
                self::SERVICE_CERAGEM,
                (int) ($consultation->seances_ceragem ?? 0),
                (float) ($consultation->prix_ceragem ?? 0),
                $stored[self::SERVICE_CERAGEM] ?? []
            ),
            self::SERVICE_DETOX => self::normalizeServiceLine(
                self::SERVICE_DETOX,
                (int) ($consultation->seances_detox ?? 0),
                (float) ($consultation->prix_detox ?? 0),
                $stored[self::SERVICE_DETOX] ?? []
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private static function normalizeServiceLine(string $key, int $seancesPrescribed, float $prix, array $stored): array
    {
        $seancesPrescribed = max(0, $seancesPrescribed);
        $prix = round(max(0, $prix), 2);
        $seancesPaid = max(0, (int) ($stored['seances_paid'] ?? 0));

        // Ancien format (tout payé d’un coup) → migrer en mémoire.
        if ($seancesPaid === 0 && ($stored['status'] ?? null) === self::STATUS_PURCHASED) {
            $seancesPaid = max(0, (int) ($stored['seances'] ?? $seancesPrescribed));
        }

        // Ne pas dépasser la prescription actuelle.
        if ($seancesPrescribed > 0) {
            $seancesPaid = min($seancesPaid, $seancesPrescribed);
        }

        $seancesRemaining = max(0, $seancesPrescribed - $seancesPaid);
        $amountRecommended = round($seancesPrescribed * $prix, 2);
        $amountPaid = round((float) ($stored['amount_paid'] ?? ($seancesPaid * $prix)), 2);
        if ($seancesPaid > 0 && ! isset($stored['amount_paid']) && ($stored['status'] ?? null) === self::STATUS_PURCHASED && isset($stored['amount'])) {
            $amountPaid = round((float) $stored['amount'], 2);
        }
        $amountRemaining = round($seancesRemaining * $prix, 2);

        if ($seancesPrescribed <= 0 || $prix <= 0) {
            $status = self::STATUS_PENDING;
            $available = false;
        } elseif ($seancesRemaining <= 0 && $seancesPaid > 0) {
            $status = self::STATUS_PURCHASED;
            $available = false;
        } elseif ($seancesPaid > 0 && $seancesRemaining > 0) {
            $status = self::STATUS_PARTIAL;
            $available = $amountRemaining > 0.00001;
        } else {
            $status = self::STATUS_PENDING;
            $available = $amountRemaining > 0.00001;
        }

        return [
            'key' => $key,
            'label' => self::serviceLabel($key),
            'seances' => $seancesPrescribed,
            'seances_prescribed' => $seancesPrescribed,
            'seances_paid' => $seancesPaid,
            'seances_remaining' => $seancesRemaining,
            'prix' => $prix,
            'amount' => $amountRecommended,
            'amount_paid' => $amountPaid,
            'amount_remaining' => $amountRemaining,
            'status' => $status,
            'order_id' => isset($stored['order_id']) ? (int) $stored['order_id'] : null,
            'purchased_at' => $stored['purchased_at'] ?? null,
            'payments' => is_array($stored['payments'] ?? null) ? $stored['payments'] : [],
            'available' => $available,
        ];
    }

    /**
     * @return array{total: int, pending: int, purchased: int, partial: int, paid_amount: float, recommended_amount: float, remaining_amount: float}
     */
    public static function serviceStats(Consultation $consultation): array
    {
        $pending = 0;
        $purchased = 0;
        $partial = 0;
        $paidAmount = 0.0;
        $recommendedAmount = 0.0;
        $remainingAmount = 0.0;
        $total = 0;

        foreach (self::serviceLines($consultation) as $line) {
            if (($line['seances_prescribed'] ?? 0) <= 0) {
                continue;
            }
            $total++;
            $recommendedAmount += (float) $line['amount'];
            $paidAmount += (float) $line['amount_paid'];
            $remainingAmount += (float) $line['amount_remaining'];

            if ($line['status'] === self::STATUS_PURCHASED) {
                $purchased++;
            } elseif ($line['status'] === self::STATUS_PARTIAL) {
                $partial++;
                $pending++;
            } elseif ($line['available']) {
                $pending++;
            }
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'purchased' => $purchased,
            'partial' => $partial,
            'paid_amount' => $paidAmount,
            'recommended_amount' => $recommendedAmount,
            'remaining_amount' => $remainingAmount,
        ];
    }

    /**
     * @param  array<string, int|string>  $serviceQuantities  key => nb séances à encaisser maintenant
     * @return array<int, array<string, mixed>>
     */
    public static function resolvePendingServices(Consultation $consultation, array $serviceQuantities): array
    {
        $lines = self::serviceLines($consultation);
        $selected = [];

        foreach ($serviceQuantities as $key => $qty) {
            $key = (string) $key;
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }
            if (! in_array($key, self::SERVICE_KEYS, true)) {
                throw new \InvalidArgumentException('Service de consultation invalide.');
            }
            $line = $lines[$key] ?? null;
            if (! $line || ! ($line['available'] ?? false)) {
                throw new \InvalidArgumentException('Service indisponible : '.self::serviceLabel($key));
            }
            $remaining = (int) ($line['seances_remaining'] ?? 0);
            if ($qty > $remaining) {
                throw new \InvalidArgumentException(
                    self::serviceLabel($key).' : maximum '.$remaining.' séance(s) restante(s).'
                );
            }

            $prix = (float) $line['prix'];
            $line['pay_seances'] = $qty;
            $line['pay_amount'] = round($qty * $prix, 2);
            $selected[] = $line;
        }

        if ($selected === []) {
            throw new \InvalidArgumentException('Indiquez le nombre de séances à encaisser.');
        }

        return $selected;
    }

    /**
     * @param  array<int, array<string, mixed>>  $serviceLines
     * @return array<int, array<string, mixed>>
     */
    public static function buildServiceCartItems(Consultation $consultation, array $serviceLines): array
    {
        $cart = [];

        foreach ($serviceLines as $line) {
            $key = (string) ($line['key'] ?? '');
            $paySeances = (int) ($line['pay_seances'] ?? 0);
            $amount = round((float) ($line['pay_amount'] ?? 0), 2);
            if ($paySeances <= 0 || $amount <= 0) {
                continue;
            }

            $cart[] = [
                'id' => 'svc_'.$key,
                'type' => 'consultation_service',
                'name' => sprintf(
                    '%s (x%d)',
                    self::serviceLabel($key),
                    $paySeances
                ),
                'price' => $amount,
                'image' => null,
                'source' => 'pos',
                'sourceLabel' => 'Consultation',
                'pv_value' => 0,
                'bv_value' => 0,
                'quantity' => 1,
                'consultation_id' => $consultation->id,
                'consultation_service_key' => $key,
                'consultation_service_seances' => $paySeances,
            ];
        }

        return $cart;
    }

    /**
     * @param  array<string, array{seances: int, amount?: float}>  $servicePayments
     */
    public static function markServicesPurchased(
        Consultation $consultation,
        array $servicePayments,
        int $orderId
    ): void {
        if ($servicePayments === []) {
            return;
        }

        $lines = self::serviceLines($consultation);
        $payment = is_array($consultation->services_payment) ? $consultation->services_payment : [];

        foreach ($servicePayments as $key => $payload) {
            $key = (string) $key;
            if (! in_array($key, self::SERVICE_KEYS, true)) {
                continue;
            }
            $line = $lines[$key] ?? null;
            if (! $line) {
                continue;
            }

            $paySeances = max(0, (int) ($payload['seances'] ?? 0));
            if ($paySeances <= 0) {
                continue;
            }
            $paySeances = min($paySeances, (int) $line['seances_remaining']);
            if ($paySeances <= 0) {
                continue;
            }

            $prix = (float) $line['prix'];
            $payAmount = isset($payload['amount'])
                ? round((float) $payload['amount'], 2)
                : round($paySeances * $prix, 2);

            $existing = is_array($payment[$key] ?? null) ? $payment[$key] : [];
            $seancesPaid = (int) ($line['seances_paid'] ?? 0) + $paySeances;
            $amountPaid = round((float) ($line['amount_paid'] ?? 0) + $payAmount, 2);
            $payments = is_array($existing['payments'] ?? null) ? $existing['payments'] : [];
            $payments[] = [
                'order_id' => $orderId,
                'seances' => $paySeances,
                'amount' => $payAmount,
                'at' => now()->toIso8601String(),
            ];

            $prescribed = (int) $line['seances_prescribed'];
            $status = $seancesPaid >= $prescribed && $prescribed > 0
                ? self::STATUS_PURCHASED
                : self::STATUS_PARTIAL;

            $payment[$key] = [
                'status' => $status,
                'order_id' => $orderId,
                'seances_paid' => $seancesPaid,
                'amount_paid' => $amountPaid,
                'prix' => $prix,
                'seances' => $prescribed,
                'purchased_at' => now()->toIso8601String(),
                'payments' => $payments,
            ];
        }

        $consultation->update(['services_payment' => $payment]);
    }
}

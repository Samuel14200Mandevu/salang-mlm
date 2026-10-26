<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Product;
use Illuminate\Support\Str;

class ConsultationSaleService
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PURCHASED = 'purchased';
    public const STATUS_DECLINED = 'declined';

    public const PORTION_FULL = 'full';
    public const PORTION_HALF = 'half';

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
        return self::resolvePortion($portion) === self::PORTION_HALF ? '1/2' : 'Complet';
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
            self::STATUS_DECLINED => 'Refusé',
            default => 'À payer',
        };
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_PURCHASED => 'badge-success',
            self::STATUS_DECLINED => 'badge-secondary',
            default => 'badge-warning',
        };
    }
}

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
     * @return array<int, array<string, mixed>>
     */
    public static function resolvePendingLines(Consultation $consultation, array $lineKeys): array
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

            $cart[] = [
                'id' => $product->id,
                'type' => 'product',
                'name' => $product->name,
                'price' => (float) $product->price,
                'image' => $product->image ? asset('storage/products/' . $product->image) : null,
                'source' => 'pos',
                'sourceLabel' => 'Consultation',
                'pv_value' => $product->pv_value ?? 0,
                'bv_value' => $product->bv_value ?? 0,
                'quantity' => 1,
                'consultation_id' => $consultation->id,
                'consultation_line_key' => $line['line_key'],
            ];
        }

        return $cart;
    }

    /**
     * @param  array<int, string>  $lineKeys
     */
    public static function markLinesPurchased(Consultation $consultation, array $lineKeys, int $orderId): void
    {
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
                $row['status'] = self::STATUS_PURCHASED;
                $row['order_id'] = $orderId;
                $row['purchased_at'] = now()->toIso8601String();
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

        return array_merge($newData, [
            'line_key' => $existing['line_key'] ?? (string) Str::uuid(),
            'status' => $existing['status'] ?? self::STATUS_PENDING,
            'order_id' => $existing['order_id'] ?? null,
            'purchased_at' => $existing['purchased_at'] ?? null,
        ]);
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

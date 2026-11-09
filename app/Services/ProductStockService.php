<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductStockService
{
    /**
     * Décrémente le stock pour une vente (atomique) et journalise.
     */
    public function consumeForSale(int $productId, int $quantity, ?Order $order = null, ?string $productName = null): void
    {
        if ($quantity < 1) {
            return;
        }

        DB::transaction(function () use ($productId, $quantity, $order, $productName) {
            $product = Product::query()->whereKey($productId)->lockForUpdate()->first();
            if (! $product) {
                throw new RuntimeException('Produit introuvable pour la décrémentation de stock.');
            }

            $before = (int) $product->stock;
            if ($before < $quantity) {
                $label = $productName ?: $product->name;

                throw new RuntimeException(
                    "Stock insuffisant pour {$label} (disponible: {$before}, demandé: {$quantity})."
                );
            }

            $after = $before - $quantity;
            $product->update(['stock' => $after]);

            $this->record(
                product: $product,
                type: StockMovement::TYPE_SALE,
                delta: -$quantity,
                before: $before,
                after: $after,
                reason: $order ? 'Vente #'.$order->order_number : 'Vente POS',
                userId: Auth::id(),
                reference: $order
            );
        });
    }

    /**
     * Remet en stock les lignes produit d'une commande annulée.
     */
    public function restoreForCancelledOrder(Order $order): void
    {
        $order->loadMissing('items');

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (! $item->product_id || (int) $item->quantity < 1) {
                    continue;
                }

                $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                if (! $product) {
                    continue;
                }

                $qty = (int) $item->quantity;
                $before = (int) $product->stock;
                $after = $before + $qty;
                $product->update(['stock' => $after]);

                $this->record(
                    product: $product,
                    type: StockMovement::TYPE_SALE_CANCEL,
                    delta: $qty,
                    before: $before,
                    after: $after,
                    reason: 'Annulation commande #'.$order->order_number,
                    userId: Auth::id(),
                    reference: $order
                );
            }
        });
    }

    /**
     * Entrée manuelle (réception / réapprovisionnement).
     */
    public function receive(Product $product, int $quantity, string $reason, ?User $actor = null): StockMovement
    {
        if ($quantity < 1) {
            throw new RuntimeException('La quantité d’entrée doit être au moins 1.');
        }

        return $this->applyDelta(
            product: $product,
            delta: $quantity,
            type: StockMovement::TYPE_ENTRY,
            reason: $reason !== '' ? $reason : 'Entrée de stock',
            actor: $actor
        );
    }

    /**
     * Sortie manuelle (perte, casse, don…).
     */
    public function remove(Product $product, int $quantity, string $reason, ?User $actor = null): StockMovement
    {
        if ($quantity < 1) {
            throw new RuntimeException('La quantité de sortie doit être au moins 1.');
        }

        return $this->applyDelta(
            product: $product,
            delta: -$quantity,
            type: StockMovement::TYPE_EXIT,
            reason: $reason !== '' ? $reason : 'Sortie de stock',
            actor: $actor
        );
    }

    /**
     * Ajuste le stock à une valeur absolue (inventaire).
     */
    public function adjustTo(Product $product, int $newStock, string $reason, ?User $actor = null): StockMovement
    {
        if ($newStock < 0) {
            throw new RuntimeException('Le stock ne peut pas être négatif.');
        }

        return DB::transaction(function () use ($product, $newStock, $reason, $actor) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $before = (int) $locked->stock;
            $delta = $newStock - $before;

            if ($delta === 0) {
                throw new RuntimeException('Le stock est déjà à cette valeur.');
            }

            $locked->update(['stock' => $newStock]);

            return $this->record(
                product: $locked,
                type: StockMovement::TYPE_ADJUSTMENT,
                delta: $delta,
                before: $before,
                after: $newStock,
                reason: $reason !== '' ? $reason : 'Ajustement inventaire',
                userId: $actor?->id ?? Auth::id()
            );
        });
    }

    /**
     * Enregistre le stock initial à la création produit (si > 0).
     */
    public function recordInitial(Product $product, int $quantity, ?User $actor = null): ?StockMovement
    {
        if ($quantity < 1) {
            return null;
        }

        return $this->record(
            product: $product,
            type: StockMovement::TYPE_INITIAL,
            delta: $quantity,
            before: 0,
            after: $quantity,
            reason: 'Stock initial à la création',
            userId: $actor?->id ?? Auth::id()
        );
    }

    protected function applyDelta(
        Product $product,
        int $delta,
        string $type,
        string $reason,
        ?User $actor = null
    ): StockMovement {
        return DB::transaction(function () use ($product, $delta, $type, $reason, $actor) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $before = (int) $locked->stock;
            $after = $before + $delta;

            if ($after < 0) {
                throw new RuntimeException(
                    "Stock insuffisant (disponible: {$before}, demandé: ".abs($delta).').'
                );
            }

            $locked->update(['stock' => $after]);

            return $this->record(
                product: $locked,
                type: $type,
                delta: $delta,
                before: $before,
                after: $after,
                reason: $reason,
                userId: $actor?->id ?? Auth::id()
            );
        });
    }

    protected function record(
        Product $product,
        string $type,
        int $delta,
        int $before,
        int $after,
        ?string $reason = null,
        ?int $userId = null,
        ?Order $reference = null
    ): StockMovement {
        return StockMovement::query()->create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity_delta' => $delta,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reason' => $reason,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->id,
        ]);
    }
}

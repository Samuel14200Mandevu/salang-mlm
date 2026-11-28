<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\CashEntry;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\PurchaseItem;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\SaleItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductStockService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PharmacyOpsService
{
    public function __construct(
        protected ProductStockService $stock
    ) {}

    /**
     * @param  array{purchased_at:string,invoice_number?:?string,supplier_id:int,paid_amount?:float|int|string,notes?:?string,items:list<array{product_id:int,quantity:int,unit_cost:float|int|string}>}  $data
     */
    public function createPurchase(array $data, ?User $actor = null): Purchase
    {
        if (empty($data['items'])) {
            throw new RuntimeException('Ajoutez au moins une ligne produit.');
        }

        return DB::transaction(function () use ($data, $actor) {
            $total = 0.0;
            $normalized = [];

            foreach ($data['items'] as $row) {
                $qty = (int) ($row['quantity'] ?? 0);
                $cost = round((float) ($row['unit_cost'] ?? 0), 2);
                if ($qty < 1 || $cost < 0) {
                    throw new RuntimeException('Quantité ou coût invalide sur une ligne d’achat.');
                }
                $line = round($qty * $cost, 2);
                $total += $line;
                $normalized[] = [
                    'product_id' => (int) $row['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'line_total' => $line,
                ];
            }

            $paid = round((float) ($data['paid_amount'] ?? 0), 2);
            $purchase = Purchase::query()->create([
                'purchased_at' => $data['purchased_at'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'supplier_id' => (int) $data['supplier_id'],
                'total_amount' => round($total, 2),
                'paid_amount' => $paid,
                'status' => Purchase::resolveStatus($total, $paid),
                'notes' => $data['notes'] ?? null,
                'user_id' => $actor?->id,
            ]);

            foreach ($normalized as $line) {
                PurchaseItem::query()->create(array_merge($line, [
                    'purchase_id' => $purchase->id,
                ]));

                $product = Product::query()->findOrFail($line['product_id']);
                $label = $purchase->invoice_number
                    ? 'Réception achat '.$purchase->invoice_number
                    : 'Réception achat #'.$purchase->id;

                $this->stock->receive($product, $line['quantity'], $label, $actor);

                if ($line['unit_cost'] > 0 && (float) $product->cost !== $line['unit_cost']) {
                    $product->update(['cost' => $line['unit_cost']]);
                }
            }

            return $purchase->load(['supplier', 'items.product']);
        });
    }

    /**
     * @param  array{sold_at:string,patient_id?:?int,customer_name?:?string,payment_method:string,insurer_id?:?int,coverage_rate?:float|int|string,notes?:?string,items:list<array{product_id:int,quantity:int,unit_price?:float|int|string}>}  $data
     */
    public function createSale(array $data, ?User $actor = null): Sale
    {
        if (empty($data['items'])) {
            throw new RuntimeException('Ajoutez au moins une ligne produit.');
        }

        return DB::transaction(function () use ($data, $actor) {
            $coverage = max(0, min(1, (float) ($data['coverage_rate'] ?? 0)));
            $method = $data['payment_method'] ?? 'cash';
            if ($method !== 'insurance') {
                $coverage = 0;
            }

            $lines = [];
            $total = 0.0;
            $costTotal = 0.0;

            foreach ($data['items'] as $row) {
                $product = Product::query()->lockForUpdate()->findOrFail((int) $row['product_id']);
                $qty = (int) ($row['quantity'] ?? 0);
                if ($qty < 1) {
                    throw new RuntimeException('Quantité invalide sur une ligne de vente.');
                }
                if ((int) $product->stock < $qty) {
                    throw new RuntimeException(
                        "Stock insuffisant pour {$product->name} (dispo: {$product->stock})."
                    );
                }

                $unitPrice = array_key_exists('unit_price', $row) && $row['unit_price'] !== null && $row['unit_price'] !== ''
                    ? round((float) $row['unit_price'], 2)
                    : round((float) $product->price, 2);
                $unitCost = round((float) ($product->cost ?? 0), 2);
                $lineTotal = round($qty * $unitPrice, 2);
                $lineCost = round($qty * $unitCost, 2);
                $insShare = round($lineTotal * $coverage, 2);
                $patShare = round($lineTotal - $insShare, 2);

                $total += $lineTotal;
                $costTotal += $lineCost;
                $lines[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                    'insurance_share' => $insShare,
                    'patient_share' => $patShare,
                    'cost_total' => $lineCost,
                    'profit' => round($lineTotal - $lineCost, 2),
                ];
            }

            $insuranceAmount = round($total * $coverage, 2);
            $patientAmount = round($total - $insuranceAmount, 2);
            $profit = round($total - $costTotal, 2);

            $sale = Sale::query()->create([
                'sold_at' => $data['sold_at'],
                'ticket_number' => Sale::nextTicketNumber(),
                'patient_id' => $data['patient_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'payment_method' => $method,
                'insurer_id' => $method === 'insurance' ? ($data['insurer_id'] ?? null) : null,
                'coverage_rate' => $coverage,
                'total_amount' => round($total, 2),
                'insurance_amount' => $insuranceAmount,
                'patient_amount' => $patientAmount,
                'cost_amount' => round($costTotal, 2),
                'profit_amount' => $profit,
                'notes' => $data['notes'] ?? null,
                'user_id' => $actor?->id,
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                unset($line['product']);

                SaleItem::query()->create(array_merge($line, [
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                ]));

                $this->stock->remove(
                    $product,
                    $line['quantity'],
                    'Vente pharmacie '.$sale->ticket_number,
                    $actor
                );
            }

            if ($patientAmount > 0 && in_array($method, ['cash', 'mobile_money', 'card', 'insurance'], true)) {
                CashEntry::query()->create([
                    'entry_date' => $data['sold_at'],
                    'label' => 'Recette '.$sale->ticket_number,
                    'category' => 'sales_receipt',
                    'amount_in' => $patientAmount,
                    'amount_out' => 0,
                    'notes' => $method === 'insurance'
                        ? 'Part patient (assurance '.number_format($coverage * 100, 0).'%)'
                        : null,
                    'user_id' => $actor?->id,
                ]);
            }

            return $sale->load(['patient', 'insurer', 'items.product']);
        });
    }
}

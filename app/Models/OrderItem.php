<?php
// app/Models/OrderItem.php

namespace App\Models;

use App\Jobs\UpdateRanks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'package_id',
        'name',
        'sku',
        'quantity',
        'price',
        'total',
        'pv_value',
        'bv_value',
        'options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'pv_value' => 'integer',
        'bv_value' => 'integer',
        'options' => 'array',
    ];

    // ============================================================
    // BOOT - AUTO UPDATE RANK
    // ============================================================

    protected static function booted(): void
    {
        // Après la création d'un OrderItem
        static::created(function ($orderItem) {
            if ($orderItem->order && $orderItem->order->user_id) {
                // Mettre à jour les PV de l'utilisateur
                $user = $orderItem->order->user;
                if ($user) {
                    // Mettre à jour le PV mensuel
                    $user->updateMonthlyPV();
                    
                    // Mettre à jour le grade
                    $user->calculateAndUpdateRank();
                    
                    Log::info('OrderItem créé - Grade mis à jour', [
                        'user_id' => $user->id,
                        'order_item_id' => $orderItem->id,
                        'pv_value' => $orderItem->pv_value,
                    ]);
                }
            }
        });

        // Après la mise à jour d'un OrderItem
        static::updated(function ($orderItem) {
            if ($orderItem->isDirty(['pv_value', 'bv_value', 'quantity'])) {
                if ($orderItem->order && $orderItem->order->user_id) {
                    $user = $orderItem->order->user;
                    if ($user) {
                        $user->updateMonthlyPV();
                        $user->calculateAndUpdateRank();
                    }
                }
            }
        });

        // Après la suppression d'un OrderItem
        static::deleted(function ($orderItem) {
            if ($orderItem->order && $orderItem->order->user_id) {
                $user = $orderItem->order->user;
                if ($user) {
                    $user->updateMonthlyPV();
                    $user->calculateAndUpdateRank();
                }
            }
        });
    }

    // ============================================================
    // RELATIONS
    // ============================================================

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total, 2);
    }

    public function getTotalPVAttribute(): int
    {
        return $this->pv_value * $this->quantity;
    }

    public function getTotalBVAttribute(): int
    {
        return $this->bv_value * $this->quantity;
    }

    // ============================================================
    // UTILITY METHODS
    // ============================================================

    public function isProduct()
    {
        return !is_null($this->product_id);
    }

    public function isPackage()
    {
        return !is_null($this->package_id);
    }

    /**
     * Mettre à jour le grade de l'utilisateur après modification
     */
    public function updateUserRank(): void
    {
        if ($this->order && $this->order->user_id) {
            $user = $this->order->user;
            if ($user) {
                dispatch(new UpdateRanks($user->id))->delay(now()->addSeconds(1));
            }
        }
    }
}
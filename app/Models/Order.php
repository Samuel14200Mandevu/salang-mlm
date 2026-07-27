<?php
// app/Models/Order.php

namespace App\Models;

use App\Jobs\UpdateTeamPV;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'tax',
        'shipping',
        'discount',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'source',          
        'shipping_address',
        'billing_address',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    // ============================================================
    // BOOTED - TRIGGERS AUTOMATIQUES
    // ============================================================

    protected static function booted(): void
    {
        static::created(function ($order) {
            if ($order->user_id) {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->updateMonthlyPV();
                    dispatch(new UpdateTeamPV($user->id, true));
                }
            }
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status') || $order->wasChanged('payment_status')) {
                if ($order->status === 'completed' || $order->payment_status === 'completed') {
                    if ($order->user_id) {
                        $user = User::find($order->user_id);
                        if ($user) {
                            $user->updateMonthlyPV();
                            dispatch(new UpdateTeamPV($user->id, true));
                            $user->calculateAndUpdateRank();
                            
                            Log::info('Order: Mise à jour des PV après commande', [
                                'order_id' => $order->id,
                                'user_id' => $user->id,
                                'status' => $order->status,
                            ]);
                        }
                    }
                }
            }
            
            if ($order->wasChanged('payment_status') && $order->payment_status === 'completed') {
                if ($order->user_id) {
                    $user = User::find($order->user_id);
                    if ($user) {
                        $user->updateMonthlyPV();
                        dispatch(new UpdateTeamPV($user->id, true));
                    }
                }
            }
        });

        static::deleted(function ($order) {
            if ($order->user_id) {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->updateMonthlyPV();
                    dispatch(new UpdateTeamPV($user->id, true));
                }
            }
        });
    }

    // ============================================================
    // RELATIONS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopePos($query)
    {
        return $query->where('source', 'pos');
    }

    public function scopeMlm($query)
    {
        return $query->where('source', 'mlm');
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'completed' => 'Paid',
            'failed' => 'Failed',
        ];
        return $labels[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function cashier()
{
    return $this->belongsTo(User::class, 'cashier_id');
}

    public function getTotalPVAttribute(): int
    {
        return $this->items()->sum('pv_value') ?? 0;
    }

    public function getTotalBVAttribute(): int
    {
        return $this->items()->sum('bv_value') ?? 0;
    }

    public function getFormattedSubtotalAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total, 2);
    }

    // ✅ SOURCE LABEL
    public function getSourceLabelAttribute()
    {
        $labels = [
            'mlm' => ' Site MLM',
            'pos' => ' Guichet POS',
        ];
        return $labels[$this->source] ?? ucfirst($this->source);
    }

    // ============================================================
    // MÉTHODES UTILITAIRES
    // ============================================================

    public function isPaid()
    {
        return $this->payment_status === 'completed';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPos()
    {
        return $this->source === 'pos';
    }

    public function isMlm()
    {
        return $this->source === 'mlm';
    }

    public function updateUserPV(): void
    {
        if ($this->user_id) {
            $user = User::find($this->user_id);
            if ($user) {
                $user->updateMonthlyPV();
                $user->updateTeamPV();
                $user->updateAllAncestors();
                $user->calculateAndUpdateRank();
            }
        }
    }
}